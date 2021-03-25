<?php /** @noinspection LaravelFunctionsInspection */


namespace App\Containers;


	use App\Exceptions\ContainerException;

    class Node extends Container{
        protected string $service_name = "node";

        protected array $service_definition = [
            'working_dir' => '/var/www',
            'image'       => 'node:13.8.0-alpine',
        ];

        protected array $volumes = [
            self::HOST_SRC_VOLUME_PATH => '/var/www'
        ];

        public function __construct(){
            parent::__construct();
            $this->set_user_uid(env('USER_ID'));
        }

        public function commands(): array{
           return [
               Commands\Node::class,
               Commands\Npm::class,
           ];
        }
    }
