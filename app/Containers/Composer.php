<?php


	namespace App\Containers;


	use App\Exceptions\ContainerException;

    class Composer extends Container{
        protected $service_name = "composer";

        protected $service_definition = [
            'working_dir' => '/var/www',
            'build'       => [
                'context' => 'git@gitlab.com:defstudio/docker/composer.git',
            ],
        ];

        protected $volumes = [
            self::HOST_SRC_VOLUME_PATH => '/var/www'
        ];

        /**
         * Composer constructor.
         * @throws ContainerException
         */
        public function __construct(){
            parent::__construct();
            $this->set_user_uid();
        }

        public function commands(): array{
            return [
                Commands\Composer::class
            ];
        }


    }
