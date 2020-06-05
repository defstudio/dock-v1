<?php

    namespace App\Commands;

    use App\Containers\Nginx;
    use App\Contracts\DockerComposeRecipe;
    use App\Exceptions\DuplicateNetworkException;
    use App\Exceptions\DuplicateServiceException;
    use App\Exceptions\ContainerException;
    use App\Services\DockerService;
    use App\Services\TerminalService;
    use LaravelZero\Framework\Commands\Command;

    class Start extends Command{

        protected $signature = 'start
                                {--build : rebuilds images before starting}';
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


            $this->task('Generating docker-compose files', function()use($docker_service){
                return $docker_service->publish();
            });




            $this->task('Starting containers', function()use($terminal){
                $command = [
                    'docker-compose',
                    'up',
                    '-d'
                ];


                if($this->option('build')){
                    $command[] = '--build';
                }


                $exit_code = $terminal->execute($command);

                if($exit_code>0){
                    return false;
                }

                return true;
            });

            return true;
        }

    }
