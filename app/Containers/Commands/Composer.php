<?php


    namespace App\Containers\Commands;


    use App\Exceptions\DockerServiceNotFoundException;
    use App\Services\DockerService;
    use App\Services\TerminalService;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use LaravelZero\Framework\Commands\Command;

    class Composer extends Command{
        protected $signature = 'composer
                                {operation}
                                {package?}
                                ';

        protected $description = 'Launch an Artisan Migration';

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
                "composer",
                $this->argument('operation')
            ];

            if($this->hasArgument('package')){
                $commands[] = $this->argument('package');
            }

            return $docker_service->service('composer')->execute($terminal, $commands);


        }
    }
