<?php /** @noinspection PhpUnhandledExceptionInspection */


namespace App\Recipes\Laravel\Commands;


    use App\Services\DockerService;
    use App\Services\TerminalService;
    use LaravelZero\Framework\Commands\Command;

    class PestCoverage extends Command{
        protected $signature = 'pest:coverage';

        protected $description = 'Starts Laravel tests with Pest and records coverage';

        public function handle(DockerService $docker_service, TerminalService $terminal): int
        {

            $terminal->init($this->output);


            $commands = array_merge([
                'php',
                './vendor/bin/pest',
                '--coverage',
                '--coverage-html coverage'
            ]);

            return $docker_service->service('php')->run($terminal, $commands);
        }
    }
