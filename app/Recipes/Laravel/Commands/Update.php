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

        /**
         * @param DockerService $docker_service
         * @param TerminalService $terminal
         * @return int
         * @throws FileNotFoundException
         */
        public function handle(DockerService $docker_service, TerminalService $terminal){

            $this->title('Starting Laravel update procedure');

            if($this->hasOption('message')){
                $this->maintenance_message = $this->option('message');
            }

            $this->task("Going in Maintenance mode", function() use($docker_service, $terminal){
                if(!Storage::disk('src')->exists('storage/framework/down')){
                    $docker_service->service('php')->run($terminal, [
                        'php',
                        'artisan',
                        'down',
                        "--message={$this->maintenance_message}",
                        "--retry=60",
                    ]);
                }
            });





            $this->task("Exit from Maintenance mode", function() use($docker_service, $terminal){
                if(Storage::disk('src')->exists('storage/framework/down')){
                    $docker_service->service('php')->run($terminal, [
                        'php',
                        'artisan',
                        'up',
                    ]);
                }
            });



            return true;
        }




    }
