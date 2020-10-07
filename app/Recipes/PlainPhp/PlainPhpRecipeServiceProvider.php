<?php

    namespace App\Recipes\PlainPhp;

    use App\Recipes\DockerComposeRecipe;
    use Illuminate\Support\Facades\Config;
    use Illuminate\Support\ServiceProvider;

    class PlainPhpRecipeServiceProvider extends ServiceProvider{

        public function register(){

            if(env('RECIPE')==PlainPhpRecipe::LABEL){
                $this->app->singleton(DockerComposeRecipe::class, PlainPhpRecipe::class);
            }
        }


        public function boot(){
            $recipes = Config::get('recipes', []);
			$recipes[PlainPhpRecipe::class] = PlainPhpRecipe::LABEL;
            Config::set('recipes', $recipes);
        }
    }
