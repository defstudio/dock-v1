<?php

    namespace App\Commands;

    use App\Exceptions\DuplicateNetworkException;
    use App\Exceptions\DuplicateServiceException;
    use App\Exceptions\ContainerException;
    use App\Services\DockerService;
    use App\Services\TerminalService;
    use LaravelZero\Framework\Commands\Command;

    class Start extends Command{

        protected $signature = 'start
                                {--build : rebuilds images before starting}
                                {--remove-orphans : remove orphans containers}';
        protected $description = 'Launch docker containers';


        /**
         * Execute the console command.
         *
         * @param DockerService $docker_service
         * @param TerminalService $terminal
         * @return mixed
         * @throws ContainerException
         * @throws DuplicateServiceException
         * @throws DuplicateNetworkException
         */
        public function handle(DockerService $docker_service, TerminalService $terminal){


            $terminal->init($this->output);


            $this->task('Generating docker-compose files', function() use ($docker_service){
                return $docker_service->publish();
            });


            $this->task('Starting containers', function() use ($terminal){
                $command = [
                    'COMPOSE_DOCKER_CLI_BUILD=1 DOCKER_BUILDKIT=1',
                    'docker-compose',
                    'up',
                    '-d',
                ];


                if($this->option('build')) $command[] = '--build';
                if($this->option('remove-orphans')) $command[] = '--remove-orphans';


                $exit_code = $terminal->execute($command);

                if($exit_code > 0){
                    return false;
                }

                return true;
            });


            return true;
        }

    }
