<?php


	namespace App\Containers;


	use App\Exceptions\ContainerException;

    class Node extends Container{
        protected $service_name = "node";

        protected $service_definition = [
            'working_dir' => '/var/www',
            'image'       => 'node:13.8.0-alpine',
            'volumes'     => [
                './src/:/var/www',
            ],
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
               Commands\Node::class,
           ];
        }
    }
