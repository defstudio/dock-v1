<?php /** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection PhpMissingFieldTypeInspection */


namespace App\Containers\Commands;

use App\Services\DockerService;
use App\Services\TerminalService;
use LaravelZero\Framework\Commands\Command;

class Composer extends Command
{
    protected $signature = 'composer';

    protected $description = 'Runs a composer command';

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
            $this->info('Log into Composer Shell');

            return $terminal->execute([
                'docker-compose',
                'run',
                '--rm',
                'composer',
                'bash',
            ]);
        } else {
            $composer_commands = explode(' ', $arguments);
            $commands = array_merge(['composer'], $composer_commands);
            return $docker_service->service('composer')->run($terminal, $commands);
        }
    }
}
