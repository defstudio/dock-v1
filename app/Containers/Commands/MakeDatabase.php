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

        $dbuser = $this->ask('Database User Name', $dbname);
        $dbpassword = $this->ask('Database User Password', 'dbpassword');

        if (!$this->execute_mysql_command($terminal, "create database $dbname")) {
            $this->error('Database creation failed');
            return self::FAILURE;
        }

        if (!$this->execute_mysql_command($terminal, "create user '$dbuser'@'%' identified by '$dbpassword'")) {
            $this->error('User creation failed');
            $this->execute_mysql_command($terminal, "drop database $dbname");
            return self::FAILURE;
        }

        if (!$this->execute_mysql_command($terminal, "GRANT ALL ON $dbname.* TO '$dbuser'@'%'")) {
            $this->execute_mysql_command($terminal, "drop database $dbname");
            $this->execute_mysql_command($terminal, "drop user '$dbuser'@'%'");
            $this->execute_mysql_command($terminal, "flush privileges");

            $this->error('User permission setup failed');
            return self::FAILURE;
        }

        $this->execute_mysql_command($terminal, "flush privileges");

        return self::SUCCESS;
    }

    private function execute_mysql_command(TerminalService $terminal_service, string $command): int
    {
        $password = env('MYSQL_ROOT_PASSWORD');

        $command = [
            'docker exec -t',
            '-i proxy_mysql_1',
            "mysql -u root -p$password",
            "-e \"$command\"",
        ];

        return $terminal_service->execute_in_shell_command_line($command) === 0;
    }
}
