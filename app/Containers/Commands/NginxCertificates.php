<?php


	namespace App\Containers\Commands;


	use App\Containers\Nginx;
    use App\Exceptions\DockerServiceNotFoundException;
    use App\Services\DockerService;
    use App\Services\TerminalService;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use Illuminate\Support\Facades\Artisan;
    use LaravelZero\Framework\Commands\Command;

    class NginxCertificates extends Command{
        protected $signature = 'nginx:certificates
                                {task : task to execute (create|renew)}
                               ';
        protected $description = "Nginx Let's Encrypt certificates management";

        /**
         * @param DockerService $docker_service
         * @param TerminalService $terminal
         * @return int
         * @throws DockerServiceNotFoundException
         * @throws BindingResolutionException
         */
        public function handle(DockerService $docker_service, TerminalService $terminal){

            if($this->argument('task')=='create'){
                $docker_service->service('nginx')->execute($terminal, [
                    'certbot',
                    '--nginx',
                ]);
            }else if($this->argument('task')=='renew'){
                $docker_service->service('nginx')->execute($terminal, [
                    'certbot',
                    'renew',
                ]);
            }

            return true;
        }
	}
