<?php

    namespace App\Commands\Docker;

    use App\Services\DockerService;
    use App\Services\TerminalService;
    use LaravelZero\Framework\Commands\Command;
    use NunoMaduro\LaravelConsoleMenu\Menu;

    /**
     * Class Shell
     * @method Menu menu($name, $options = [])
     * @package App\Commands
     */
    class Stats extends Command{
        /**
         * The signature of the command.
         *
         * @var string
         */
        protected $signature = 'stats
                                {container_name?}';

        /**
         * The description of the command.
         *
         * @var string
         */
        protected $description = 'Display docker stats';

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
              "stats",
            ];

            $container_name = $this->argument('container_name');
            if(!empty($container_name)){
                $commands[] = $container_name;
            }

            return $terminal->execute($commands);

        }


    }
