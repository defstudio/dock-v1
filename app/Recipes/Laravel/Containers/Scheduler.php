<?php


namespace App\Recipes\Laravel\Containers;


use App\Containers\Php;

class Scheduler extends Php
{
    protected string $service_name = 'scheduler';

    public function __construct()
    {
        parent::__construct();
        $this->set_target('scheduler');
    }


}
