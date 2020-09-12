<?php

    namespace App\Recipes\Laravel;

    use App\Recipes\DockerComposeRecipe;
    use Illuminate\Support\Facades\Config;
    use Illuminate\Support\ServiceProvider;

    class LaravelRecipeServiceProvider extends ServiceProvider{

        public function register(){

            if(env('RECIPE')==LaravelRecipe::LABEL){
                $this->app->singleton(DockerComposeRecipe::class, LaravelRecipe::class);
            }
        }


        public function boot(){
            $recipes = Config::get('recipes', []);
            $recipes[LaravelRecipe::class] = LaravelRecipe::LABEL;
            Config::set('recipes', $recipes);
        }
    }
