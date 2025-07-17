<?php
/** @noinspection LaravelFunctionsInspection */
/** @noinspection PhpMissingFieldTypeInspection */
/** @noinspection PhpHierarchyChecksInspection */

/** @noinspection DuplicatedCode */


namespace App\Recipes\Laravel\Commands;

use App\Services\DockerService;
use App\Services\TerminalService;
use App\Traits\InteractsWithEnvContent;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;

class Deploy extends Command
{
    use InteractsWithEnvContent;

    protected $signature = 'laravel:deploy
                               {--hot : execute without using maintenance mode}';

    protected $description = 'Updates Laravel codebase from git';

    public function is_production(): bool
    {
        return env('ENV') == 'production';
    }

    /**
     * @param  DockerService  $docker_service
     * @param  TerminalService  $terminal
     *
     * @return int
     */
    public function handle(DockerService $docker_service, TerminalService $terminal)
    {

        $this->title('Starting Laravel update procedure');

        if (!$this->hasOption('hot')) {
            if (!$this->task("Going in Maintenance mode", function () use ($docker_service, $terminal) {
                if (!Storage::disk('src')->exists('storage/framework/down')) {
                    $docker_service->service('php')->execute($terminal, [
                        'php',
                        'artisan',
                        'down',
                        "--retry=60",
                    ], null, false);
                }
            })) {
                return false;
            }
        }

        if (!$this->task("Updating codebase from git", function () use ($docker_service, $terminal) {
            $commands = [
                'cd',
                'src',
                '&&',
                'git reset --hard',
                '&&',
                'git pull',
            ];


            if (!empty($user_id = env('USER_ID'))) {
                $commands[] = '&&';
                $commands[] = "chown -R $user_id *";
            }

            return $terminal->execute_in_shell_command_line($commands);
        })) {
            return false;
        }

        if(env('ENABLE_OPCACHE')){
            if (!$this->task("Resetting OpCache", function () use ($docker_service, $terminal) {

                $commands = [
                    'php',
                    "/usr/bin/cachetool.phar",
                    "opcache:reset",
                ];

                return $docker_service->service('php')->execute($terminal, $commands, with_pseudo_terminal: false);
            })) {
                return false;
            }
        }

        if(env('RUN_NPM_ON_DEPLOY', true)) {
            if (!$this->task("Installing NPM packages", function() use ($docker_service, $terminal) {

                $commands = [
                    "npm",
                    "install",
                ];

                return $docker_service->service('node')->run($terminal, $commands, null, false);
            })) {
                return false;
            }

            if (!$this->task("Compiling Assets", function() use ($docker_service, $terminal) {
                if ($this->is_production()) {
                    $commands = [
                        "npm",
                        "run",
                        "prod",
                    ];
                } else {
                    $commands = [
                        "npm",
                        "run",
                        "dev",
                    ];
                }
                return $docker_service->service('node')->run($terminal, $commands, null, false);
            })) {
                return false;
            }
        }


        if (!$this->task("Installing Composer packages", function () use ($docker_service, $terminal) {
            if ($this->is_production()) {
                $commands = [
                    "composer",
                    "install",
                    "--no-dev",
                    "--optimize-autoloader",
                ];
            } else {
                $commands = [
                    "composer",
                    "install",
                ];
            }
            return $docker_service->service('composer')->run($terminal, $commands, null, false);
        })) {
            return false;
        }

        if (!$this->task("Database maintenance", function () use ($docker_service, $terminal) {
            $docker_service->service('php')->execute($terminal, [
                'php',
                'artisan',
                'migrate',
                "--force",
            ], null, false);

            $docker_service->service('php')->execute($terminal, [
                'php',
                'artisan',
                'db:seed',
                "--force",
            ], null, false);
        })) {
            return false;
        }


        if (!$this->task("Cache setup", function () use ($docker_service, $terminal) {
            $docker_service->service('php')->execute($terminal, [
                "php",
                "artisan",
                "optimize:clear",
            ], null, false);

            if ($this->is_production()) {
                $docker_service->service('php')->execute($terminal, [
                    "php",
                    "artisan",
                    "optimize",
                ], null, false);
            }

            $docker_service->service('php')->execute($terminal, [
                "php",
                "artisan",
                "event:clear",
            ], null, false);

            if ($this->is_production()) {
                $docker_service->service('php')->execute($terminal, [
                    "php",
                    "artisan",
                    "event:cache",
                ], null, false);
            }

            return true;
        })) {
            return false;
        }


        if (!$this->task("Exit from Maintenance mode", function () use ($docker_service, $terminal) {
            if (Storage::disk('src')->exists('storage/framework/down')) {
                $docker_service->service('php')->execute($terminal, [
                    'php',
                    'artisan',
                    'up',
                ], null, false);
            }
        })) {
            return false;
        }


        if (!$this->task("Restarting Queues", function () use ($docker_service, $terminal) {

            $commands = [
                "php",
                "/var/www/artisan",
                "queue:restart",
            ];

            return $docker_service->service('worker')->execute($terminal, $commands);
        })) {
            return false;
        }


        return 0;
    }


}
