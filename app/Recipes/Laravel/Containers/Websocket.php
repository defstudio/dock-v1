<?php


namespace App\Recipes\Laravel\Containers;


use App\Containers\Php;

class Websocket extends Php
{
    protected string $service_name = 'websocket';

    public function __construct()
    {
        parent::__construct();
        $this->set_target('websocket');
    }


}
