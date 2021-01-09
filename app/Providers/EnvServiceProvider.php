<?php

namespace App\Providers;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class EnvServiceProvider extends ServiceProvider
{

    protected $allowed_commands = [
        'stop',
        'init',
        'app:build',
        'app:install',
        'app:rename',
        'app:update',
        'make:command',
        'stub:publish',
        'self-update',
        'lazydocker',
        'test',
    ];

    public function register()
    {

    }


    /**
     * Checks if an .env file is defined or if an init command is given
     */
    public function boot()
    {
        if (!config('app.env')=='testing' ||  !in_array(($_SERVER['argv'][1] ?? ''), $this->allowed_commands)) {

            if (!Storage::disk('cwd')->exists('.env')) {
                Log::warning('No .env file found, please run "init" command first');
                die();
            }
        }
    }
}
