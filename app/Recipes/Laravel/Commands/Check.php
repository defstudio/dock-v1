<?php


    namespace App\Recipes\Laravel\Commands;


    use App\Services\TerminalService;
    use LaravelZero\Framework\Commands\Command;

    class Check extends Command{
        protected string $signature = 'check';

        protected string $description = 'Execute a laravel application complete check using PhpCS, Larastan and Laravel Test Suite';

        public function handle(TerminalService $terminal): int
        {

            $terminal->init($this->output);

            $checks = [
                'larastan' => [
                    'label' => 'Larastan',
                    'arguments' => [],
                ],
                'phpcs' => [
                    'label' => 'PhpCs',
                    'arguments' => [],
                ],
                'artisan' => [
                    'label' => 'Tests',
                    'arguments' => ['test', '--parallel'],
                ],
            ];

            foreach ($checks as $command => $config){
                $label = data_get($config, 'label');
                $arguments = data_get($config, 'arguments');
                if(!$this->task("Running $label", fn()=> $this->runCommand($command, $arguments, $this->output))){
                    $this->error("$label failed!");
                    return -1;
                }
            }

            return 0;
        }
    }
