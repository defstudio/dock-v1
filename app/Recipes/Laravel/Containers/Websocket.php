<?php


    namespace App\Recipes\Laravel\Containers;


    use App\Containers\Container;
    use App\Exceptions\ContainerException;

    class Websocket extends Container{
        protected string $service_name = "websocket";

        protected array $service_definition = [
            'restart' => 'unless-stopped',
            'working_dir' => '/var/www',
            'expose' => [6001],
            'build'       => [
                'context' => 'https://gitlab.com/defstudio/docker/laravel-websocket.git',
            ],
        ];

        protected array $volumes = [
            self::HOST_SRC_VOLUME_PATH => '/var/www'
        ];

        /**
         * Php constructor.
         * @throws ContainerException
         */
        public function __construct(){
            parent::__construct();
            $this->set_user_uid();
        }
    }
