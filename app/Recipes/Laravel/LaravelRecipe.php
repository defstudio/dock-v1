<?php


    namespace App\Recipes\Laravel;


    use App\Containers\Container;
    use App\Containers\MailHog;
    use App\Containers\Nginx;
    use App\Containers\Node;
    use App\Containers\PhpMyAdmin;
    use App\Containers\Redis;
    use App\Containers\Composer;
    use App\Containers\MySql;
    use App\Recipes\DockerComposeRecipe;
    use App\Recipes\Laravel\Commands\Artisan;
    use App\Recipes\Laravel\Commands\Init;
    use App\Recipes\Laravel\Commands\Install;
    use App\Recipes\Laravel\Commands\Migrate;
    use App\Recipes\Laravel\Containers\EchoServer;
    use App\Recipes\Laravel\Containers\Php;
    use App\Recipes\Laravel\Containers\Worker;
    use App\Traits\InteractsWithEnvContent;
    use Illuminate\Console\Command;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use Illuminate\Contracts\Filesystem\FileNotFoundException;
    use Illuminate\Support\Facades\Storage;

    class LaravelRecipe extends DockerComposeRecipe{
        use InteractsWithEnvContent;

        const LABEL = 'Laravel';


        public function init(Command $parent_command): int{

            try{

                $env_content = Storage::disk('local')->get('env/.env.laravel');

            } catch(FileNotFoundException $e){
                $parent_command->error('Cannot find this recipe .env file template');
                return 1;
            }

            if($parent_command->confirm('Would you like to customize your recipe?')){

                //<editor-fold desc="General Configuration">
                $parent_command->question("Network configuration");

                $application_host = $parent_command->ask("Enter application hostname", "laravel.ktm");
                $this->set_env($env_content, "HOST", $application_host);

                $application_env = $parent_command->choice("Enter application environment", ['local', 'production'], 0);
                $this->set_env($env_content, "ENV", $application_env);
                //</editor-fold>


                //<editor-fold desc="Network CONfiguration">
                $parent_command->question("Network configuration");
                if($parent_command->confirm("Is the application behind a proxy?")){
                    $this->comment_env($env_content, 'NGINX_PORT');
                    $this->comment_env($env_content, 'NGINX_PORT_SSL');
                    $this->comment_env($env_content, 'MYSQL_PORT');
                    $this->comment_env($env_content, 'PHPMYADMIN_PORT');
                    $this->comment_env($env_content, 'MAILHOG_PORT');
                }else{

                    $parent_command->info("Exposed services selection (leave blank to skip)");

                    $nginx_port = $parent_command->ask("Enter Nginx exposed port", 80);
                    if(!empty($nginx_port)){
                        if($nginx_port=='443'){
                            $this->comment_env($env_content, 'NGINX_PORT');
                            $this->set_env($env_content, "NGINX_PORT_SSL", $nginx_port);
                        }else{
                            $this->set_env($env_content, "NGINX_PORT", $nginx_port);
                        }
                    }else{
                        $this->comment_env($env_content, 'NGINX_PORT');
                    }

                    if($nginx_port!=443){
                        $nginx_ssl_port = $parent_command->ask("Enter Nginx SSL exposed port", 443);
                        if(!empty($nginx_ssl_port)) {
                            $this->set_env($env_content, "NGINX_PORT_SSL", $nginx_ssl_port);
                        }else{
                            $this->comment_env($env_content, 'NGINX_PORT_SSL');
                        }
                    }

                    $mysql_port = $parent_command->ask("Enter MySQL exposed port", 3306);
                    if(!empty($mysql_port)) {
                        $this->set_env($env_content, "MYSQL_PORT", $mysql_port);
                    }else{
                        $this->comment_env($env_content, 'MYSQL_PORT');
                    }

                    $phpmyadmin_port = $parent_command->ask("Enter PhpMyAdmin exposed port", 8081);
                    if(!empty($phpmyadmin_port)) {
                        $this->set_env($env_content, "PHPMYADMIN_PORT", $phpmyadmin_port);
                    }else{
                        $this->comment_env($env_content, 'PHPMYADMIN_PORT');
                    }

                    $phpmyadmin_subdomain = $parent_command->ask("Enter PhpMyAdmin exposed subdomain", "mysql");
                    if(!empty($phpmyadmin_subdomain)) {
                        $this->set_env($env_content, "PHPMYADMIN_SUBDOMAIN", $phpmyadmin_subdomain);
                    }else{
                        $this->comment_env($env_content, 'PHPMYADMIN_SUBDOMAIN');
                    }

                    $mailhog_port = $parent_command->ask("Enter MailHog exposed port", 8025);
                    if(!empty($mailhog_port)) {
                        $this->set_env($env_content, "MAILHOG_PORT", $mailhog_port);
                    }else{
                        $this->comment_env($env_content, 'MAILHOG_PORT');
                    }

                    $mailhog_subdomain = $parent_command->ask("Enter MailHog exposed subdomain", "mail");
                    if(!empty($mailhog_subdomain)) {
                        $this->set_env($env_content, "MAILHOG_SUBDOMAIN", $mailhog_subdomain);
                    }else{
                        $this->comment_env($env_content, 'MAILHOG_SUBDOMAIN');
                    }
                }
                //</editor-fold>


                //<editor-fold desc="MySql Configuration">
                $parent_command->question("MySql configuration");
                $this->set_env($env_content, 'MYSQL_DATABASE', $parent_command->ask("Database Name", "database"));
                $this->set_env($env_content, 'MYSQL_USER', $parent_command->ask("Database User", "dbuser"));
                $this->set_env($env_content, 'MYSQL_PASSWORD', $parent_command->ask("Database Password", "dbpassword"));
                $this->set_env($env_content, 'MYSQL_ROOT_PASSWORD', $parent_command->ask("Database Root Password", "root"));
                //</editor-fold>


            }

            Storage::disk('cwd')->put('.env', $env_content);


            return 0;
        }




        protected function recipe_commands(): array{
            return [
                Install::class,
                Init::class,
                Artisan::class,
                Migrate::class
            ];
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


    }
