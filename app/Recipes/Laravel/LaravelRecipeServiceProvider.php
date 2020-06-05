<?php

    namespace App\Recipes\Laravel;

    use App\Contracts\DockerComposeRecipe;
    use Illuminate\Support\ServiceProvider;

    class LaravelRecipeServiceProvider extends ServiceProvider{

        public function register(){
            if(env('RECIPE')==LaravelRecipe::LABEL){
                $this->app->singleton(DockerComposeRecipe::class, LaravelRecipe::class);
            }
        }


        public function boot(){

        }
    }
