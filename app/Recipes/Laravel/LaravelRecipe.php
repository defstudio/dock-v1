<?php


    namespace App\Recipes\Laravel;


    use App\Containers\Container;
    use App\Containers\MailHog;
    use App\Containers\Nginx;
    use App\Containers\Node;
    use App\Containers\PhpMyAdmin;
    use App\Containers\Redis;
    use App\Contracts\DockerComposeRecipe;
    use App\Containers\Composer;
    use App\Containers\MySql;
    use App\Exceptions\DuplicateServiceException;
    use App\Exceptions\ContainerException;
    use App\Recipes\Laravel\Commands\Artisan;
    use App\Recipes\Laravel\Commands\Init;
    use App\Recipes\Laravel\Commands\Install;
    use App\Recipes\Laravel\Commands\Migrate;
    use App\Recipes\Laravel\Containers\EchoServer;
    use App\Recipes\Laravel\Containers\Php;
    use App\Recipes\Laravel\Containers\Worker;
    use App\Services\DockerService;
    use Illuminate\Contracts\Container\BindingResolutionException;

    class LaravelRecipe implements DockerComposeRecipe{

        const LABEL = 'Laravel';

        private $docker_service;

        /** @var Container[] $services */
        private $containers = [];

        private $exposed_hosts = [];
        private $exposed_addresses = [];

        public function __construct(DockerService $docker_service){
            $this->docker_service = $docker_service;
        }

        /**
         * @throws BindingResolutionException
         */
        public function build(){
            $nginx = $this->build_nginx();
            $mysql = $this->build_mysql();


            if(env('ENV', 'local')) {
                if(!empty(env("PHPMYADMIN_PORT")) || !empty(env("PHPMYADMIN_SUBDOMAIN"))){
                    $this->build_phpmyadmin($mysql, $nginx);
                }
            }

            if(env('ENV', 'local')) {
                if(!empty(env("MAILHOG_PORT")) || !empty(env("MAILHOG_SUBDOMAIN"))){
                    $this->build_mailhog($nginx);
                }
            }

            $this->add_container(Worker::class);

            $this->add_container(Composer::class);

            $this->add_container(Node::class);

            /** @var Redis $redis */
            $redis = $this->add_container(Redis::class);

            $this->build_echo_server($redis, $nginx);


        }

        /**
         * @return Nginx
         * @throws BindingResolutionException
         */
        private function build_nginx(): Nginx{
            /** @var Nginx $nginx */
            $nginx = $this->add_container(Nginx::class, [app()->make(Php::class)]);
            $nginx->add_site(env('HOST', "laravel.ktm"), '/var/www/public', '
                location /socket.io {
                    proxy_pass http://localhost:6001;
                    proxy_http_version 1.1;
                    proxy_set_header Upgrade $http_upgrade;
                    proxy_set_header Connection "Upgrade";
                }
            ');
            if(!empty(env('NGINX_PORT'))) {
                $nginx->map_port(env('NGINX_PORT'), 80);
                $this->add_exposed_host(env('HOST', "laravel.ktm"));
                $this->add_exposed_address("Laravel", "http", env('HOST', "laravel.ktm"), env('NGINX_PORT'));
            }
            if(!empty(env('NGINX_PORT_SSL'))) {
                $nginx->map_port(env('NGINX_PORT_SSL'), 443);
                $this->add_exposed_host(env('HOST', "laravel.ktm"));
                $this->add_exposed_address("Laravel SSL", "https", env('HOST', "laravel.ktm"), env('NGINX_PORT_SSL'));
            }

            return $nginx;
        }

        /**
         * @return MySql
         * @throws BindingResolutionException
         */
        private function build_mysql(): MySql{
            /** @var MySql $mysql */
            $mysql = $this->add_container(MySql::class);
            $mysql->set_database(env('MYSQL_DATABASE', 'laravel'));
            $mysql->set_user(env('MYSQL_USER', 'dbuser'));
            $mysql->set_password(env('MYSQL_PASSWORD', 'dbpassword'));
            $mysql->set_root_password(env('MYSQL_ROOT_PASSWORD', 'root'));

            if(!empty(env('MYSQL_PORT'))) {
                $this->add_exposed_host(env('HOST', "laravel.ktm"));
                $this->add_exposed_address("MySql", "http", env('HOST', "laravel.ktm"), env('MYSQL_PORT'));
                $mysql->map_port(env('MYSQL_PORT'), 3306);
            }

            return $mysql;
        }

        /**
         * @param MySql $mysql
         * @param Nginx $nginx
         * @return PhpMyAdmin
         * @throws BindingResolutionException
         */
        public function build_phpmyadmin(MySql $mysql, Nginx $nginx): PhpMyAdmin{
            /** @var PhpMyAdmin $phpmyadmin */
            $phpmyadmin = $this->add_container(PhpMyAdmin::class);
            $phpmyadmin->set_database_service($mysql->service_name());
            $phpmyadmin->set_database_root_password($mysql->get_environment('MYSQL_ROOT_PASSWORD', 'root'));
            $phpmyadmin->depends_on($mysql->service_name());

            if(!empty(env("PHPMYADMIN_PORT"))){
                $phpmyadmin->map_port(env("PHPMYADMIN_PORT"), 80);
                $this->add_exposed_host(env('HOST', "laravel.ktm"));
                $this->add_exposed_address("PhpMyAdmin", "http", env('HOST', "laravel.ktm"), env('PHPMYADMIN_PORT'));
            }

            if(!empty(env("PHPMYADMIN_SUBDOMAIN"))){
                $host = env('PHPMYADMIN_SUBDOMAIN') . "." . env('HOST');
                $nginx->add_proxy($host, $phpmyadmin->service_name());
                $this->add_exposed_host($host);
                $this->add_exposed_address("PhpMyAdmin ", "http", $host, 80);
            }

            return $phpmyadmin;
        }

        /**
         * @param Nginx $nginx
         * @return MailHog
         * @throws BindingResolutionException
         */
        public function build_mailhog(Nginx $nginx): MailHog{

            /** @var MailHog $mailhog */
            $mailhog = $this->add_container(MailHog::class);

            if(!empty(env("MAILHOG_PORT"))){
                $mailhog->map_port(env("MAILHOG_PORT"), 8025);
                $this->add_exposed_host(env('HOST', "laravel.ktm"));
                $this->add_exposed_address("MailHog", "http", env('HOST', "laravel.ktm"), env('MAILHOG_PORT'));
            }

            if(!empty(env("MAILHOG_SUBDOMAIN"))){
                $host = env('MAILHOG_SUBDOMAIN') . "." . env('HOST');
                $nginx->add_proxy($host, $mailhog->service_name());
                $this->add_exposed_host($host);
                $this->add_exposed_address("MailHog ", "http", $host, 80);
            }


            return $mailhog;
        }

        /**
         * @param Redis $redis
         * @param Nginx $nginx
         * @return EchoServer
         * @throws BindingResolutionException
         */
        public function build_echo_server(Redis $redis, Nginx $nginx):EchoServer{
            /** @var EchoServer $echo_server */
            $echo_server = $this->add_container(EchoServer::class);
            $echo_server->depends_on($redis->service_name());
            $echo_server->set_auth_host($nginx->service_name());
            $echo_server->set_debug(false);
            $echo_server->set_devmode(true);
            $echo_server->set_allow_cors(true);
            $echo_server->set_allow_origin("http://" . env('HOST', "laravel.ktm"));
            $echo_server->set_allow_methods();
            $echo_server->set_allow_headers();
            $echo_server->set_clients();
            $echo_server->set_redis_port();
            $echo_server->set_redis_service($redis->service_name());
            $echo_server->set_protocol();
            $echo_server->set_ssl_cert_path();
            $echo_server->set_ssl_key_path();
            $echo_server->set_ssl_chain_path();
            $echo_server->set_ssl_passphrase();

            return $echo_server;
        }

        public function commands(): array{
            $commands = [
                Install::class,
                Init::class,
                Artisan::class,
                Migrate::class,
            ];



            foreach($this->containers as $container){
                foreach($container->commands() as $command){
                    $commands[] = $command;
                }
            }

            return array_unique($commands);
        }

        /**
         * @param string $class
         * @param array $arguments
         * @return Container
         * @throws BindingResolutionException
         */
        private function add_container(string $class, array $arguments = []): Container{
            $container = app()->make($class, $arguments);
            $this->containers[] = $container;
            return $container;
        }

        /**
         * @throws DuplicateServiceException
         * @throws ContainerException
         */
        public function setup(){
            foreach($this->containers as $container){
                $this->docker_service->add_container($container);
            }
        }

        private function add_exposed_host($hostname){
            $this->exposed_hosts[] = $hostname;
        }

        private function add_exposed_address(string $label, string $protocol, $uri, $port){
            if($port==80||$port==443){
                $port = "";
            }else{
                $port = ":$port";
            }
            $this->exposed_addresses[$label] = "$protocol://{$uri}{$port}";
        }

        public function hosts(): array{
            return array_unique($this->exposed_hosts);
        }

        public function urls(): array{
            return $this->exposed_addresses;
        }

        public function label(): string{
            return static::LABEL;
        }

    }
