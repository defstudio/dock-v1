<?php

    namespace App\Providers;

    use App\Services\DockerService;
    use App\Services\TerminalService;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Support\ServiceProvider;
    use Illuminate\Support\Str;
    use Symfony\Component\Process\Process;

    class AppServiceProvider extends ServiceProvider{
        /**
         * Bootstrap any application services.
         *
         * @return void
         * @throws BindingResolutionException
         *
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
