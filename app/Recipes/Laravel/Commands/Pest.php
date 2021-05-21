<?php


    namespace App\Recipes\Laravel\Commands;


    use App\Exceptions\DockerServiceNotFoundException;
    use App\Services\DockerService;
    use App\Services\TerminalService;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use LaravelZero\Framework\Commands\Command;

    class Pest extends Command{
        protected string $signature = 'pest';

        protected string $description = 'Starts Laravel tests with Pest';

        public function __construct()
        {
            parent::__construct();
            $this->ignoreValidationErrors();
        }

        /**
         * @param DockerService $docker_service
         * @param TerminalService $terminal
         *
         * @return int
         * @throws DockerServiceNotFoundException
         */
        public function handle(DockerService $docker_service, TerminalService $terminal){

            $terminal->init($this->output);

            $arguments = $this->input->__toString();

            $commands = array_merge([
                'php',
                './vendor/bin/pest',
            ], collect (explode(' ', $arguments))->map(fn($command) => trim($command, "'"))->toArray());

            return $docker_service->service('php')->run($terminal, $commands);
        }
    }
