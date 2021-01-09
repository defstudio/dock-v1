<?php
/** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection PhpMissingFieldTypeInspection */


namespace App\Containers\Commands;

use App\Traits\ExecutesShellCommands;
use LaravelZero\Framework\Commands\Command;

class Node extends Command
{
    use ExecutesShellCommands;

    protected $signature = 'node';
    protected $description = 'Runs a node command';
}
