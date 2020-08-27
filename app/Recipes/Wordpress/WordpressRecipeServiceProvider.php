<?php

    namespace App\Recipes\Wordpress;

    use App\Recipes\DockerComposeRecipe;
    use Illuminate\Support\Facades\Config;
    use Illuminate\Support\ServiceProvider;

    class WordpressRecipeServiceProvider extends ServiceProvider{

        public function register(){

            if(env('RECIPE')==WordpressRecipe::LABEL){
                $this->app->singleton(DockerComposeRecipe::class, WordpressRecipe::class);
            }
        }


        public function boot(){
            $recipes = Config::get('recipes', []);
            $recipes[WordpressRecipe::LABEL] = WordpressRecipe::class;
            Config::set('recipes', $recipes);
        }
    }
