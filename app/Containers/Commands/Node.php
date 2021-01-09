<?php /** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection PhpMissingFieldTypeInspection */


namespace App\Containers\Commands;

    use App\Services\DockerService;
    use App\Services\TerminalService;
    use LaravelZero\Framework\Commands\Command;

    class Node extends Command{
        protected $signature = 'node';

        protected $description = 'Runs a node command';

        public function __construct()
        {
            parent::__construct();
            $this->ignoreValidationErrors();
        }

        public function handle(DockerService $docker_service, TerminalService $terminal): int
        {
            $terminal->init($this->output);

            $arguments = (string) $this->input;

            if (empty($arguments)) {
                $this->info('Log into Node Shell');

                return $terminal->execute([
                    'docker-compose',
                    'run',
                    '--rm',
                    'node',
                    'bash',
                ]);
            } else {
                $node_commands = explode(' ', $arguments);
                $commands = array_merge(['node'], $node_commands);
                return $docker_service->service('node')->run($terminal, $commands);
            }
        }
    }
