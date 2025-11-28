<?php


namespace App\Recipes\Laravel\Containers;


use App\Containers\Php;

class LaravelWebsocket extends Php
{
    protected string $service_name = 'laravel-websocket';

    public function __construct()
    {
        parent::__construct();
        $this->set_target('websockets-laravel');
    }


}
