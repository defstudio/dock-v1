<?php

namespace App\Recipes\Laravel\Containers;

use App\Containers\Container;

class MeiliSearch extends Container
{
    const HOST_MEILISEARCH_VOLUME_PATH = './volumes/meilisearch/db/';

    protected string $service_name = 'meilisearch';

    protected array $service_definition = [
        'restart' => 'unless-stopped',
        'image' => 'getmeili/meilisearch:latest',
        'expose' => [7700],
        'environment' => [
            'MEILI_NO_ANALYTICS' => false
        ],
        'healthcheck' => [
            'test' => ['CMD', 'wget', '--no-verbose', '--spider', 'http://127.0.0.1:7700/health'],
            'retries' => 3,
            'timeout' => '5s',
        ]
    ];


    protected array $volumes = [
        self::HOST_MEILISEARCH_VOLUME_PATH => '/meili_data',
    ];
}
