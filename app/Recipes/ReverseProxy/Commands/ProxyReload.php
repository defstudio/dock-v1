<?php


    namespace App\Recipes\ReverseProxy\Commands;


    use App\Containers\Nginx;
    use App\Contracts\SSLService;
    use App\Recipes\ReverseProxy\Services\TargetsService;
    use App\Services\DockerService;
    use App\Services\TerminalService;
    use LaravelZero\Framework\Commands\Command;
    use stdClass;

    class ProxyReload extends Command{
        protected $signature = 'proxy:reload';

        protected $description = 'Reloads all proxy targets';

        public function handle(DockerService $docker_service, TargetsService $targets){

            /** @noinspection PhpParamsInspection */
            return $targets->reload_targets($docker_service->service('nginx'), $this);
        }



    }
