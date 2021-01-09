<?php


namespace App\Containers\Commands;

use App\Traits\ExecutesShellCommands;
use LaravelZero\Framework\Commands\Command;

class Php extends Command
{
    use ExecutesShellCommands;

    protected $signature = 'php';
    protected $description = 'Executes an php command';
}
