<?php


	namespace App\Containers;



    class Memcached extends Container{
        protected string $service_name = "memcached";


        protected array $service_definition = [
            'restart' => 'unless-stopped',
            'image'       => 'memcached:alpine',
            'expose'     => [11211],
        ];
    }
