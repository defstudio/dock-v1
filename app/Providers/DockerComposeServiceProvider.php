<?php

    namespace App\Providers;

    use App\Contracts\DockerComposeRecipe;
    use Illuminate\Support\ServiceProvider;

    class DockerComposeServiceProvider extends ServiceProvider{
        public function register(){

        }

        public function boot(DockerComposeRecipe $recipe){

            $recipe->build();

            $this->commands($recipe->commands());

            $recipe->setup();
        }


    }
