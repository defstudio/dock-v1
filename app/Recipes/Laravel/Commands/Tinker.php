<?php


    namespace App\Recipes\Laravel\Commands;


    use App\Exceptions\DockerServiceNotFoundException;
    use App\Services\DockerService;
    use App\Services\TerminalService;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use LaravelZero\Framework\Commands\Command;

    class Tinker extends Command{
        protected $signature = 'tinker';

        protected $description = 'Starts Laravel tinker shell';

        /**
         * @param DockerService $docker_service
         * @param TerminalService $terminal
         *
         * @return int
         * @throws DockerServiceNotFoundException
         * @throws BindingResolutionException
         */
        public function handle(DockerService $docker_service, TerminalService $terminal){

            $terminal->init($this->output);


            $commands = array_merge([
                'php',
                'artisan',
                'tinker',
            ]);
            return $docker_service->service('php')->run($terminal, $commands);
        }
    }
