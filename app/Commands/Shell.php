<?php

    namespace App\Commands;

    use App\Services\DockerService;
    use App\Services\TerminalService;
    use LaravelZero\Framework\Commands\Command;
    use NunoMaduro\LaravelConsoleMenu\Menu;

    /**
     * Class Shell
     * @method Menu menu($name, $options = [])
     * @package App\Commands
     */
    class Shell extends Command{
        /**
         * The signature of the command.
         *
         * @var string
         */
        protected $signature = 'shell
                                {service?} : service to login';

        /**
         * The description of the command.
         *
         * @var string
         */
        protected $description = 'Log into a service shell';

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
                $menu = $this->menu('Select Service')
                                    ->setForegroundColour(config('styles.menu.colors.foreground'))
                                    ->setBackgroundColour(config('styles.menu.colors.background'))
                                    ->setWidth(config('styles.menu.width'));
                //@formatter:on

                foreach($docker_service->get_services() as $service){
                    $menu->addOption($service->service_name(), $service->service_name());
                }

                $container_name = $menu->open();
            }else{
                $container_name = $this->option('service');
            }

            if(empty($container_name)) return 0;

            $this->info("Starting shell for container: $container_name");

            return $terminal->execute([
                'docker-compose',
                'exec',
                $container_name,
                'bash',
            ]);

        }


    }
