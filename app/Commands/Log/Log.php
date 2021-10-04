<?php /** @noinspection DuplicatedCode */

    namespace App\Commands\Log;

    use App\Services\DockerService;
    use App\Services\TerminalService;
    use LaravelZero\Framework\Commands\Command;

    /**
     * Class Log
     * @package App\Commands
     * @method menu(string $string, array $available_services)
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
                $available_services = [];
                foreach($docker_service->get_containers() as $service){
                    $available_services[$service->service_name()] = $service->service_name();
                }

                $service = $this->menu("Select Service to log", $available_services)->open();
            }else{
                $service = $this->argument('service');
            }

            if(empty($service)) return 0;

            return $terminal->execute([
                'docker-compose',
                'logs',
                '-f',
                '-n 20',
                $service,
            ]);

        }
    }
