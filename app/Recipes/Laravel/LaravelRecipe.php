<?php /** @noinspection HttpUrlsUsage */
/** @noinspection PhpUnnecessaryLocalVariableInspection */
/** @noinspection PhpReturnValueOfMethodIsNeverUsedInspection */
/** @noinspection LaravelFunctionsInspection */
/** @noinspection DuplicatedCode */

/** @noinspection PhpUnhandledExceptionInspection */


namespace App\Recipes\Laravel;

use App\Containers\MailHog;
use App\Containers\Memcached;
use App\Containers\Nginx;
use App\Containers\Node;
use App\Containers\PhpMyAdmin;
use App\Containers\Redis;
use App\Containers\Composer;
use App\Containers\MySql;
use App\Recipes\DockerComposeRecipe;
use App\Recipes\Laravel\Commands\Artisan;
use App\Recipes\Laravel\Commands\Check;
use App\Recipes\Laravel\Commands\Init;
use App\Recipes\Laravel\Commands\Install;
use App\Recipes\Laravel\Commands\Larastan;
use App\Recipes\Laravel\Commands\Migrate;
use App\Recipes\Laravel\Commands\Deploy;
use App\Recipes\Laravel\Commands\OpcacheReset;
use App\Recipes\Laravel\Commands\OpcacheStatus;
use App\Recipes\Laravel\Commands\Pest;
use App\Recipes\Laravel\Commands\PestCoverage;
use App\Recipes\Laravel\Commands\PhpCs;
use App\Recipes\Laravel\Commands\RestartQueue;
use App\Recipes\Laravel\Commands\Vite;
use App\Recipes\Laravel\Commands\Watch;
use App\Containers\Php;
use App\Recipes\Laravel\Containers\Dusk;
use App\Recipes\Laravel\Containers\MeiliSearch;
use App\Recipes\Laravel\Containers\Nightwatch;
use App\Recipes\Laravel\Containers\Pulse;
use App\Recipes\Laravel\Containers\Scheduler;
use App\Recipes\Laravel\Containers\Websocket;
use App\Recipes\Laravel\Containers\Worker;
use App\Recipes\ReverseProxy\ReverseProxyRecipe;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class LaravelRecipe extends DockerComposeRecipe
{

    const LABEL = 'Laravel';

    const DEFAULT_HOST = 'laravel.ktm';

    public function customize_init(Command $parent_command, string $env_content): string
    {

        if ($parent_command->confirm('Would you like to customize your recipe?')) {

            //<editor-fold desc="General Configuration">
            $parent_command->question("General configuration");

            $application_host = $parent_command->ask("Enter application hostname", self::DEFAULT_HOST);
            $this->set_env($env_content, "HOST", $application_host);

            $application_env = $parent_command->choice("Enter application environment", [
                'local',
                'production',
            ], 0);
            $this->set_env($env_content, "ENV", $application_env);
            //</editor-fold>


            $php_version = $parent_command->ask("Enter PHP version", 'latest');
            $this->set_env($env_content, "PHP_VERSION", $php_version);

            if ($application_env === 'production') {
                $enable_opcache = $parent_command->confirm("Should OpCache be enabled?");
                $this->set_env($env_content, 'OPCACHE_ENABLED', $enable_opcache ? 1 : 0);
            } else {
                $this->set_env($env_content, 'OPCACHE_ENABLED', 0);
            }

            //<editor-fold desc="Network Configuration">
            $parent_command->question("Network configuration");
            if ($parent_command->confirm("Is the application behind a proxy?")) {
                $this->comment_env($env_content, 'NGINX_PORT');
                $this->comment_env($env_content, 'NGINX_PORT_SSL');
                $this->comment_env($env_content, 'MYSQL_PORT');
                $this->comment_env($env_content, 'PHPMYADMIN_PORT');
                $this->comment_env($env_content, 'MAILHOG_PORT');

                $this->set_env($env_content, 'REVERSE_PROXY_NETWORK', ReverseProxyRecipe::PROXY_NETWORK);

                if ($shared_db = $parent_command->confirm('Should Application use a shared database')) {
                    $this->set_env($env_content, 'MYSQL_SHARED_DB_NETWORK', ReverseProxyRecipe::DB_NETWORK);
                }

            } else {

                $parent_command->info("Exposed services selection (type x to skip)");

                $nginx_port = $parent_command->ask("Enter Nginx exposed port", 80);
                if ($nginx_port == 'x') {
                    $this->comment_env($env_content, 'NGINX_PORT');
                } else {
                    if ($nginx_port == '443') {
                        $this->comment_env($env_content, 'NGINX_PORT');
                        $this->set_env($env_content, "NGINX_PORT_SSL", $nginx_port);
                    } else {
                        $this->set_env($env_content, "NGINX_PORT", $nginx_port);

                        $nginx_ssl_port = $parent_command->ask("Enter Nginx SSL exposed port", 443);
                        if ($nginx_ssl_port == 'x') {
                            $this->comment_env($env_content, 'NGINX_PORT_SSL');
                        } else {
                            $this->set_env($env_content, "NGINX_PORT_SSL", $nginx_ssl_port);
                        }
                    }
                }

                if (!empty($shared_db)) {
                    $mysql_port = $parent_command->ask("Enter MySQL exposed port", 3306);
                    if ($mysql_port == 'x') {
                        $this->comment_env($env_content, 'MYSQL_PORT');
                    } else {
                        $this->set_env($env_content, "MYSQL_PORT", $mysql_port);
                    }

                    $phpmyadmin_port = $parent_command->ask("Enter PhpMyAdmin exposed port", 8081);
                    if ($phpmyadmin_port == 'x') {
                        $this->comment_env($env_content, 'PHPMYADMIN_PORT');
                    } else {
                        $this->set_env($env_content, "PHPMYADMIN_PORT", $phpmyadmin_port);
                    }
                }

                $mailhog_port = $parent_command->ask("Enter MailHog exposed port", 8025);
                if ($mailhog_port == 'x') {
                    $this->comment_env($env_content, 'MAILHOG_PORT');
                } else {
                    $this->set_env($env_content, "MAILHOG_PORT", $mailhog_port);
                }
            }

            if ($parent_command->confirm("Do you want to setup a custom ssl certificate?")) {
                $parent_command->info("This setup will allow you to define an external folder to load ssl certificates into nginx setup");
                $parent_command->info("Note: the folder must contain at least the following files:");
                $parent_command->info(" - live/[hostname]/fullchain.pem");
                $parent_command->info(" - live/[hostname]/privkey.pem");

                $ssl_certificates_folder = $parent_command->ask("Enter the path to the ssl certificates folder (absolute or relative to dock folder)");
                $this->set_env($env_content, 'NGINX_CUSTOM_CERTIFICATES_FOLDER', $ssl_certificates_folder);

                $ssl_certificate_hostname = $parent_command->ask("Enter the hostname contained in the certificate", $application_host);
                $this->set_env($env_content, 'NGINX_CUSTOM_CERTIFICATES_HOSTNAME', $ssl_certificate_hostname);

            }
            //</editor-fold>


            $websocket_port = $parent_command->ask("Enter Websocket exposed port (x to disable)", 6001);
            if ($websocket_port == 'x') {
                $this->comment_env($env_content, 'WEBSOCKET_PORT');
            } else {
                $this->set_env($env_content, "WEBSOCKET_PORT", $websocket_port);
            }

            $phpmyadmin_subdomain = $parent_command->ask("Enter PhpMyAdmin exposed subdomain", "mysql");
            if ($phpmyadmin_subdomain == 'x') {
                $this->comment_env($env_content, 'PHPMYADMIN_SUBDOMAIN');
            } else {
                $this->set_env($env_content, "PHPMYADMIN_SUBDOMAIN", $phpmyadmin_subdomain);
            }


            $mailhog_subdomain = $parent_command->ask("Enter MailHog exposed subdomain", "mail");
            if ($mailhog_subdomain == 'x') {
                $this->comment_env($env_content, 'MAILHOG_SUBDOMAIN');
            } else {
                $this->set_env($env_content, "MAILHOG_SUBDOMAIN", $mailhog_subdomain);
            }


            //<editor-fold desc="MySql Configuration">
            $parent_command->question("MySql configuration");
            $this->set_env($env_content, 'MYSQL_DATABASE', $parent_command->ask("Database Name", "database"));
            $this->set_env($env_content, 'MYSQL_USER', $parent_command->ask("Database User", "dbuser"));
            $this->set_env($env_content, 'MYSQL_PASSWORD', $parent_command->ask("Database Password", "dbpassword"));

            if (!empty($shared_db)) {
                $this->set_env($env_content, 'MYSQL_ROOT_PASSWORD', $parent_command->ask("Database Root Password", "root"));
            }
            //</editor-fold>

        }

        return $env_content;
    }

    public function build_scheduler(): void
    {
        $scheduler = $this->add_container(Scheduler::class)
            ->add_network($this->internal_network())
            ->depends_on('redis');

        if ($shared_db_network = env('MYSQL_SHARED_DB_NETWORK')) {
            $scheduler->add_network($shared_db_network);
        } else {
            $scheduler->depends_on('mysql');
        }
    }

    public function build_worker(): void
    {
        $worker = $this->add_container(Worker::class)
            ->add_network($this->internal_network())
            ->depends_on('redis');


        if ($shared_db_network = env('MYSQL_SHARED_DB_NETWORK')) {
            $worker->add_network($shared_db_network);
        } else {
            $worker->depends_on('mysql');
        }
    }

    public function build_pulse(): void
    {
        if (!empty(env('ENABLE_PULSE'))) {
            $pulse = $this->add_container(Pulse::class)
                ->add_network($this->internal_network())
                ->depends_on('redis');

            if ($shared_db_network = env('MYSQL_SHARED_DB_NETWORK')) {
                $pulse->add_network($shared_db_network);
            } else {
                $pulse->depends_on('mysql');
            }
        }
    }


    public function build_nightwatch(): void
    {
        if (!empty(env('ENABLE_NIGHTWATCH'))) {
            $this->add_container(Nightwatch::class)
                ->add_network($this->internal_network());
        }
    }



    public function build_composer(): void
    {
        $composer = $this->add_container(Composer::class)
            ->add_network($this->internal_network());

        if ($shared_db_network = env('MYSQL_SHARED_DB_NETWORK')) {
            $composer->add_network($shared_db_network);
        } else {
            $composer->depends_on('mysql');
        }
    }

    protected function recipe_commands(): array
    {
        $commands = [
            Install::class,
            Init::class,
            Artisan::class,
            Migrate::class,
            Watch::class,
            Deploy::class,
            RestartQueue::class,
            Larastan::class,
            PhpCs::class,
            Check::class,
            Pest::class,
            PestCoverage::class,
        ];

        if (env('ENV') != 'production') {
            $commands[] = Vite::class;
        }

        if (env('ENABLE_OPCACHE')) {
            $commands[] = OpcacheReset::class;
            $commands[] = OpcacheStatus::class;
        }

        return $commands;
    }

    protected function host(): string
    {
        return env('HOST', self::DEFAULT_HOST);
    }

    protected function internal_network(): string
    {
        return Str::of("{$this->host()} internal network")->slug('_');
    }

    public function build(): void
    {
        $php = $this->build_php();

        $nginx = $this->build_nginx($php);

        if (empty(env('MYSQL_SHARED_DB_NETWORK'))) {
            $mysql = $this->build_mysql();
            $this->build_phpmyadmin($mysql, $nginx);
        }

        $this->build_mailhog($nginx);

        $this->build_scheduler();

        $this->build_worker();

        $this->build_composer();

        $this->add_container(Node::class)
            ->add_network($this->internal_network())
            ->map_port(3000);

        $this->add_container(Redis::class)
            ->add_network($this->internal_network());

        if (!empty(env('MEMCACHED_ENABLED'))) {
            $this->add_container(Memcached::class)
                ->add_network($this->internal_network());
        }

        if (!empty(env('MEILISEARCH_ENABLED'))) {
            $this->add_container(MeiliSearch::class)
                ->add_network($this->internal_network());
        }

        if (!empty(env('WEBSOCKET_PORT'))) {
            $this->build_websocket();
        }

        if (!empty(env('ENABLE_BROWSER_TESTS'))) {
            $dusk = $this->build_dusk();
            $dusk->set_link($nginx->service_name(), env('HOST'));
        }

        $this->build_pulse();

        $this->build_nightwatch();
    }

    private function build_php(): Php
    {
        $php = app()->make(Php::class)
            ->set_environment('DOCK', 1)
            ->add_network($this->internal_network())
            ->depends_on('redis');

        if ($shared_db_network = env('MYSQL_SHARED_DB_NETWORK')) {
            $php->add_network($shared_db_network);
        } else {
            $php->depends_on('mysql');
        }

        return $php;
    }

    private function build_nginx(Php $php): Nginx
    {
        /** @var Nginx $nginx */
        $nginx = $this->add_container(Nginx::class)->add_network($this->internal_network());
        $nginx->set_php_service($php);

        if ($custom_certificates_folder = env('NGINX_CUSTOM_CERTIFICATES_FOLDER')) {
            $nginx->set_volume($custom_certificates_folder, '/etc/letsencrypt');
        }

        $extra_site_config = "
                location /socket.io {
                    proxy_pass http://localhost:6001;
                    proxy_http_version 1.1;
                    proxy_set_header Upgrade \$http_upgrade;
                    proxy_set_header Connection \"Upgrade\";
                }

                 location ^~ /livewire {
                    try_files \$uri \$uri/ /index.php?\$query_string;
                }

        ";

        if (($cache_age = env('NGINX_CACHE_AGE')) && ($cache_files = env('NGINX_CACHE_FILES'))) {


            $extra_site_config .= "
                location ~* \.($cache_files)$ {
                    expires $cache_age;
                    etag off;
                    if_modified_since off;
                    add_header Cache-Control \"public, no-transform\";
                }
            ";
        }

        $nginx->add_site($this->host(), 80, '/var/www/public', null, null, $extra_site_config);

        if (env('NGINX_CUSTOM_CERTIFICATES_HOSTNAME')) {
            $certificate_hostname = env('NGINX_CUSTOM_CERTIFICATES_HOSTNAME', $this->host());
            $ssl_certificate = "/etc/letsencrypt/live/$certificate_hostname/fullchain.pem";
            $ssl_certificate_key = "/etc/letsencrypt/live/$certificate_hostname/privkey.pem";
        }

        $nginx->add_site($this->host(), 443, '/var/www/public', $ssl_certificate ?? null, $ssl_certificate_key ?? null, $extra_site_config);


        if (!empty(env('NGINX_PORT'))) {
            $nginx->map_port(env('NGINX_PORT'), 80);
            $this->add_exposed_host(env('HOST', self::DEFAULT_HOST));
            $this->add_exposed_address(self::LABEL, "http", env('HOST', self::DEFAULT_HOST), env('NGINX_PORT'));
        }
        if (!empty(env('NGINX_PORT_SSL'))) {
            $nginx->map_port(env('NGINX_PORT_SSL'), 443);
            $this->add_exposed_host(env('HOST', self::DEFAULT_HOST));
            $this->add_exposed_address(self::LABEL." SSL", "https", env('HOST', self::DEFAULT_HOST), env('NGINX_PORT_SSL'));
        }

        $proxy_network = env('REVERSE_PROXY_NETWORK');
        if (!empty($proxy_network)) {
            $nginx->add_network($proxy_network);
        }

        return $nginx;
    }

    private function build_mysql(): MySql
    {
        /** @var MySql $mysql */
        $mysql = $this->add_container(MySql::class)->add_network($this->internal_network());

        $mysql->set_database(env('MYSQL_DATABASE', 'database'));
        $mysql->set_user(env('MYSQL_USER', 'dbuser'));
        $mysql->set_password(env('MYSQL_PASSWORD', 'dbpassword'));
        $mysql->set_root_password(env('MYSQL_ROOT_PASSWORD', 'root'));

        if (!empty(env('MYSQL_PORT'))) {
            $this->add_exposed_host(env('HOST', self::DEFAULT_HOST));
            $this->add_exposed_address("MySql", "http", env('HOST', self::DEFAULT_HOST), env('MYSQL_PORT'));
            $mysql->map_port(env('MYSQL_PORT'), 3306);
        }

        return $mysql;
    }

    private function build_dusk(): ?Dusk
    {
        if (empty(env("ENABLE_BROWSER_TESTS"))) {
            return null;
        }

        /** @var Dusk $dusk */
        $dusk = $this->add_container(Dusk::class)
            ->add_network($this->internal_network());

        return $dusk;
    }

    public function build_websocket(): ?Websocket
    {
        if (empty(env("WEBSOCKET_PORT"))) {
            return null;
        }

        /** @var Websocket $websocket */
        $websocket = $this->add_container(Websocket::class)
            ->add_network($this->internal_network())
            ->depends_on('php');

        $proxy_network = env('REVERSE_PROXY_NETWORK');

        if (empty($proxy_network)) {
            $websocket->map_port(env("WEBSOCKET_PORT"), 6001);
            $this->add_exposed_host(env('HOST', self::DEFAULT_HOST));
            $this->add_exposed_address("Websocket", "http", env('HOST', self::DEFAULT_HOST), env('WEBSOCKET_PORT'));
        } else {
            $websocket->add_network($proxy_network);
        }

        if ($shared_db_network = env('MYSQL_SHARED_DB_NETWORK')) {
            $websocket->add_network($shared_db_network);
        } else {
            $websocket->depends_on('mysql');
        }

        return $websocket;
    }

    public function build_phpmyadmin(MySql $mysql, Nginx $nginx): ?PhpMyAdmin
    {

        if (env('ENV', 'local') != 'local') {
            return null;
        }
        if (empty(env("PHPMYADMIN_PORT")) && empty(env("PHPMYADMIN_SUBDOMAIN"))) {
            return null;
        }


        /** @var PhpMyAdmin $phpmyadmin */
        $phpmyadmin = $this->add_container(PhpMyAdmin::class)->add_network($this->internal_network());
        $phpmyadmin->set_database_service($mysql->service_name());
        $phpmyadmin->set_database_root_password($mysql->get_environment('MYSQL_ROOT_PASSWORD', 'root'));
        $phpmyadmin->depends_on($mysql->service_name());

        if (!empty(env("PHPMYADMIN_PORT"))) {
            $phpmyadmin->map_port(env("PHPMYADMIN_PORT"), 80);
            $this->add_exposed_host(env('HOST', self::DEFAULT_HOST));
            $this->add_exposed_address("PhpMyAdmin", "http", env('HOST', self::DEFAULT_HOST), env('PHPMYADMIN_PORT'));
        }

        if (!empty(env("PHPMYADMIN_SUBDOMAIN"))) {
            $host = env('PHPMYADMIN_SUBDOMAIN').".".env('HOST');
            $nginx->add_proxy($host, 80, $phpmyadmin->service_name(), 80);
            $this->add_exposed_host($host);
            $this->add_exposed_address("PhpMyAdmin ", "http", $host, 80);
        }

        return $phpmyadmin;
    }

    public function build_mailhog(Nginx $nginx): ?MailHog
    {
        if (env('ENV', 'local') != 'local') {
            return null;
        }
        if (empty(env("MAILHOG_PORT")) && empty(env("MAILHOG_SUBDOMAIN"))) {
            return null;
        }


        /** @var MailHog $mailhog */
        $mailhog = $this->add_container(MailHog::class)->add_network($this->internal_network());

        if (!empty(env("MAILHOG_PORT"))) {
            $mailhog->map_port(env("MAILHOG_PORT"), 8025);
            $this->add_exposed_host(env('HOST', self::DEFAULT_HOST));
            $this->add_exposed_address("MailHog", "http", env('HOST', self::DEFAULT_HOST), env('MAILHOG_PORT'));
        }

        if (!empty(env("MAILHOG_SUBDOMAIN"))) {
            $host = env('MAILHOG_SUBDOMAIN').".".env('HOST');
            $nginx->add_proxy($host, 80, $mailhog->service_name(), 8025);
            $this->add_exposed_host($host);
            $this->add_exposed_address("MailHog ", "http", $host, 80);
        }
        return $mailhog;
    }

    public function setup(): void
    {
        parent::setup();

        $this->docker_service->add_network($this->internal_network(), $this->internal_network(), 'bridge');


        if ($proxy_network = env('REVERSE_PROXY_NETWORK')) {
            $this->docker_service->add_external_network($proxy_network);
        }

        if ($shared_db_network = env('MYSQL_SHARED_DB_NETWORK')) {
            $this->docker_service->add_external_network($shared_db_network);
        }
    }
}
