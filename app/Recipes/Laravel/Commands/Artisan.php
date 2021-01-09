<?php
/** @noinspection PhpMissingFieldTypeInspection */

namespace App\Recipes\Laravel\Commands;

use App\Traits\ExecutesShellCommands;
use LaravelZero\Framework\Commands\Command;

class Artisan extends Command
{
    use ExecutesShellCommands;

    protected $signature = 'artisan';
    protected $description = 'Executes an Artisan command';

    protected string $target_service = 'php';
    protected string $target_command = 'php';
}
