<?php


namespace App\Containers\Commands;

use App\Traits\ExecutesShellCommands;
use LaravelZero\Framework\Commands\Command;

class Dusk extends Command
{
    use ExecutesShellCommands;

    protected $signature = 'dusk';
    protected $description = 'Executes a dusk command';
}
