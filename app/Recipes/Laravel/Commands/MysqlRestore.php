<?php

namespace App\Recipes\Laravel\Commands;

use App\Services\DockerService;
use App\Services\TerminalService;
use App\Traits\ExecutesShellCommands;
use Illuminate\Console\Command;

class MysqlRestore extends Command
{
    protected $signature = 'mysql:restore
                            {file} : sql file to restore (in src/ folder)';

    protected $description = 'Restore a sql backup in application database';

    public function handle(TerminalService $terminal, DockerService $docker_service){
        $terminal->init($this->output);

    }
}
