<?php /** @noinspection DuplicatedCode */


    namespace App\Recipes\Laravel\Commands;

    use App\Services\DockerService;
    use App\Services\TerminalService;
    use App\Traits\InteractsWithEnvContent;
    use Illuminate\Contracts\Filesystem\FileNotFoundException;
    use Illuminate\Support\Facades\Storage;
    use LaravelZero\Framework\Commands\Command;

    class Update extends Command{
        use InteractsWithEnvContent;

        protected $signature = 'laravel:update
                                {--message= : Message to show on 503 maintenance page}
                               ';

        protected $description = 'Update Laravel codebase from git';

        protected $maintenance_message = "Ongonig maintenance, system will be available soon...";

        public function is_production(): bool{
            return env('ENV') == 'production';
        }

        /**
         * @param DockerService   $docker_service
         * @param TerminalService $terminal
         *
         * @return int
         * @throws FileNotFoundException
         */
        public function handle(DockerService $docker_service, TerminalService $terminal){

            $this->title('Starting Laravel update procedure');

            if($this->hasOption('message')){
                $this->maintenance_message = $this->option('message');
            }

            if(!$this->task("Going in Maintenance mode", function() use ($docker_service, $terminal){
                if(!Storage::disk('src')->exists('storage/framework/down')){
                    $docker_service->service('php')->run($terminal, [
                        'php',
                        'artisan',
                        'down',
                        "--retry=60",
                    ]);
                }
            })) return false;


            if(!$this->task("Updating codebase from git", function() use ($docker_service, $terminal){
                return $terminal->execute_in_shell_command_line([
                    'cd',
                    'src',
                    '&&',
                    'git reset --hard',
                    '&&',
                    'git pull',
                ]);
            })) return false;


            if(!$this->task("Installing Composer packages", function() use ($docker_service, $terminal){
                if($this->is_production()){
                    $commands = [
                        "install",
                        "--no-interaction",
                        "--no-dev",
                        "--optimize-autoloader",
                        "--ignore-platform-reqs",
                    ];
                } else{
                    $commands = [
                        "install",
                        "--no-interaction",
                        "--ignore-platform-reqs",
                    ];
                }
                return $docker_service->service('composer')->run($terminal, $commands);
            })) return false;

            if(!$this->task("Installing NPM packages", function() use ($docker_service, $terminal){

                $commands = [
                    "npm",
                    "install",
                ];

                return $docker_service->service('node')->run($terminal, $commands);
            })) return false;

            if(!$this->task("Compiling Assets", function() use ($docker_service, $terminal){
                if($this->is_production()){
                    $commands = [
                        "npm",
                        "run",
                        "prod",
                    ];
                } else{
                    $commands = [
                        "npm",
                        "run",
                        "dev",
                    ];
                }
                return $docker_service->service('node')->run($terminal, $commands);
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




            if(!$this->task("Cache setup", function() use ($docker_service, $terminal){
                $docker_service->service('php')->execute($terminal, [
                    "php",
                    "artisan",
                    "config:clear",
                ]);

                if($this->is_production()){
                    $docker_service->service('php')->execute($terminal, [
                        "php",
                        "artisan",
                        "config:cache",
                    ]);
                }

                $docker_service->service('php')->execute($terminal, [
                    "php",
                    "artisan",
                    "route:clear",
                ]);

                if($this->is_production()){
                    $docker_service->service('php')->execute($terminal, [
                        "php",
                        "artisan",
                        "route:cache",
                    ]);
                }

                $docker_service->service('php')->execute($terminal, [
                    "php",
                    "artisan",
                    "view:clear",
                ]);

                if($this->is_production()){
                    $docker_service->service('php')->execute($terminal, [
                        "php",
                        "artisan",
                        "view:cache",
                    ]);
                }

                return true;
            })) return false;


            if(!$this->task("Exit from Maintenance mode", function() use ($docker_service, $terminal){
                if(Storage::disk('src')->exists('storage/framework/down')){
                    $docker_service->service('php')->run($terminal, [
                        'php',
                        'artisan',
                        'up',
                    ]);
                }
            })) return false;


            return true;
        }


    }
