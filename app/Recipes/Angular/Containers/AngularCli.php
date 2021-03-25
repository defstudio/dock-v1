<?php /** @noinspection LaravelFunctionsInspection */


namespace App\Recipes\Angular\Containers;


use App\Containers\Container;

class AngularCli extends Container
{
    protected string $service_name = 'angular-cli';

    protected array $service_definition = [
        'restart' => 'unless-stopped',
        'image' => 'defstudio/angular-cli:latest'
    ];

    protected array $volumes = [
        self::HOST_SRC_VOLUME_PATH => '/app',
    ];

    public function __construct(){
        parent::__construct();
        $this->set_user_uid(env('USER_ID'));
    }
}
