<?php


    namespace App\Containers;



    class MailHog extends Container{
        protected string $service_name = 'mailhog';

        protected array $service_definition = [
            'restart'     => 'unless-stopped',
            'expose'      => [1025],
            'image'       => 'mailhog/mailhog:latest',
        ];

    }
