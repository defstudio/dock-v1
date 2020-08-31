<?php


	namespace App\Containers;



    class Redis extends Container{
        protected $service_name = "redis";

        protected $service_definition = [
            'restart' => 'unless-stopped',
            'image'       => 'redis:5',
            'expose'     => [6379],
        ];


    }
