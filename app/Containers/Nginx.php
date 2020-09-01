<?php


    namespace App\Containers;


    use App\Exceptions\DuplicateNetworkException;
    use App\Exceptions\DuplicateServiceException;
    use App\Exceptions\ContainerException;
    use App\Services\DockerService;
    use Illuminate\Support\Facades\Storage;

    class Nginx extends Container{

        protected $service_name = "nginx";

        const NGINX_CONF = 'nginx/nginx.conf';
        const SITES_AVAILABLE_DIR = 'nginx/sites-available';
        const UPSTREAM_CONF = 'nginx/conf.d/upstream.conf';
        const SITE_TEMPLATE = 'nginx/templates/site.conf';
        const PROXY_TEMPLATE = 'nginx/templates/proxy.conf';

        const PHP_SERVICE_NAME = 'php';

        protected $service_definition = [
            'restart'     => 'unless-stopped',
            'working_dir' => '/var/www',
            'build'       => [
                'context' => 'git@gitlab.com:defstudio/docker/nginx.git',
            ],
            'expose'      => [80,443],
            'depends_on'  => [
                self::PHP_SERVICE_NAME,
            ],
        ];



        protected $volumes = [
            self::HOST_SRC_VOLUME_PATH => '/var/www',
            self::HOST_CONFIG_VOLUME_PATH . self::NGINX_CONF => '/etc/nginx/nginx.conf',
            self::HOST_CONFIG_VOLUME_PATH . self::UPSTREAM_CONF => '/etc/nginx/conf.d/upstream.conf',
            self::HOST_CONFIG_VOLUME_PATH . self::SITES_AVAILABLE_DIR => '/etc/nginx/sites-available',
        ];

        private $sites = [];
        private $proxies = [];

        private $php_service;


        public function __construct(Php $php_service){
            parent::__construct();

            $this->php_service = $php_service;
        }

        public function add_site($host, $root = "/var/www", $extra=''){
            $this->sites[$host] = [
                'host' => $host,
                'root' => $root,
                'extra' => $extra
            ];
        }

        public function add_proxy($host, $proxy_target, $proxy_port=80, $extra=''){
            $this->proxies[$host] = [
                'host' => $host,
                'proxy_target' => $proxy_target,
                'proxy_port' => $proxy_port,
                'extra' => $extra
            ];
        }

        public function set_php_service(Php $php_service){
            $this->php_service = $php_service;
        }

        public function unset_php_service(){
            $this->php_service = null;
            unset($this->service_definition['depends_on']);
        }


        /**
         * @param DockerService $service
         * @throws DuplicateServiceException
         * @throws ContainerException
         */
        public function setup(DockerService $service){

            if(!empty($this->php_service)){
                $this->php_service->service_name(self::PHP_SERVICE_NAME);
                $service->add_container($this->php_service);
            }
        }


        public function publish_assets(){
            $this->publish_nginx_conf();
            $this->publish_sites_available_directory();
            $this->publish_sites();

                $this->publish_upstream_conf();

        }

        protected function publish_nginx_conf(){
            $this->disk()->put(self::NGINX_CONF, Storage::get(self::NGINX_CONF));
        }

        protected function publish_upstream_conf(){
            $template = Storage::get(self::UPSTREAM_CONF);

            $this->compile_template($template, ['php_service' => self::PHP_SERVICE_NAME]);
            $this->disk()->put(self::UPSTREAM_CONF, $template);
        }

        protected function publish_sites_available_directory(){
            if($this->disk()->exists(self::SITES_AVAILABLE_DIR)){
                $this->disk()->deleteDirectory(self::SITES_AVAILABLE_DIR);
            }

            $this->disk()->makeDirectory(self::SITES_AVAILABLE_DIR);
        }

        protected function publish_sites(){
            foreach($this->sites as $site){
                        $this->publish_site($site);
            }
            foreach($this->proxies as $proxy){
                $this->publish_proxy($proxy);
            }
        }

        protected function publish_site(array $site_data){
            $template = Storage::get(self::SITE_TEMPLATE);

            $this->compile_template($template, $site_data);

            $this->disk()->put(self::SITES_AVAILABLE_DIR . "/" . $site_data['host'] . ".conf", $template);
        }

        protected function publish_proxy(array $proxy_data){
            $template = Storage::get(self::PROXY_TEMPLATE);

            $this->compile_template($template, $proxy_data);

            $this->disk()->put(self::SITES_AVAILABLE_DIR . "/" . $proxy_data['host'] . ".conf", $template);
        }

    }
