<?php

    namespace App\Commands\Docker;

    use App\Services\DockerService;
    use App\Services\TerminalService;
    use LaravelZero\Framework\Commands\Command;

    /**
     * Class Shell
     * @package App\Commands
     */
    class Ps extends Command{
        /**
         * The signature of the command.
         *
         * @var string
         */
        protected $signature = 'ps';

        /**
         * The description of the command.
         *
         * @var string
         */
        protected $description = 'Display docker ps command';

        /**
         * Execute the console command.
         *
         * @param TerminalService $terminal
         * @param DockerService $docker_service
         * @return mixed
         */
        public function handle(TerminalService $terminal){

            $commands = [
              "docker",
              "ps",
            ];

            $container_name = $this->argument('container_name');
            if(!empty($container_name)){
                $commands[] = $container_name;
            }

            return $terminal->execute($commands);

        }


    }
