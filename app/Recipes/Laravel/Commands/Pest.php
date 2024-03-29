<?php


    namespace App\Recipes\Laravel\Commands;


    use App\Exceptions\DockerServiceNotFoundException;
    use App\Services\DockerService;
    use App\Services\TerminalService;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use LaravelZero\Framework\Commands\Command;

    class Pest extends Command{
        protected $signature = 'pest';

        protected $description = 'Starts Laravel tests with Pest';

        /**
         * @param DockerService $docker_service
         * @param TerminalService $terminal
         *
         * @return int
         * @throws DockerServiceNotFoundException
         */
        public function handle(DockerService $docker_service, TerminalService $terminal){

            $terminal->init($this->output);

            $commands = array_merge([
                'php',
                './vendor/bin/pest',
            ]);

            return $docker_service->service('php')->run($terminal, $commands);
        }
    }
