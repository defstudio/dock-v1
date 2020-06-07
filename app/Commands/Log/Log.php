<?php /** @noinspection DuplicatedCode */

    namespace App\Commands\Log;

    use App\Services\DockerService;
    use App\Services\TerminalService;
    use LaravelZero\Framework\Commands\Command;
    use NunoMaduro\LaravelConsoleMenu\Menu;

    /**
     * Class Log
     * @method Menu menu($name, $options = [])
     * @package App\Commands
     */
    class Log extends Command{
        protected $signature = 'log
                                 {service?} : service name to log
                                ';

        protected $description = 'Log a specific service';

        /**
         * Execute the console command.
         *
         * @param TerminalService $terminal
         * @param DockerService $docker_service
         * @return mixed
         */
        public function handle(TerminalService $terminal, DockerService $docker_service){

            if($this->argument('service')==null){

                //@formatter:off
                $menu = $this->menu('Select Service to Log')
                             ->setForegroundColour(config('styles.menu.colors.foreground'))
                             ->setBackgroundColour(config('styles.menu.colors.background'))
                             ->setWidth(config('styles.menu.width'));
                //@formatter:on

                foreach($docker_service->get_containers() as $service){
                    $menu->addOption($service->service_name(), $service->service_name());
                }

                $service = $menu->open();
            }else{
                $service = $this->argument('service');
            }

            if(empty($service)) return 0;

            return $terminal->execute([
                'docker-compose',
                'logs',
                '-f',
                $service,
            ]);

        }
    }
