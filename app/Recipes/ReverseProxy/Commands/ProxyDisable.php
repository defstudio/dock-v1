<?php


    namespace App\Recipes\ReverseProxy\Commands;


    use App\Containers\Nginx;
    use App\Recipes\ReverseProxy\Services\TargetsService;
    use App\Services\DockerService;
    use App\Services\TerminalService;
    use LaravelZero\Framework\Commands\Command;

    class ProxyDisable extends Command{
        protected $signature = 'proxy:disable
                                {target : project_name:port or hostname:port to disable}
                               ';

        protected $description = 'Disables a proxy target';

        public function handle(DockerService $docker_service, TerminalService $terminal, TargetsService $targets){

            $command_target_id = $this->argument('target');

            $target = $targets->get_target($command_target_id);

            if(empty($target)){
                $this->error("Unknown target $command_target_id");
                $this->line("Please use project_name:port or hostname:port");
                return false;
            }

            $target->active = 0;

            $targets->set_target($target);

            /** @var Nginx $nginx */
            $nginx =$docker_service->service('nginx');
            $nginx->reset_proxies();
            $targets->make_proxies($nginx);
            $nginx->publish_assets();



            return $this->call('nginx:reload');
        }
    }
