<?php


	namespace App\Services\SSL;


	use App\Contracts\SSLService;
    use App\Recipes\DockerComposeRecipe;
    use Illuminate\Console\Command;

    class SelfSigned implements SSLService{

        public function init_recipe(Command $parent_command, string $env_content): string{
            return $env_content;
        }

        public function build_ssl_provider(DockerComposeRecipe $recipe): void{
            //nothing to do
        }

        public function compute_certificate_location(string $hostname): string{
            return '/etc/nginx/ssl/nginx.cert';
        }

        public function compute_certificate_key_location(string $hostname): string{
            return '/etc/nginx/ssl/nginx.key';
        }
    }
