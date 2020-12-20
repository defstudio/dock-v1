<?php


namespace App\Recipes\Laravel\Containers;


use App\Containers\Php;

class Worker extends Php
{
    protected string $service_name = 'worker';

    public function __construct()
    {
        parent::__construct();
        $this->set_target('worker');
    }


}
