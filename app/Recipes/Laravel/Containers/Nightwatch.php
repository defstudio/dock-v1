<?php


namespace App\Recipes\Laravel\Containers;


use App\Containers\Php;

class Nightwatch extends Php
{
    protected string $service_name = 'nightwatch';

    public function __construct()
    {
        parent::__construct();

        $this->set_target('nightwatch');
        $this->set_service_definition('expose', [2407]);
    }
}
