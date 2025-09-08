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

        $this->replicas(env('WORKERS_COUNT', 1));
    }

    public function replicas(int $replicas): self
    {
        $this->set_service_definition('deploy.replicas', $replicas);

        return $this;
    }


}
