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
                'context' => 'https://gitlab.com/defstudio/docker/php.git',
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

        public function set_version($version){
            $this->set_service_definition('build.args.PHP_VERSION', $version);
        }

        public function enable_xdebug(bool $enabled=true): self{
            $this->set_service_definition('build.args.ENABLE_XDEBUG', $enabled?1:0);
            return $this;
        }

        public function enable_libreoffice_writer(bool $enabled=true): self{
            $this->set_service_definition('build.args.ENABLE_LIBREOFFICE_WRITER', $enabled?1:0);
            return $this;
        }


        /**
         * Php constructor.
         *
         * @throws ContainerException
         */
        public function __construct(){
            parent::__construct();
            $this->set_user_uid();
        }

    }
