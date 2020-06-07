<?php

    namespace App\Commands;

     use App\Recipes\DockerComposeRecipe;
    use App\Services\DockerService;
    use App\Services\TerminalService;
     use Carbon\Carbon;
     use Illuminate\Support\Facades\Storage;
    use LaravelZero\Framework\Commands\Command;
    use NunoMaduro\LaravelConsoleMenu\Menu;

    /**
     * Class Init
     * @method Menu menu($name, $options = [])
     * @package App\Commands
     */
    class Backup extends Command{

        protected $signature = 'backup';
        protected $description = 'Start docker containers backup';


        /**
         * Execute the console command.
         *
         * @param DockerService $docker_service
         * @param TerminalService $terminal
         * @return mixed
         */
        public function handle(DockerService $docker_service, TerminalService $terminal){
            $terminal->init($this->output);

            $now = Carbon::now();
            $backup_folder = $now->format('Y-d-m_h-i-s_').$now->timestamp;

            Storage::disk('backup')->makeDirectory($backup_folder);

            foreach($docker_service->get_containers() as $container){
                $this->task("Backup {$container->service_name()} service", function()use($container, $backup_folder){
                   return $container->backup($backup_folder);
                });
            }

            return 0;
        }

    }
