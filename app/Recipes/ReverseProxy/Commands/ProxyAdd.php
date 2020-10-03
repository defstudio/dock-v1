<?php


    namespace App\Recipes\ReverseProxy\Commands;


    use App\Containers\Nginx;
    use App\Contracts\SSLService;
    use App\Recipes\ReverseProxy\Services\TargetsService;
    use App\Services\DockerService;
    use App\Services\TerminalService;
    use LaravelZero\Framework\Commands\Command;
    use stdClass;

    class ProxyAdd extends Command{
        protected $signature = 'proxy:add';

        protected $description = 'Creates a new proxy target';

        public function handle(DockerService $docker_service, TerminalService $terminal, TargetsService $targets){

            $terminal->init($this->output);

            $this->title("New Proxy Wizard");

            $creation_mode = $this->menu("Select proxy creation mode", [
                'standard' => 'Standard',
                'advanced' => 'Advanced',
            ])->open();

            switch($creation_mode){
                case 'standard':
                    $target = $this->standard_target_wizard();
                    break;
                default:
                    $target = $this->advanced_target_wizard();
                    break;

            }

            $targets->set_target($target);

            /** @noinspection PhpParamsInspection */
            return $targets->reload_targets($docker_service->service('nginx'), $this);
        }

        private function standard_target_wizard(): object{
            $target = new stdClass();

            $target->active = 1;

            $target->project = $this->ask('Enter project name');
            $target->hostname = $this->ask('Enter hostname');
            $target->port = $this->ask('Enter port');


            $target->subdomains = [];

            while(($subdomain = $this->ask('Enter a subdomain to bind (leave blank to skip)', '')) != ''){

                $public_visibility = $this->confirm("Should $subdomain.{$target->hostname} be visible outside local network?");

                $target->subdomains[$subdomain] = $public_visibility?'public':'local';
            }


            if($target->port == 443){
                /** @var SSLService $ssl_service */
                $ssl_service = app(SSLService::class);

                $target->ssl_certificate = $this->ask('Enter SSL certificate location', $ssl_service->compute_certificate_location($target->hostname));
                $target->ssl_certificate_key = $this->ask('Enter SSL certificate key location', $ssl_service->compute_certificate_key_location($target->hostname));
            }


            return $target;

        }

        private function advanced_target_wizard(): object{
            $target = new stdClass();

            $target->active = 1;

            $target->hostname = $this->ask('Enter hostname');
            $target->port = $this->ask('Enter port:');

            $target->destination_hostname = $this->ask('Enter destination hostname');
            $target->destination_port = $this->ask('Enter destination port',  $target->port);

            if($target->port == 443){
                /** @var SSLService $ssl_service */
                $ssl_service = app(SSLService::class);

                $target->ssl_certificate = $this->ask('Enter SSL certificate location', $ssl_service->compute_certificate_location($target->hostname));
                $target->ssl_certificate_key = $this->ask('Enter SSL certificate key location', $ssl_service->compute_certificate_key_location($target->hostname));
            }

            return $target;
        }


    }
