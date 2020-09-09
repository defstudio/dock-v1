<?php


    namespace App\Recipes\ReverseProxy\Commands;


    use App\Containers\Nginx;
    use App\Recipes\ReverseProxy\Services\TargetsService;
    use App\Services\DockerService;
    use App\Services\TerminalService;
    use LaravelZero\Framework\Commands\Command;

    class ProxyEnable extends Command{
        protected $signature = 'proxy:enable
                                {target : project_name:port or hostname:port to enable}
                               ';

        protected $description = 'Enables a proxy target';

        public function handle(DockerService $docker_service, TerminalService $terminal, TargetsService $targets){

            $command_target_id = $this->argument('target');

            $target = $targets->get_target($command_target_id);

            if(empty($target)){
                $this->error("Unknown target $command_target_id");
                $this->line("Please use project_name:port or hostname:port");
                return false;
            }

            $target->active = 1;

            $targets->set_target($target);


            /** @noinspection PhpParamsInspection */
            return $targets->reload_targets($docker_service->service('nginx'), $this);

        }
    }
