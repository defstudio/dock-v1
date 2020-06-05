<?php

    namespace App\Commands;

    use LaravelZero\Framework\Commands\Command;
    use Symfony\Component\Process\Process;

    class ListAllContainers extends Command{
        protected $signature = 'list:all';

        protected $description = 'List all active docker containers';

        /**
         * Execute the console command.
         *
         * @return mixed
         */
        public function handle(){
            /** @var Process $process */
            $process = app('App\Process', [
                'docker',
                'ps',
            ]);

            $exitCode = $process->run(function($type, $buffer){
                $this->output->write($buffer);
            });

            return $exitCode;
        }
    }
