<?php


	namespace App\Contracts;


	use App\Recipes\DockerComposeRecipe;
    use Illuminate\Console\Command;

    interface SSLService{

        public function init_recipe(Command $parent_command, string $env_content): string;

        public function build_ssl_provider(DockerComposeRecipe $recipe): void;

        public function compute_certificate_location(string $hostname): string;

        public function compute_certificate_key_location(string $hostname): string;

        public function reserved_ports(): array;
    }
