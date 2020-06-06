<?php

    namespace App\Providers;


   use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\ServiceProvider;

    class EnvServiceProvider extends ServiceProvider{
        public function register(){

        }


        /**
         * Checks if an .env file is defined or if an init command is given
         */
        public function boot(){
            if(($_SERVER['argv'][1]??'') != 'init'){
                if(!Storage::disk('cwd')->exists('.env')){
                    Log::warning('No .env file found, please run "init" command first');
                    die();
                }
            }
        }


    }
