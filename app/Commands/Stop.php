<?php

    namespace App\Commands;

    use App\Services\TerminalService;
    use Illuminate\Console\Scheduling\Schedule;
    use Illuminate\Support\Stringable;
    use LaravelZero\Framework\Commands\Command;

    class Stop extends Command{
        /**
         * The signature of the command.
         *
         * @var string
         */
        protected $signature = 'stop';

        /**
         * The description of the command.
         *
         * @var string
         */
        protected $description = 'Stop docker containers';

        /**
         * Execute the console command.
         *
         * @param TerminalService $terminal
         * @return mixed
         */
        public function handle(TerminalService $terminal){
            $terminal->init($this->output);

            $this->info('Stopping containers...');

            $exit_code = $terminal->execute([
                ...((new Stringable(env('DOCKER_COMPOSE_COMMAND', 'docker compose')))
                    ->explode(' ')
                    ->toArray()),
                'down'
            ]);

            if($exit_code==0){
                $this->info('Containers stopped');
            }

            return $exit_code;
        }
    }
