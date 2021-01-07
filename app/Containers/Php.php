<?php


    namespace App\Containers;


    use App\Exceptions\ContainerException;
    use Illuminate\Contracts\Filesystem\Filesystem;
    use Illuminate\Support\Facades\Storage;

    class Php extends Container{

        protected string $service_name = "php";

        protected array $service_definition = [
            'restart'     => 'unless-stopped',
            'working_dir' => '/var/www',
            'build'       => [
                'context' => 'https://github.com/def-studio/docker-php.git',
                'target' => 'fpm',
                'args'    => [
                    'ENABLE_XDEBUG' => 0,
                    'ENABLE_LIBREOFFICE_WRITER' => 0,
                ],
            ],
            'expose'      => [9000],
        ];

        protected array $volumes = [
            self::HOST_SRC_VOLUME_PATH => '/var/www',
        ];

        public function set_target($target): self{
            $this->set_service_definition('build.target', $target);
            return $this;
        }

        public function set_version($version): self{
            $this->set_service_definition('build.args.PHP_VERSION', $version);
            return $this;
        }

        public function enable_xdebug(bool $enabled=true): self{
            $this->set_service_definition('build.args.ENABLE_XDEBUG', $enabled?1:0);
            return $this;
        }

        public function enable_libreoffice_writer(bool $enabled=true): self{
            $this->set_service_definition('build.args.ENABLE_LIBREOFFICE_WRITER', $enabled?1:0);
            return $this;
        }


        public function __construct(){
            parent::__construct();

            $this->set_user_uid();

            if(env('ENV', 'local') == 'local'){
                $this->enable_xdebug();
            }

            if(env('ENABLE_LIBREOFFICE_WRITER', 'local') == '1'){
                $this->enable_libreoffice_writer();
            }

            if(!empty(env('PHP_VERSION'))){
                $this->set_version(env('PHP_VERSION'));
            }
        }

    }
