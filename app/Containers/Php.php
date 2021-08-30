<?php
/** @noinspection LaravelFunctionsInspection */


namespace App\Containers;

class Php extends Container
{

    protected string $service_name = "php";

    protected array $service_definition = [
        'restart'     => 'unless-stopped',
        'working_dir' => '/var/www',
        'build'       => [
            'context' => 'https://github.com/def-studio/docker-php.git',
            'target'  => 'fpm',
            'args'    => [
                'ENABLE_XDEBUG'             => 0,
                'ENABLE_LIBREOFFICE_WRITER' => 0,
                'ENABLE_BACKUP_TOOLS'       => 0,
            ],
        ],
        'expose'      => [9000],
        'extra_hosts' => ['host.docker.internal:host-gateway'],
    ];

    protected array $volumes = [
        self::HOST_SRC_VOLUME_PATH => '/var/www',
    ];

    public function set_target($target): self
    {
        $this->set_service_definition('build.target', $target);
        return $this;
    }

    public function set_version($version): self
    {
        $this->set_service_definition('build.args.PHP_VERSION', $version);
        return $this;
    }

    public function enable_xdebug(bool $enabled = true): self
    {
        $this->set_service_definition('build.args.ENABLE_XDEBUG', $enabled ? 1 : 0);
        return $this;
    }

    public function enable_libreoffice_writer(bool $enabled = true): self
    {
        $this->set_service_definition('build.args.ENABLE_LIBREOFFICE_WRITER', $enabled ? 1 : 0);
        return $this;
    }

    public function enable_backup_tools(bool $enabled = true): self
    {
        $this->set_service_definition('build.args.ENABLE_BACKUP_TOOLS', $enabled ? 1 : 0);
        return $this;
    }


    public function __construct()
    {
        parent::__construct();

        $this->set_user_uid(env('USER_ID'));

        if (env('ENV', 'local') == 'local') {
            $this->enable_xdebug();
        }

        if (env('ENABLE_LIBREOFFICE_WRITER', '0') == '1') {
            $this->enable_libreoffice_writer();
        }

        if (env('ENABLE_BACKUP_TOOLS', '0') == '1') {
            $this->enable_backup_tools();
        }

        if (!empty(env('PHP_VERSION'))) {
            $this->set_version(env('PHP_VERSION'));
        }
    }

}
