<?php

    namespace App\Providers;


    use App\Recipes\DockerComposeRecipe;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\ServiceProvider;

    class DockerComposeServiceProvider extends ServiceProvider{
        public function register(){

        }


        public function boot(){


            try{
                $recipe = $this->app->make(DockerComposeRecipe::class);
                $recipe->build();

                $this->commands($recipe->commands());

                $recipe->setup();
            } catch(BindingResolutionException $e){
                Log::error('No recipe is defined');
            }


        }


    }
