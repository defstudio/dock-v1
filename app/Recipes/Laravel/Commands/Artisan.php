<?php


    namespace App\Recipes\Laravel\Commands;


    use App\Exceptions\DockerServiceNotFoundException;
    use App\Services\DockerService;
    use App\Services\TerminalService;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use LaravelZero\Framework\Commands\Command;

    class Artisan extends Command{
        protected $signature = 'artisan
                                {commands?* : artisan commmands to execute}
                               ';

        protected $description = 'Executes an Artisan command';

        /**
         * @param DockerService $docker_service
         * @param TerminalService $terminal
         * @return int
         * @throws DockerServiceNotFoundException
         * @throws BindingResolutionException
         */
        public function handle(DockerService $docker_service, TerminalService $terminal){

            $terminal->init($this->output);

            $artisan_commands = $this->argument("commands");

            if(empty($artisan_commands)){
                $this->info('Log into Artisan Shell');

                return $terminal->execute([
                    'docker-compose',
                    'exec',
                    'php',
                    'bash',
                ]);
            }else{
                $commands = array_merge(['composer'], $artisan_commands);
                return $docker_service->service('artisan')->run($terminal, $commands);
            }




        }
    }
