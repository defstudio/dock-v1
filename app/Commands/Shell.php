<?php

    namespace App\Commands;

    use App\Services\DockerService;
    use App\Services\TerminalService;
    use LaravelZero\Framework\Commands\Command;

    /**
     * Class Shell
     * @package App\Commands
     */
    class Shell extends Command{
        /**
         * The signature of the command.
         *
         * @var string
         */
        protected $signature = 'shell
                                {service?} : service to login
                               ';

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

                $available_services = [];
                foreach($docker_service->get_containers() as $service){
                    $available_services[$service->service_name()] = $service->service_name();
                }

                $service_name = $this->menu("Select Service", $available_services)->open();
            }else{
                $service_name = $this->argument('service');
            }

            if(empty($service_name)) return 0;

            $this->info("Starting shell for container: $service_name");

            return $terminal->execute([
                env('DOCKER_COMPOSE_COMMAND', 'docker compose'),
                'exec',
                $service_name,
                '/bin/bash',
            ]);

        }


    }
