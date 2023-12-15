<?php


    namespace App\Recipes\Laravel\Commands;


    use App\Exceptions\DockerServiceNotFoundException;
    use App\Services\DockerService;
    use App\Services\TerminalService;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use LaravelZero\Framework\Commands\Command;

    class OpcacheReset extends Command{
        protected $signature = 'opcache:reset';

        protected $description = 'Reset OpCache';

        /**
         * @param DockerService $docker_service
         * @param TerminalService $terminal
         * @return int
         * @throws DockerServiceNotFoundException
         * @throws BindingResolutionException
         */
        public function handle(DockerService $docker_service, TerminalService $terminal){

            $terminal->init($this->output);

            return $docker_service->service('php')->execute($terminal, [
                "php",
                "/usr/bin/cachetool.phar",
                "opcache:reset",
            ]);
        }
    }
