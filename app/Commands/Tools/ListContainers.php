<?php

    namespace App\Commands\Tools;

    use App\Services\TerminalService;
    use LaravelZero\Framework\Commands\Command;

    class ListContainers extends Command{
        protected $signature = 'list:containers
                                {--all}';

        protected $description = 'List all active docker containers';

        /**
         * Execute the console command.
         *
         * @param TerminalService $terminal
         * @return mixed
         */
        public function handle(TerminalService $terminal){

            $commands = [
                'docker',
                'ps',
            ];

            if($this->option('all')){
                $commands[] = '--all';
            }


            return $terminal->execute($commands);
        }
    }
