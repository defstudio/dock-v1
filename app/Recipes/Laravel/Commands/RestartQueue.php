<?php


    namespace App\Recipes\Laravel\Commands;

    use App\Exceptions\DockerServiceNotFoundException;
    use App\Services\DockerService;
    use App\Services\TerminalService;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use LaravelZero\Framework\Commands\Command;

    class RestartQueue extends Command{
        protected $signature = 'queue:restart';

        protected $description = 'Restarts queue workers';

        /**
         * @param DockerService $docker_service
         * @param TerminalService $terminal
         * @return int
         * @throws DockerServiceNotFoundException
         * @throws BindingResolutionException
         */
        public function handle(DockerService $docker_service, TerminalService $terminal){

            $terminal->init($this->output);

            $commands = [
                "php",
                "/var/www/artisan",
                "queue:restart",
            ];


            return $docker_service->service('worker')->run($terminal, $commands);


        }
    }
