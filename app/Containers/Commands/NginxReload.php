<?php


	namespace App\Containers\Commands;


	use App\Containers\Nginx;
    use App\Exceptions\DockerServiceNotFoundException;
    use App\Services\DockerService;
    use App\Services\TerminalService;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use LaravelZero\Framework\Commands\Command;

    class NginxReload extends Command{
        protected $signature = 'nginx:reload';
        protected $description = 'Soft reloads Nginx configuration';

        /**
         * @param DockerService $docker_service
         * @param TerminalService $terminal
         * @return int
         * @throws DockerServiceNotFoundException
         * @throws BindingResolutionException
         */
        public function handle(DockerService $docker_service, TerminalService $terminal){

                $this->task('Executing Nginx soft reload', function() use ($docker_service, $terminal){
                     $docker_service->service('nginx')->execute($terminal, [
                        'nginx',
                        '-s',
                        'reload',
                     ]);
                });

                $commands = array_merge(['composer'], $composer_commands);
                return $docker_service->service('composer')->execute($terminal, $commands);



        }
	}
