<?php


    namespace App\Containers;



    class SeleniumChrome extends Container{
        protected string $service_name = 'selenium';

        protected array $service_definition = [
            'restart'     => 'unless-stopped',
            'image'       => 'selenium/standalone-chrome',
        ];

        protected array $volumes = [
            '/dev/shm:/dev/shm'
        ];
    }
