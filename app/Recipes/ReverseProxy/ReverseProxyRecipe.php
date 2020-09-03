<?php


    namespace App\Recipes\ReverseProxy;


    use App\Containers\CertbotCloudflare;
    use App\Containers\Container;
    use App\Containers\Nginx;
    use App\Exceptions\ContainerException;
    use App\Exceptions\DuplicateNetworkException;
    use App\Exceptions\DuplicateServiceException;
    use App\Recipes\DockerComposeRecipe;
    use App\Recipes\ReverseProxy\Exceptions\ProxyTargetInvalidException;
    use App\Traits\InteractsWithEnvContent;
    use Illuminate\Console\Command;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use Illuminate\Contracts\Filesystem\FileNotFoundException;
    use Illuminate\Support\Facades\Storage;

    class ReverseProxyRecipe extends DockerComposeRecipe{

        use InteractsWithEnvContent;

        const LABEL = 'ReverseProxy';

        private array $external_networks = [];


        protected function customize_init(Command $parent_command, string $env_content): string{
            if(!Storage::disk('cwd')->exists('targets.json')){
                Storage::disk('cwd')->put('targets.json', json_encode([
                    [
                        'container_network'   => 'example_default',
                        'container_hostname'  => 'example_nginx_1',
                        'container_port'  => 80,
                        'external_hostname' => 'example.ktm',
                        'external_port'     => 80,
                        'ssl_certificate'     => '/etc/letsencrypt/live/example.ktm/fullchain.pem',
                        'ssl_certificate_key'     => '/etc/letsencrypt/live/example.ktm/privkey.pem',

                    ],
                ], JSON_PRETTY_PRINT));
            }

            $env_content = $this->init_ssl_configuration($parent_command, $env_content);

            return $env_content;
        }

        private function init_ssl_configuration(Command $parent_command, string $env_content): string{
            $parent_command->question('SSL Configuration');

            $ssl_provider = $parent_command->choice('Select SSL Provider', [
                'openssl',
                'certbot',
            ], 0);

            $this->set_env($env_content, 'SSL_PROVIDER', $ssl_provider);

            switch($ssl_provider){
                case 'certbot':
                    $env_content = $this->init_certbot_provider($parent_command, $env_content);
                    break;
                case 'openssl':

                    break;
            }

            return $env_content;
        }

        private function init_certbot_provider(Command $parent_command, string $env_content): string{

            $challenge_mode = $parent_command->choice('Certbot challenge mode', [
                'dns-cloudflare',
            ], 'dns-cloudflare');

            $this->set_env($env_content, 'CERTBOT_CHALLENGE_MODE', $challenge_mode);

            switch($challenge_mode){
                case 'dns-cloudflare':
                    $env_content = $this->init_certbot_dns_challenge($parent_command, $env_content);
                    break;
            }


            return $env_content;
        }

        private function init_certbot_dns_challenge(Command $parent_command, string $env_content): string{

            $token = $parent_command->ask('Cloudflare Token');

            $this->set_env($env_content, 'CLOUDFLARE_TOKEN', $token);

            return $env_content;
        }

        /**
         * @inheritDoc
         * @throws BindingResolutionException
         * @throws FileNotFoundException
         */
        public function build(){

            $nginx = $this->build_nginx();


            $this->build_targets($nginx);


            $this->build_ssl_providers();

        }

        /**
         * @return Nginx
         * @throws BindingResolutionException
         */
        private function build_nginx(): Nginx{
            /** @var Nginx $nginx */
            $nginx = $this->add_container(Nginx::class);

            $nginx->map_port(80, 80);
            $nginx->map_port(443, 443);

            $nginx->unset_service_definition('working_dir');
            $nginx->unset_php_service();

            $nginx->set_volume(Container::HOST_CONFIG_VOLUME_PATH . 'certbot/letsencrypt', '/etc/letsencrypt');

            return $nginx;
        }

        /**
         * @param Nginx $nginx
         *
         * @throws FileNotFoundException
         */
        private function build_targets(Nginx $nginx): void{

            $targets_json = Storage::disk('cwd')->get('targets.json');
            $targets = json_decode($targets_json);

            if(empty($targets)) throw new ProxyTargetInvalidException("targets.json file is invalid");

            foreach($targets as $target){
                $this->external_networks[] = $target->container_network;
                $nginx->add_network($target->container_network);
                $nginx->add_proxy($target->external_hostname, $target->external_port,  $target->container_hostname, $target->container_port, $target->ssl_certificate??'', $target->ssl_certificate_key??'');
            }
        }

        /**
         * @throws BindingResolutionException
         */
        private function build_ssl_providers(): void{
            $ssl_provider = env('SSL_PROVIDER');


            switch($ssl_provider){
                case 'certbot':
                    $this->build_certbot_provider();
                    break;
            }
        }

        /**
         * @throws BindingResolutionException
         */
        private function build_certbot_provider(): void{
            $challenge_mode = env('CERTBOT_CHALLENGE_MODE');

            switch($challenge_mode){
                case 'dns-cloudflare':
                    $this->build_certbot_dns_cloudflare();
                    break;
            }
        }

        /**
         * @throws BindingResolutionException
         */
        private function build_certbot_dns_cloudflare(): void{
           $this->add_container(CertbotCloudflare::class, [
                'cloudflare_token' => env('CLOUDFLARE_TOKEN'),
            ]);

        }

        /**
         * @throws ContainerException
         * @throws DuplicateNetworkException
         * @throws DuplicateServiceException
         */
        public function setup(){
            parent::setup();

            foreach(array_unique($this->external_networks) as $network){
                $this->docker_service->add_external_network($network);
            }
        }

        /**
         * @inheritDoc
         */
        protected function recipe_commands(): array{
            return [];
        }
    }


