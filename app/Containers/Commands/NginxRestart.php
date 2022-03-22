<?php


	namespace App\Containers\Commands;


	use App\Containers\Nginx;
    use App\Exceptions\DockerServiceNotFoundException;
    use App\Services\DockerService;
    use App\Services\TerminalService;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use LaravelZero\Framework\Commands\Command;

    class NginxRestart extends Command{
        protected $signature = 'nginx:restart';
        protected $description = 'Restart Nginx';

        /**
         * @param DockerService $docker_service
         * @param TerminalService $terminal
         * @return int
         * @throws DockerServiceNotFoundException
         * @throws BindingResolutionException
         */
        public function handle(DockerService $docker_service, TerminalService $terminal){

                return $this->task('Executing Nginx restart', function() use ($docker_service, $terminal){
                    return $docker_service->service('nginx')->execute($terminal, [
                        'service',
                        'nginx',
                        'restart',
                     ]);
                });
        }
	}
