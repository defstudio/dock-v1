<?php


    namespace App\Recipes\ReverseProxy;


    use App\Containers\CertbotCloudflare;
    use App\Containers\Container;
    use App\Containers\Nginx;
    use App\Contracts\SSLService;
    use App\Exceptions\ContainerException;
    use App\Exceptions\DuplicateNetworkException;
    use App\Exceptions\DuplicateServiceException;
    use App\Recipes\DockerComposeRecipe;
    use App\Recipes\ReverseProxy\Commands\ProxyAdd;
    use App\Recipes\ReverseProxy\Commands\ProxyDisable;
    use App\Recipes\ReverseProxy\Commands\ProxyEnable;
    use App\Recipes\ReverseProxy\Commands\ProxyReload;
    use App\Recipes\ReverseProxy\Exceptions\ProxyTargetInvalidException;
    use App\Recipes\ReverseProxy\Services\TargetsService;
    use App\Services\SSL\Certbot;
    use App\Services\SSL\SelfSigned;
    use App\Traits\InteractsWithEnvContent;
    use Illuminate\Console\Command;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use Illuminate\Contracts\Filesystem\FileNotFoundException;

    class ReverseProxyRecipe extends DockerComposeRecipe{

        use InteractsWithEnvContent;

        const LABEL = 'ReverseProxy';

        const PROXY_NETWORK = 'reverse_proxy_network';


        protected function customize_init(Command $parent_command, string $env_content): string{
            /** @var TargetsService $targets */
            $targets = app()->make(TargetsService::class);
            $targets->init_targets_file();

            $env_content = $this->init_ssl_configuration($parent_command, $env_content);

            return $env_content;
        }

        private function init_ssl_configuration(Command $parent_command, string $env_content): string{
            $parent_command->question('SSL Configuration');

            $ssl_provider_class = $parent_command->choice('Select SSL Provider', [
                SelfSigned::class => 'self-signed',
                Certbot::class => 'certbot',
            ], Certbot::class);

            $this->set_env($env_content, 'SSL_PROVIDER', $ssl_provider_class);

            /** @var SSLService $ssl_provider */
            $ssl_provider = app()->make($ssl_provider_class);

            $env_content = $ssl_provider->init_recipe($parent_command, $env_content);

            return $env_content;
        }


        /**
         * @inheritDoc
         * @throws BindingResolutionException
         * @throws FileNotFoundException
         * @throws ProxyTargetInvalidException
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

            $nginx->map_port(80);
            $nginx->map_port(443);
            $nginx->map_port(6001);

            $nginx->unset_service_definition('working_dir');
            $nginx->unset_php_service();

            // TODO: verificare come mai genera errore per certificato mancante
            $nginx->enable_backend_not_found_page();

            $nginx->set_volume(Container::HOST_CONFIG_VOLUME_PATH . 'certbot/letsencrypt', '/etc/letsencrypt');

            $nginx->add_network(self::PROXY_NETWORK);

            return $nginx;
        }

        /**
         * @param Nginx $nginx
         *
         * @throws FileNotFoundException|ProxyTargetInvalidException
         */
        private function build_targets(Nginx $nginx): void{

            /** @var TargetsService $targets */
            $targets = app()->make(TargetsService::class);

            $targets->make_proxies($nginx);


        }

        /**
         * @throws BindingResolutionException
         */
        private function build_ssl_providers(): void{

            /** @var SSLService $ssl_provider */
            $ssl_provider = app()->make(SSLService::class);
            $ssl_provider->build_ssl_provider($this);

        }



        /**
         * @throws ContainerException
         * @throws DuplicateNetworkException
         * @throws DuplicateServiceException
         */
        public function setup(){
            parent::setup();
            $this->docker_service->add_network(self::PROXY_NETWORK, self::PROXY_NETWORK, 'bridge');
        }

        /**
         * @inheritDoc
         */
        protected function recipe_commands(): array{
            return [
                ProxyEnable::class,
                ProxyDisable::class,
                ProxyAdd::class,
                ProxyReload::class,
            ];
        }
    }


