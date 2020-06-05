<?php


    namespace App\Containers;



    class MailHog extends Container{
        protected $service_name = 'mailhog';

        protected $service_definition = [
            'restart'     => 'unless-stopped',
            'expose'      => [1025],
            'image'       => 'mailhog/mailhog:latest',
        ];

    }
