<?php


namespace App\Recipes\Angular;


use App\Recipes\DockerComposeRecipe;
use App\Recipes\PlainPhp\PlainPhpRecipe;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AngularRecipeServiceProvider extends ServiceProvider
{
    public function register()
    {
        if(env('RECIPE') == AngularRecipe::LABEL){
            $this->app->singleton(DockerComposeRecipe::class, AngularRecipe::class);
        }
    }

    public function boot(){
        $recipes = Config::get('recipes', []);
        $recipes[AngularRecipe::class] = AngularRecipe::LABEL;
        Config::set('recipes', $recipes);
    }
}
