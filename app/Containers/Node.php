<?php
/** @noinspection LaravelFunctionsInspection */


namespace App\Containers;

class Node extends Container
{
    protected string $service_name = "node";

    protected array $service_definition = [
        'working_dir' => '/var/www',
        'image'       => 'defstudio/node:alpine-lts',
    ];

    protected array $volumes = [
        self::HOST_SRC_VOLUME_PATH => '/var/www',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->set_user_uid(env('USER_ID'));
    }

    public function commands(): array
    {
        return [
            Commands\Node::class,
            Commands\Npm::class,
        ];
    }
}
