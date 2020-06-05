<?php

    namespace App\Providers;

    use App\Contracts\DockerComposeRecipe;
    use App\Services\DockerService;
    use App\Services\TerminalService;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use Illuminate\Support\ServiceProvider;
    use Symfony\Component\Process\Process;

    class AppServiceProvider extends ServiceProvider{
        /**
         * Bootstrap any application services.
         *
         * @throws BindingResolutionException
         *
         * @return void
         */
        public function boot(){

            $this->app->singleton(DockerService::class, DockerService::class);

            $this->app->bind(TerminalService::class, TerminalService::class);

        }

        /**
         * Register any application services.
         *
         * @return void
         *
         */
        public function register(){

        }
    }
