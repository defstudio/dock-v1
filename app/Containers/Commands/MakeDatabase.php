<?php /** @noinspection LaravelFunctionsInspection */

/** @noinspection PhpUnhandledExceptionInspection */

namespace App\Containers\Commands;

use App\Services\DockerService;
use App\Services\TerminalService;
use LaravelZero\Framework\Commands\Command;

class MakeDatabase extends Command
{
    protected $signature = 'make:database
                            {database_name?} : database name';
    protected $description = 'Create a new database';

    public function handle(DockerService $docker_service, TerminalService $terminal)
    {
        $this->title('New DB Creation');

        $dbname = $this->argument('database_name') ?? $this->ask('Database Name:');

        throw_if(empty($dbname), 'DB Name is required');

        $dbuser = $this->ask('Database User Name', 'dbuser');
        $dbpassword = $this->ask('Database User Password', 'dbpassword');

        if (!$this->execute_mysql_command($docker_service, $terminal, "create database $dbname")) {
            $this->error('Database creation failed');
            return self::FAILURE;
        }

        if (!$this->execute_mysql_command($docker_service, $terminal, "create user '$dbuser'@'docker' identified by '$dbpassword'")) {
            $this->error('User creation failed');
            return self::FAILURE;
        }

        if (!$this->execute_mysql_command($docker_service, $terminal, "grant all privileges on '$dbname'.* to '$dbuser'@'docker' with grant option")) {
            $this->error('User permission setup failed');
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function execute_mysql_command(DockerService $docker_service, TerminalService $terminal_service, string $command): int
    {
        $password = env('MYSQL_ROOT_PASSWORD');

        $command = [
            "mysql -u root -p$password",
            "-e \"$command\"",
        ];

        return $terminal_service->execute_in_shell_command_line(["docker-compose exec mysql mysql -u root -v"]) === 0;
    }
}
