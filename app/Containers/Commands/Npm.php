<?php
/** @noinspection PhpMissingFieldTypeInspection */


namespace App\Containers\Commands;

use App\Traits\ExecutesShellCommands;
use LaravelZero\Framework\Commands\Command;

class Npm extends Command
{
    use ExecutesShellCommands;

    protected $signature = 'npm';
    protected $description = 'Runs an npm command';

    protected string $target_service = 'node';
}
