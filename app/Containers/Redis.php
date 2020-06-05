<?php


	namespace App\Containers;



    class Redis extends Container{
        protected $service_name = "redis";

        protected $service_definition = [
            'restart' => 'always',
            'image'       => 'redis:5',
            'expose'     => [6379],
        ];


    }
