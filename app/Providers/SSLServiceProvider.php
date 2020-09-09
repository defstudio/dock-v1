<?php

    namespace App\Providers;


    use App\Contracts\SSLService;
    use App\Exceptions\SSLProviderException;
    use App\Recipes\DockerComposeRecipe;
    use App\Recipes\ReverseProxy\ReverseProxyRecipe;

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Illuminate\Support\ServiceProvider;

    class SSLServiceProvider extends ServiceProvider{


        public function register(){

            $this->app->bind(SSLService::class, function(){
                $ssl_provider_class = env('SSL_PROVIDER');

                if(!class_exists($ssl_provider_class)) throw SSLProviderException::provider_not_found($ssl_provider_class);

                return new $ssl_provider_class;
            });

        }


    }
