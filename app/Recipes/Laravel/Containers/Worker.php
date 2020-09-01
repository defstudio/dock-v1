<?php


    namespace App\Recipes\Laravel\Containers;


    use App\Containers\Container;
    use App\Exceptions\ContainerException;

    class Worker extends Container{
        protected $service_name = "worker";

        protected $service_definition = [
            'restart' => 'unless-stopped',
            'working_dir' => '/var/www',
            'build'       => [
                'context' => 'git@gitlab.com:defstudio/docker/laravel-worker.git',
            ],
        ];

        protected $volumes = [
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
