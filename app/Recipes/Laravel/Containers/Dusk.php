<?php


namespace App\Recipes\Laravel\Containers;


use App\Containers\Container;
use App\Containers\Php;

class Dusk extends Container
{
    public const BROWSER_CHROME = 'chrome';
    public const BROWSER_EDGE = 'edge';
    public const BROWSER_FIREFOX = 'firefox';

    protected string $service_name = 'dusk';

    protected array $service_definition = [
        'working_dir' => '/var/www',
        'image' => 'selenium/standalone-' . self::BROWSER_CHROME,
    ];

    protected array $volumes = [
        self::HOST_SRC_VOLUME_PATH => '/var/www',
    ];

    public function commands(): array
    {
        return [
            \App\Containers\Commands\Dusk::class,
        ];
    }
}