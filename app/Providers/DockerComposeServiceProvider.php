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

            } catch(BindingResolutionException $e){
            }

            if(!empty($recipe)){
                $recipe->build();

                $this->commands($recipe->commands());

                $recipe->setup();
            }



        }


    }
