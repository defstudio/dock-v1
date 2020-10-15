<?php /** @noinspection DuplicatedCode */


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
                    return false;
                }
            }


            $env_content = $src->get('.env');

            if(!$this->task("Updating .env file", function()use($docker_service, $terminal, &$env_content){
                $env_content = $this->compile_env($env_content);
                return true;
            })) return false;


            $src->put('.env', $env_content);


            if(env('ENV') == 'production'){
                if(!$this->init_production($docker_service, $terminal, $env_content)) return false;
            }else{
                if(!$this->init_development($docker_service, $terminal, $env_content)) return false;
            }


            $this->info('Your Laravel system is configured and ready to use');

            return true;
        }

        private function init_development(DockerService $docker_service, TerminalService $terminal, string $env_content): bool{
            if(!$this->task("Installing Composer packages", function()use($docker_service, $terminal){
                return $docker_service->service('composer')->run($terminal, [
                    "install",
                    "--ignore-platform-reqs",
                    "--no-interaction",
                ]);
            })) return false;

            if(!$this->task("Installing NPM packages", function()use($docker_service, $terminal){
                return $docker_service->service('node')->run($terminal, [
                    "npm",
                    "install",
                ]);
            })) return false;

            if(!$this->task("Compiling Assets", function()use($docker_service, $terminal){
                return $docker_service->service('node')->run($terminal, [
                    "npm",
                    "run",
                    "dev",
                ]);
            })) return false;

            if(!$this->task("Database maintenance", function() use ($docker_service, $terminal){
                $docker_service->service('php')->run($terminal, [
                    'php',
                    'artisan',
                    'migrate',
                    "--force",
                ]);

                $docker_service->service('php')->run($terminal, [
                    'php',
                    'artisan',
                    'db:seed',
                    "--force",
                ]);
            })) return false;

            if($this->get_env($env_content, "APP_KEY")==''){
                if(!$this->task("Generating a new app key", function()use($docker_service, $terminal){
                    return $docker_service->service('php')->execute($terminal, [
                        "php",
                        "artisan",
                        "key:generate",
                    ]);
                })) return false;
            }

            if(!$this->task("Cache setup", function()use($docker_service, $terminal){
                $docker_service->service('php')->run($terminal, [
                    "php",
                    "artisan",
                    "config:clear",
                ]);

                $docker_service->service('php')->run($terminal, [
                    "php",
                    "artisan",
                    "route:clear",
                ]);

                $docker_service->service('php')->run($terminal, [
                    "php",
                    "artisan",
                    "view:clear",
                ]);

                return true;
            })) return false;

            return true;
        }

        private function init_production(DockerService $docker_service, TerminalService $terminal, string $env_content): bool{
            if(!$this->task("Installing Composer packages", function()use($docker_service, $terminal){
                return $docker_service->service('composer')->run($terminal, [
                    "install",
                    "--no-interaction",
                    "--optimize-autoloader",
                    "--no-dev",
                    "--ignore-platform-reqs",
                ]);
            })) return false;

            if(!$this->task("Installing NPM packages", function()use($docker_service, $terminal){
                return $docker_service->service('node')->run($terminal, [
                    "npm",
                    "install",
                ]);
            })) return false;

            if(!$this->task("Compiling Assets", function()use($docker_service, $terminal){
                return $docker_service->service('node')->run($terminal, [
                    "npm",
                    "run",
                    "prod",
                ]);
            })) return false;

            if(!$this->task("Database maintenance", function() use ($docker_service, $terminal){
                $docker_service->service('php')->run($terminal, [
                    'php',
                    'artisan',
                    'migrate',
                    "--force",
                ]);

                $docker_service->service('php')->run($terminal, [
                    'php',
                    'artisan',
                    'db:seed',
                    "--force",
                ]);
            })) return false;

            if($this->get_env($env_content, "APP_KEY")==''){
                if(!$this->task("Generating a new app key", function()use($docker_service, $terminal){
                    return $docker_service->service('php')->execute($terminal, [
                        "php",
                        "artisan",
                        "key:generate",
                    ]);
                })) return false;
            }

            if(!$this->task("Cache setup", function()use($docker_service, $terminal){
                $docker_service->service('php')->execute($terminal, [
                    "php",
                    "artisan",
                    "config:clear",
                ]);

                $docker_service->service('php')->execute($terminal, [
                    "php",
                    "artisan",
                    "config:cache",
                ]);

                $docker_service->service('php')->execute($terminal, [
                    "php",
                    "artisan",
                    "route:clear",
                ]);

                $docker_service->service('php')->execute($terminal, [
                    "php",
                    "artisan",
                    "route:cache",
                ]);

                $docker_service->service('php')->execute($terminal, [
                    "php",
                    "artisan",
                    "view:clear",
                ]);

                $docker_service->service('php')->execute($terminal, [
                    "php",
                    "artisan",
                    "view:cache",
                ]);

                return true;
            })) return false;

            return true;
        }


        private function compile_env(string $env_content){
            if(env('ENV')=='production'){
                $this->set_env($env_content, "APP_URL", "https://".env('HOST'));
            }else{
                $this->set_env($env_content, "APP_URL", "http://".env('HOST'));
            }
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
