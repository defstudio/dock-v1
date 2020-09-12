<?php


	namespace App\Services\SSL;


	use App\Containers\CertbotCloudflare;
    use App\Contracts\SSLService;
    use App\Recipes\DockerComposeRecipe;
    use App\Traits\InteractsWithEnvContent;
    use Illuminate\Console\Command;
    use Illuminate\Contracts\Container\BindingResolutionException;

    class Certbot implements SSLService{
        use InteractsWithEnvContent;

        public function init_recipe(Command $parent_command, string $env_content): string{

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

        public function build_ssl_provider(DockerComposeRecipe $recipe): void{
            $challenge_mode = env('CERTBOT_CHALLENGE_MODE');

            switch($challenge_mode){
                case 'dns-cloudflare':
                    $this->build_certbot_dns_cloudflare($recipe);
                    break;
            }
        }

        /**
         * @param DockerComposeRecipe $recipe
         *
         * @throws BindingResolutionException
         */
        private function build_certbot_dns_cloudflare(DockerComposeRecipe $recipe): void{
            $recipe->add_container(CertbotCloudflare::class, [
                'cloudflare_token' => env('CLOUDFLARE_TOKEN'),
            ]);
        }

        public function compute_certificate_location(string $hostname): string{
            return "/etc/letsencrypt/live/$hostname/fullchain.pem";
        }

        public function compute_certificate_key_location(string $hostname): string{
            return "/etc/letsencrypt/live/$hostname/privkey.pem";
        }
    }
