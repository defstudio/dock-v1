<?php


    namespace App\Containers;


    use Illuminate\Support\Facades\Storage;

    class CertbotStandalone extends Container{
        protected string $service_name = 'certbot-standalone';

        const PATH_LETSENCRYPT_DIR = 'certbot/letsencrypt';

        protected array $service_definition = [
            'image' => 'certbot/certbot',
        ];



        protected array $volumes = [
            self::HOST_CONFIG_VOLUME_PATH . self::PATH_LETSENCRYPT_DIR => '/etc/letsencrypt',
        ];

        public function __construct(){
            parent::__construct();
        }

        public function commands(): array{
            return [
                Commands\CertbotStandalone::class,
            ];
        }
    }
