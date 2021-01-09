<?php


    namespace App\Containers\Commands;

    use App\Exceptions\DockerServiceNotFoundException;
    use App\Services\DockerService;
    use App\Services\TerminalService;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use LaravelZero\Framework\Commands\Command;

    class Composer extends Command{
        protected $signature = 'composer';

        protected $description = 'Runs a composer command';

        public function __construct()
        {
            parent::__construct();
            $this->ignoreValidationErrors();
        }

        /**
         * @param DockerService   $docker_service
         * @param TerminalService $terminal
         *
         * @return int
         * @throws DockerServiceNotFoundException
         * @throws BindingResolutionException
         */
        public function handle(DockerService $docker_service, TerminalService $terminal){

            $terminal->init($this->output);

            $arguments = (string) $this->input;

            if(empty($arguments)){
                $this->info('Log into Composer Shell');

                return $terminal->execute([
                    'docker-compose',
                    'run',
                    '--rm',
                    'composer',
                    'bash',
                ]);
            } else{
                $composer_commands = explode(' ', $arguments);
                $commands = array_merge(['composer'], $composer_commands);
                return $docker_service->service('composer')->run($terminal, $commands);
            }


        }
    }
