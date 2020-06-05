<?php


    namespace App\Recipes\Laravel\Commands;


    use App\Exceptions\DockerServiceNotFoundException;
    use App\Services\DockerService;
    use App\Services\TerminalService;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use LaravelZero\Framework\Commands\Command;

    class Artisan extends Command{
        protected $signature = 'artisan';

        protected $description = 'Launch an Artisan shell';

        /**
         * @param DockerService $docker_service
         * @param TerminalService $terminal
         * @return int
         * @throws DockerServiceNotFoundException
         * @throws BindingResolutionException
         */
        public function handle(DockerService $docker_service, TerminalService $terminal){

            $terminal->init($this->output);

            $this->info('Log into Artisan Shell');

            return $terminal->execute([
                'docker-compose',
                'exec',
                'php',
                'bash',
            ]);



        }
    }
