<?php


    namespace App\Containers\Commands;


    use App\Services\DockerService;
    use App\Services\TerminalService;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
    use LaravelZero\Framework\Commands\Command;

    class CertbotCloudflare extends Command{

        protected $signature = 'certbot:cloudflare
                                {task : task to execute (create|renew)}
                                {--token=}
                               ';

        protected $description = 'Manage SSL certificates throught certbot cloudflare provider';

        public function handle(DockerService $docker_service, TerminalService $terminal): bool{

            $task = $this->argument('task');
            $token = $this->option('token');

            switch($task){
                case 'create':
                    return $this->create_certificate($docker_service, $terminal, $token);
                case 'renew':
                    return $this->renew_certificates($docker_service, $terminal, $token);
                default:
                    $this->error("Unrecognized task");
                    $this->line("please run 'dock help certbot:cloudflare' for a list of available options");
                    return false;
            }

        }

        private function create_certificate(DockerService $docker_service, TerminalService $terminal, string|null $token): bool{

            $this->title('Certbot certificate creation');

            $email = $this->ask('SSL Certificate owner email');

            $domains = [];

            while( !empty($domain = $this->ask('Enter domain (leave blank to skip)'))){
                $domains[] = $domain;
            }

            if($token === null){
                $token_file = '/root/cloudflare.ini';
            }else{
                $token_file = "/root/cloudflare_$token.ini";
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
                $token_file,
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

        private function renew_certificates(DockerService $docker_service, TerminalService $terminal, string|null $token): bool{
            $this->title('Certbot certificate renewal');

            if($token === null){
                $token_file = '/root/cloudflare.ini';
            }else{
                $token_file = "/root/cloudflare_$token.ini";
            }

            collect(Storage::disk('configs')->files('certbot/letsencrypt/renewal'))
                ->filter(function (string $file) {
                    $content = Storage::disk('configs')->get($file);
                    return Str::of($content)->contains("authenticator = dns-cloudflare");
                })->map(fn (string $file): string => Str::of($file)->afterLast("/")->before('.conf'))
                ->each(function (string $domain) use ($token_file, $terminal, $docker_service) {
                    $command = [
                        'renew',
                        '--dns-cloudflare',
                        '--dns-cloudflare-credentials',
                        $token_file,
                        '--dns-cloudflare-propagation-seconds',
                        60,
                        "--agree-tos",
                        "--no-eff-email",
                        "--force-renewal",
                        "--cert-name",
                        $domain,
                    ];

                    $docker_service->service('certbot-cloudflare')->run($terminal, $command);
                });


            return self::SUCCESS;
        }
    }
