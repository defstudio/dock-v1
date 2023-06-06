<?php


namespace App\Containers;


use App\Exceptions\ContainerException;

class Composer extends Php
{
    protected string $service_name = "composer";

    /**
     * Composer constructor.
     *
     * @throws ContainerException
     */
    public function __construct()
    {
        parent::__construct();
        $this->set_target('composer');
        $this->set_volume('./configs/composer/auth.json', '/.composer/auth.json');
    }

    public function commands(): array
    {
        return [
            Commands\Composer::class,
        ];
    }


    public function publish_assets(): void
    {
        if ($this->disk()->exists('composer/auth.json')) {
            dump('auth.json already exists');
            return;
        }


        if ($this->disk()->put('composer/auth.json', '{}')) {
            dump('auth.json created');
        } else {
            dump('auth.json creation error');
        }
    }
}
