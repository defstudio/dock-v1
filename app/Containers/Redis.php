<?php


	namespace App\Containers;



    class Redis extends Container{
        protected string $service_name = "redis";

        protected array $service_definition = [
            'restart' => 'unless-stopped',
            'image'       => 'redis:5',
            'expose'     => [6379],
        ];


    }
