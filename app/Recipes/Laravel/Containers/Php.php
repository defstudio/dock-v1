<?php


    namespace App\Recipes\Laravel\Containers;


    class Php extends \App\Containers\Php{
        public function get_service_definition(): array{

           $service_definition = parent::get_service_definition();

           $service_definition['build']['args']['ENABLE_LARAVEL_CRON'] = 1;

           return $service_definition;
        }

    }
