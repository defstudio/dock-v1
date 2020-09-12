<?php


    namespace App\Containers;


    use Illuminate\Support\Facades\Storage;

    class CertbotCloudflare extends Container{
        protected string $service_name = 'certbot-cloudflare';

        const PATH_LETSENCRYPT_DIR = 'certbot/letsencrypt';
        const PATH_CLOUDFLARE_INI = 'certbot/cloudflare.ini';

        private string $cloudflare_token;

        protected array $service_definition = [
            'image' => 'certbot/dns-cloudflare',
        ];

        protected array $volumes = [
            self::HOST_CONFIG_VOLUME_PATH . self::PATH_LETSENCRYPT_DIR => '/etc/letsencrypt',
            self::HOST_CONFIG_VOLUME_PATH . self::PATH_CLOUDFLARE_INI  => '/root/cloudflare.ini',
        ];

        public function __construct(string $cloudflare_token){
            parent::__construct();

            $this->cloudflare_token = $cloudflare_token;
        }

        public function publish_assets(){
            $this->publish_cloudflare_ini();
        }

        private function publish_cloudflare_ini(){
            $template = Storage::get(self::PATH_CLOUDFLARE_INI);
            $this->compile_template($template, [
                'dns_cloudflare_api_token'      => $this->cloudflare_token,
            ]);
            $this->disk()->put(self::PATH_CLOUDFLARE_INI, $template);
        }

        public function commands(): array{
            return [
                Commands\CertbotCloudflare::class,
            ];
        }


    }
