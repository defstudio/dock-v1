<?php


    namespace App\Containers\Commands;


    use App\Services\DockerService;
    use App\Services\TerminalService;
    use LaravelZero\Framework\Commands\Command;

    class CertbotCloudflare extends Command{

        protected $signature = 'certbot:cloudflare
                                {task : task to execute (create|renew)}
                               ';

        protected $description = 'Manage SSL certificates throught certbot cloudflare provider';

        public function handle(DockerService $docker_service, TerminalService $terminal): bool{

            $task = $this->argument('task');

            switch($task){
                case 'create':
                    return $this->create_certificate($docker_service, $terminal);
                case 'renew':
                    return $this->renew_certificates($docker_service, $terminal);
                default:
                    $this->error("Unrecognized task");
                    $this->line("please run 'dock help certbot:cloudflare' for a list of available options");
                    return false;
            }

        }

        private function create_certificate(DockerService $docker_service, TerminalService $terminal): bool{

            $this->title('Certbot certificate creation');

            $email = $this->ask('SSL Certificate owner email');

            $domains = [];

            while( !empty($domain = $this->ask('Enter domain (leave blank to skip)'))){
                $domains[] = $domain;
            }


            if(empty($domains)){
                $this->error('No domain selected');
                $this->line('Enter at lease one domain to bind the certificate to');
                return false;
            }

            $command = [
                'certonly',
                '--dns-cloudflare',
                '--dns-cloudflare-credentials',
                '/root/cloudflare.ini',
                '--dns-cloudflare-propagation-seconds',
                60,
                "--email",
                $email,
                "--agree-tos",
                "--no-eff-email",
                "--force-renewal",
            ];

            foreach($domains as $domain){
                $command[] = "-d $domain";
            }

            return $docker_service->service('certbot-cloudflare')->run($terminal, $command);

        }

        private function renew_certificates(DockerService $docker_service, TerminalService $terminal): bool{
            $this->title('Certbot certificate renewal');

            $command = [
                'renew',
                '--dns-cloudflare',
                '--dns-cloudflare-credentials',
                '/root/cloudflare.ini',
                '--dns-cloudflare-propagation-seconds',
                60,
                "--agree-tos",
                "--no-eff-email",
                "--force-renewal",
            ];

            return $docker_service->service('certbot-cloudflare')->run($terminal, $command);

        }

    }
