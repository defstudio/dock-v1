<?php

    namespace App\Commands\Log;

    use App\Services\DockerService;
    use App\Services\TerminalService;
    use Illuminate\Support\Stringable;
    use LaravelZero\Framework\Commands\Command;

    /**
     * Class Log
     * @package App\Commands
     */
    class LogAll extends Command{
        protected $signature = 'log:all';

        protected $description = 'Log all services';

        /**
         * Execute the console command.
         *
         * @param TerminalService $terminal
         * @param DockerService $docker_service
         * @return mixed
         */
        public function handle(TerminalService $terminal){

            return $terminal->execute([
                ...((new Stringable(env('DOCKER_COMPOSE_COMMAND', 'docker compose')))
                    ->explode(' ')
                    ->toArray()),
                'logs',
                '--follow',
                '--tail=50',
            ]);

        }
    }
