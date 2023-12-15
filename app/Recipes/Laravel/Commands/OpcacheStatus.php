<?php


    namespace App\Recipes\Laravel\Commands;


    use App\Exceptions\DockerServiceNotFoundException;
    use App\Services\DockerService;
    use App\Services\TerminalService;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use LaravelZero\Framework\Commands\Command;

    class OpcacheStatus extends Command{
        protected $signature = 'opcache:status
                                {--scripts} : show scripts cache status';

        protected $description = 'Show OpCache status';

        /**
         * @param DockerService $docker_service
         * @param TerminalService $terminal
         * @return int
         * @throws DockerServiceNotFoundException
         * @throws BindingResolutionException
         */
        public function handle(DockerService $docker_service, TerminalService $terminal){

            $terminal->init($this->output);


            if($this->option('scripts')){
                return $docker_service->service('php')->execute($terminal, [
                    "php",
                    "/usr/bin/cachetool.phar",
                    "opcache:status:scripts",
                ]);
            }

            return $docker_service->service('php')->execute($terminal, [
                "php",
                "/usr/bin/cachetool.phar",
                "opcache:status",
            ]);
        }
    }
