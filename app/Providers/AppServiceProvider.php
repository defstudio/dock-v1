<?php

    namespace App\Providers;

    use App\Services\DockerService;
    use App\Services\TerminalService;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Support\ServiceProvider;
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

            $this->app->bind('version', function(Application $app){
                $lastRevisionTag = '$(git rev-list --tags --max-count=1)';

                $task = [
                    'git',
                    'describe',
                    '--tags',
                    $lastRevisionTag,
                ];

                $process = tap(new Process($task, $app->basePath()))->run();

                return trim($process->getOutput()) ?: 'unreleased';
            });

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
