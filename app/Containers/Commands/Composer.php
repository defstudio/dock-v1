<?php
/** @noinspection PhpMissingFieldTypeInspection */


namespace App\Containers\Commands;

use App\Traits\ExecutesShellCommands;
use LaravelZero\Framework\Commands\Command;

class Composer extends Command
{
    use ExecutesShellCommands;

    protected $signature = 'composer';
    protected $description = 'Runs a composer command';

    protected string $target_command = 'composer';
}
