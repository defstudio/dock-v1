<?php


    namespace App\Containers\Commands;

    use App\Exceptions\DockerServiceNotFoundException;
    use App\Services\DockerService;
    use App\Services\TerminalService;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use LaravelZero\Framework\Commands\Command;

    class Node extends Command{
        protected $signature = 'node
                                {commands* : node commands to execute} ';

        protected $description = 'Executes a node command';

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
                "node",
            ];

            $composer_commands = $this->argument("commands");
            if(!empty($composer_commands)){
                $commands = array_merge($commands, $composer_commands);
            }
            return $docker_service->service('node')->execute($terminal, $commands);


        }
    }
