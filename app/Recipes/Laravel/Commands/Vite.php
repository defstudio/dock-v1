<?php


    namespace App\Recipes\Laravel\Commands;

    use App\Exceptions\DockerServiceNotFoundException;
    use App\Services\DockerService;
    use App\Services\TerminalService;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use LaravelZero\Framework\Commands\Command;

    class Vite extends Command{
        protected $signature = 'vite';

        protected $description = 'Launch vite server';

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
                "npm",
                "run",
                "dev",
            ];


            return $docker_service->service('node')->run($terminal, $commands);


        }
    }
