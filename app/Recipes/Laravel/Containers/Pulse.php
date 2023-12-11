<?php


namespace App\Recipes\Laravel\Containers;


use App\Containers\Php;

class Pulse extends Php
{
    protected string $service_name = 'pulse';

    public function __construct()
    {
        parent::__construct();
        $this->set_target('pulse');
    }
}
