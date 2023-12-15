<?php /** @noinspection LaravelFunctionsInspection */


namespace App\Recipes\ReverseProxy;


use App\Containers\CertbotCloudflare;
use App\Containers\Container;
use App\Containers\MySql;
use App\Containers\Nginx;
use App\Contracts\SSLService;
use App\Exceptions\ContainerException;
use App\Exceptions\DuplicateNetworkException;
use App\Exceptions\DuplicateServiceException;
use App\Recipes\DockerComposeRecipe;
use App\Recipes\ReverseProxy\Commands\ProxyAdd;
use App\Recipes\ReverseProxy\Commands\ProxyDisable;
use App\Recipes\ReverseProxy\Commands\ProxyEnable;
use App\Recipes\ReverseProxy\Commands\ProxyReload;
use App\Recipes\ReverseProxy\Exceptions\ProxyTargetInvalidException;
use App\Recipes\ReverseProxy\Services\TargetsService;
use App\Services\SSL\Certbot;
use App\Services\SSL\SelfSigned;
use App\Traits\InteractsWithEnvContent;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class ReverseProxyRecipe extends DockerComposeRecipe
{

    const LABEL = 'ReverseProxy';

    const PROXY_NETWORK = 'reverse_proxy_network';
    const DB_NETWORK = 'shared_db_network';


    protected function customize_init(Command $parent_command, string $env_content): string
    {
        /** @var TargetsService $targets */
        $targets = app()->make(TargetsService::class);
        $targets->init_targets_file();

        if ($parent_command->confirm('Would you like to a shared DB service?')) {
            $this->set_env($env_content, 'ENABLE_MYSQL', 1);

            $parent_command->question("MySql configuration");

            $mysql_port = $parent_command->ask("Enter MySQL exposed port", 3306);
            if ($mysql_port == 'x') {
                $this->comment_env($env_content, 'MYSQL_PORT');
            } else {
                $this->set_env($env_content, "MYSQL_PORT", $mysql_port);
            }
            $this->set_env($env_content, 'MYSQL_DATABASE', $parent_command->ask("Database Name", "database"));
            $this->set_env($env_content, 'MYSQL_USER', $parent_command->ask("Database User", "dbuser"));
            $this->set_env($env_content, 'MYSQL_PASSWORD', $parent_command->ask("Database Password", "dbpassword"));
            $this->set_env($env_content, 'MYSQL_ROOT_PASSWORD', $parent_command->ask("Database Root Password", "root"));
        }

        return $this->init_ssl_configuration($parent_command, $env_content);
    }

    private function ssl_provider(): SSLService
    {
        return app()->make(env('SSL_PROVIDER'));
    }

    private function init_ssl_configuration(Command $parent_command, string $env_content): string
    {
        $parent_command->question('SSL Configuration');

        $ssl_provider_class = $parent_command->choice('Select SSL Provider', [
            SelfSigned::class => 'self-signed',
            Certbot::class => 'certbot',
        ], Certbot::class);

        $this->set_env($env_content, 'SSL_PROVIDER', $ssl_provider_class);

        /** @var SSLService $ssl_service */
        $ssl_service = app()->make($ssl_provider_class);
        return $ssl_service->init_recipe($parent_command, $env_content);
    }


    /**
     * @inheritDoc
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     * @throws ProxyTargetInvalidException
     */
    public function build()
    {

        $nginx = $this->build_nginx($this->ssl_provider()->reserved_ports());

        $this->build_targets($nginx);

        $this->build_ssl_providers();

        $this->build_mysql();
    }

    private function build_nginx(array $reserved_ports): Nginx
    {
        /** @var Nginx $nginx */
        $nginx = $this->add_container(Nginx::class);

        foreach ([80, 443, 6001] as $port) {
            if (!in_array($port, $reserved_ports)) {
                $nginx->map_port($port);
            }
        }

        $nginx->unset_service_definition('working_dir');
        $nginx->unset_php_service();

        $nginx->enable_backend_not_found_page();

        $nginx->set_volume(Container::HOST_CONFIG_VOLUME_PATH.'certbot/letsencrypt', '/etc/letsencrypt');

        $nginx->add_network(self::PROXY_NETWORK);

        return $nginx;
    }

    /**
     * @param Nginx $nginx
     *
     * @throws FileNotFoundException|ProxyTargetInvalidException
     */
    private function build_targets(Nginx $nginx): void
    {

        /** @var TargetsService $targets */
        $targets = app()->make(TargetsService::class);

        collect($targets->get_targets())
            ->each(function ($target) {
                if (in_array($target->port, $this->ssl_provider()->reserved_ports())) {
                    throw new ProxyTargetInvalidException("Target in target.json can't use port [$target->port]: it is reserved from ssl provider");
                }
            });

        $targets->make_proxies($nginx);
    }

    /**
     * @throws BindingResolutionException
     */
    private function build_ssl_providers(): void
    {

        /** @var SSLService $ssl_provider */
        $ssl_provider = app()->make(SSLService::class);
        $ssl_provider->build_ssl_provider($this);

    }


    /**
     * @throws ContainerException
     * @throws DuplicateNetworkException
     * @throws DuplicateServiceException
     */
    public function setup(): void
    {
        parent::setup();
        $this->docker_service->add_network(self::PROXY_NETWORK, self::PROXY_NETWORK, 'bridge');
    }

    /**
     * @inheritDoc
     */
    protected function recipe_commands(): array
    {
        return [
            ProxyEnable::class,
            ProxyDisable::class,
            ProxyAdd::class,
            ProxyReload::class,
        ];
    }

    private function build_mysql(): void
    {
        if (!env('ENABLE_MYSQL')){
            return;
        }

        $mysql = $this->add_container(MySql::class)->add_network(self::DB_NETWORK);
        $mysql->set_database(env('MYSQL_DATABASE', 'database'));
        $mysql->set_user(env('MYSQL_USER', 'dbuser'));
        $mysql->set_password(env('MYSQL_PASSWORD', 'dbpassword'));
        $mysql->set_root_password(env('MYSQL_ROOT_PASSWORD', 'root'));

        if (!empty(env('MYSQL_PORT'))) {
            $mysql->map_port(env('MYSQL_PORT'), 3306);
        }
    }
}


