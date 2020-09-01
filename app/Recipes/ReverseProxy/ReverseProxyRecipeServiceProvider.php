<?php

    namespace App\Recipes\ReverseProxy;

    use App\Recipes\DockerComposeRecipe;
    use App\Recipes\ReverseProxy\ReverseProxyRecipe;
    use Illuminate\Support\Facades\Config;
    use Illuminate\Support\ServiceProvider;

    class ReverseProxyRecipeServiceProvider extends ServiceProvider{

        public function register(){

            if(env('RECIPE')==ReverseProxyRecipe::LABEL){
                $this->app->singleton(DockerComposeRecipe::class, ReverseProxyRecipe::class);
            }
        }


        public function boot(){
            $recipes = Config::get('recipes', []);
            $recipes[ReverseProxyRecipe::LABEL] = ReverseProxyRecipe::class;
            Config::set('recipes', $recipes);
        }
    }
