<?php


    namespace App\Containers;


    use App\Exceptions\ContainerException;
    use Illuminate\Contracts\Filesystem\Filesystem;
    use Illuminate\Support\Facades\Storage;

    class
    Php extends Container{

        protected string $service_name = "php";


        protected array $service_definition = [
            'restart'     => 'unless-stopped',
            'working_dir' => '/var/www',
            'build'       => [
                'context' => 'https://gitlab.com/defstudio/docker/php.git',
                'args' => [
                    'ENABLE_XDEBUG' => 0
                ]
            ],
            'expose' => [9000],
        ];

        protected array $volumes = [
          self::HOST_SRC_VOLUME_PATH => '/var/www'
        ];

        public function enable_xdebug(): self{
            $this->set_service_definition('build.args.ENABLE_XDEBUG', 1);
            return $this;
        }

        public function disable_xdebug(): self{
            $this->set_service_definition('build.args.ENABLE_XDEBUG', 1);
            return $this;
        }

        /**
         * Php constructor.
         * @throws ContainerException
         */
        public function __construct(){
            parent::__construct();
            $this->set_user_uid();
        }

    }
