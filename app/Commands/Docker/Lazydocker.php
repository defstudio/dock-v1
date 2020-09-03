<?php

    namespace App\Commands\Docker;

    use App\Services\DockerService;
    use App\Services\TerminalService;
    use LaravelZero\Framework\Commands\Command;

    /**
     * Class Shell
     * @package App\Commands
     */
    class Lazydocker extends Command{
        /**
         * The signature of the command.
         *
         * @var string
         */
        protected $signature = 'lazydocker';

        /**
         * The description of the command.
         *
         * @var string
         */
        protected $description = 'Run lazydocker for containers management';

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
              "run",
              "--rm",
              "-it",
              "-v",
              "/var/run/docker.sock:/var/run/docker.sock",
              "-v",
              "./configs/lazydocker:/.config/jesseduffield/lazydocker",
              "lazyteam/lazydocker",
            ];

            return $terminal->execute($commands);

        }


    }
