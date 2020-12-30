<?php /** @noinspection PhpMissingFieldTypeInspection */


    namespace App\Recipes\Laravel\Commands;


    use App\Exceptions\DockerServiceNotFoundException;
    use App\Services\DockerService;
    use App\Services\TerminalService;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use LaravelZero\Framework\Commands\Command;

    class PhpCs extends Command{
        protected $signature = 'phpcs';

        protected $description = 'Starts Laravel phpcs analisys';

        /**
         * @param DockerService $docker_service
         * @param TerminalService $terminal
         *
         * @return int
         * @throws DockerServiceNotFoundException
         * @throws BindingResolutionException
         */
        public function handle(DockerService $docker_service, TerminalService $terminal): int{

            $terminal->init($this->output);


            $commands = array_merge([
                'php',
                './vendor/bin/phpcs',
                '--standard=./php_cs.xml',
                '--error-severity=1',
                '--warning-severity=8',
            ]);
            return $docker_service->service('php')->run($terminal, $commands);
        }
    }
