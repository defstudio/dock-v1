<?php

namespace App\Commands;

use App\Services\DockerService;
use App\Services\TerminalService;
use LaravelZero\Framework\Commands\Command;

/**
 * Class Shell
 *
 * @package App\Commands
 */
class Rebuild extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'rebuild
                                {service?} : service to rebuild
                               ';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Rebuild a service';

    /**
     * Execute the console command.
     *
     * @param TerminalService $terminal
     * @param DockerService $docker_service
     *
     * @return mixed
     */
    public function handle(TerminalService $terminal, DockerService $docker_service)
    {

        if ($this->argument('service') == null) {
            $available_services = [];
            foreach ($docker_service->get_containers() as $service) {
                $available_services[$service->service_name()] = $service->service_name();
            }

            $service_name = $this->menu("Select Service", $available_services)->open();
        } else {
            $service_name = $this->argument('service');
        }

        if (empty($service_name)) {
            return 0;
        }

        $this->info("Rebuilding service: $service_name");

        $terminal->execute([
            env('DOCKER_COMPOSE_COMMAND', 'docker compose'),
            'pull',
            $service_name,
        ]);

        $terminal->execute([
            env('DOCKER_COMPOSE_COMMAND', 'docker compose'),
            'up',
            '-d',
            '--no-deps',
            '--build',
            $service_name,
        ], environment_variables: [
            'COMPOSE_DOCKER_CLI_BUILD' => 1,
            'DOCKER_BUILDKIT' => 1,
        ]);

        return 0;
    }
}
