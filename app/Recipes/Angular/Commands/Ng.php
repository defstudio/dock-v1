<?php /** @noinspection PhpMissingFieldTypeInspection */


namespace App\Recipes\Angular\Commands;


use App\Traits\ExecutesShellCommands;
use LaravelZero\Framework\Commands\Command;

class Ng extends Command
{
    use ExecutesShellCommands;

    protected $signature = 'ng';
    protected $description = 'Executes an ng command';

    protected string $target_service = 'angular-cli';
}
