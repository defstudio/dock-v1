<?php


    namespace App\Recipes\Laravel\Containers;

    use App\Containers\Container;
    use Illuminate\Support\Arr;

    class EchoServer extends Container{
        protected $service_name = 'echo-server';

        protected $service_definition = [
            'restart'     => 'unless-stopped',
            'working_dir' => '/var/www',
            'build'       => [
                'context' => 'git@gitlab.com/defstudio/docker/laravel-echo-server.git',
            ],
            'environment' => [
                'ECHO_AUTH_HOST=http://nginx',
                'ECHO_DEBUG=false',
                'ECHO_CLIENTS=[]',
                'ECHO_REDIS_PORT=6379',
                'ECHO_REDIS_HOSTNAME=redis',
                'ECHO_DEVMODE=false',
                'ECHO_PROTOCOL=http',
                'ECHO_SSL_CERT_PATH=',
                'ECHO_SSL_KEY_PATH=',
                'ECHO_SSL_CHAIN_PATH=',
                'ECHO_SSL_PASSPHRASE=',
                'ECHO_ALLOW_CORS=true',
                'ECHO_ALLOW_ORIGIN=http://localhost:80',
                'ECHO_ALLOW_METHODS="GET, POST"',
                'ECHO_ALLOW_HEADERS="Origin, Content-Type, X-Auth-Token, X-Requested-With, Accept, Authorization, X-CSRF-TOKEN, X-Socket-Id"',
            ],
            'expose'      => [6001],
        ];


        public function set_auth_host($service_name = "nginx"){
            $this->set_environment('ECHO_AUTH_HOST', "http://$service_name", false);
        }

        public function set_debug(bool $enabled = false){
            if($enabled){
                $this->set_environment("ECHO_DEBUG", "true", false);
            } else{
                $this->set_environment("ECHO_DEBUG", "false", false);
            }
        }

        public function set_devmode(bool $enabled = false){
            if($enabled){
                $this->set_environment("ECHO_DEVMODE", "true", false);
            } else{
                $this->set_environment("ECHO_DEVMODE", "false", false);
            }
        }

        public function set_allow_cors(bool $enabled = false){
            if($enabled){
                $this->set_environment("ECHO_ALLOW_CORS", "true", false);
            } else{
                $this->set_environment("ECHO_ALLOW_CORS", "false", false);
            }
        }

        public function set_allow_origin(string $url = "http://localhost:80"){
            $this->set_environment("ECHO_ALLOW_ORIGIN", $url, false);
        }

        /**
         * @param string[] $methods
         */
        public function set_allow_methods($methods = ["GET", "POST"]){
            $methods = Arr::wrap($methods);
            $methods = implode(',', $methods);
            $this->set_environment("ECHO_ALLOW_HEADERS", "$methods", false);
        }


        /**
         * @param string[] $headers
         */
        public function set_allow_headers($headers = ["Origin", "Content-Type", "X-Auth-Token", "X-Requested-With", "Accept", "Authorization", "X-CSRF-TOKEN", "X-Socket-Id"]){
            $headers = Arr::wrap($headers);
            $headers = implode(',', $headers);
            $this->set_environment("ECHO_ALLOW_METHODS", "$headers", false);
        }


        /**
         * @param string[]|string $clients
         */
        public function set_clients($clients = []){
            $clients = Arr::wrap($clients);
            $clients = implode(',', $clients);
            $this->set_environment("ECHO_CLIENTS", "[$clients]", false);
        }

        public function set_redis_port($port = "6379"){
            $this->set_environment("ECHO_REDIS_PORT", $port, false);
        }

        public function set_redis_service($service_name = "redis"){
            $this->set_environment("ECHO_REDIS_HOSTNAME", $service_name, false);
        }

        public function set_protocol($protocol = "http"){
            $this->set_environment("ECHO_PROTOCOL", $protocol, false);
        }

        public function set_ssl_cert_path($path = ""){
            $this->set_environment("ECHO_SSL_CERT_PATH", $path, false);
        }

        public function set_ssl_key_path($path = ""){
            $this->set_environment("ECHO_SSL_KEY_PATH", $path, false);
        }

        public function set_ssl_chain_path($path = ""){
            $this->set_environment("ECHO_SSL_CHAIN_PATH", $path, false);
        }

        public function set_ssl_passphrase($passhprase = ""){
            $this->set_environment("ECHO_SSL_PASSPHRASE", $passhprase, false);
        }


    }
