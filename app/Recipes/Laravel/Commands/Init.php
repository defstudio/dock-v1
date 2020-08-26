<?php


	namespace App\Recipes\Laravel\Commands;

    use App\Services\DockerService;
    use App\Services\TerminalService;
    use App\Traits\InteractsWithEnvContent;
    use Illuminate\Contracts\Filesystem\FileNotFoundException;
    use Illuminate\Support\Facades\Storage;
    use LaravelZero\Framework\Commands\Command;

    class Init extends Command{
        use InteractsWithEnvContent;


        protected $signature = 'laravel:init';

        protected $description = 'Initialize an existing laravel source';

        /**
         * @param DockerService $docker_service
         * @param TerminalService $terminal
         * @return int
         * @throws FileNotFoundException
         */
        public function handle(DockerService $docker_service, TerminalService $terminal){

            $src = Storage::disk('src');


            $env_exists = $this->task('Checking laravel .env file existence', function()use($src){
               return $src->exists('.env');
            });

            if(!$env_exists){
                $env_copy_ok = $this->task('Creating a new .env file', function()use($src){
                    return $src->copy('.env.example', '.env');
                });

                if(!$env_copy_ok){
                    $this->error("Can't copy .env.example");
                    return 1;
                }
            }


            $env_content = $src->get('.env');

            if(!$this->task("Updating .env file", function()use($docker_service, $terminal, &$env_content){
                $env_content = $this->compile_env($env_content);
                return true;
            })) return 1;


            $src->put('.env', $env_content);


            if(!$this->task("Installing Composer packages", function()use($docker_service, $terminal){
                return $docker_service->service('composer')->execute($terminal, [
                    "install",
                ]);
            })) return 1;

            if(!$this->task("Installing NPM packages", function()use($docker_service, $terminal){
                return $docker_service->service('node')->execute($terminal, [
                    "npm",
                    "install",
                ]);
            })) return 1;

            if(!$this->task("Compiling Assets", function()use($docker_service, $terminal){
                return $docker_service->service('node')->execute($terminal, [
                    "npm",
                    "run",
                    "dev",
                ]);
            })) return 1;

            if($this->get_env($env_content, "APP_KEY")==''){
                if(!$this->task("Generating a new app key", function()use($docker_service, $terminal){
                    return $docker_service->service('php')->execute($terminal, [
                        "php",
                        "artisan",
                        "key:generate",
                    ]);
                })) return 1;
            }

            if(!$this->task("Clearing configuration cache", function()use($docker_service, $terminal){
                return $docker_service->service('php')->execute($terminal, [
                    "php",
                    "artisan",
                    "config:clear",
                ]);
            })) return 1;


            $this->info('Your Laravel system is configured and ready to use');



            return 0;
        }

        private function compile_env(string $env_content){
            $this->set_env($env_content, "APP_URL", "http://".env('HOST'));
            $this->set_env($env_content, "APP_ENV", env('ENV'));
            $this->set_env($env_content, "DB_HOST", 'mysql');
            $this->set_env($env_content, "DB_DATABASE", env('MYSQL_DATABASE', 'laravel'));
            $this->set_env($env_content, "DB_USERNAME", env('MYSQL_USER', 'dbuser'));
            $this->set_env($env_content, "DB_PASSWORD", env('MYSQL_PASSWORD', 'dbpassword'));
            $this->set_env($env_content, "REDIS_HOST", 'redis');
            $this->set_env($env_content, "SESSION_DOMAIN", env('HOST'));

            if(env('ENV', 'local')){
                if(!empty(env("MAILHOG_PORT")) || !empty(env("MAILHOG_SUBDOMAIN"))){
                    $this->set_env($env_content, "MAIL_HOST", 'mailhog');
                    $this->set_env($env_content, "MAIL_PORT", '1025');
                }
            }

            return $env_content;
        }



    }
