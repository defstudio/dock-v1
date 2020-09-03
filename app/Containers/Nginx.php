<?php


    namespace App\Containers;


    use App\Containers\Commands\NginxReload;
    use App\Exceptions\DuplicateNetworkException;
    use App\Exceptions\DuplicateServiceException;
    use App\Exceptions\ContainerException;
    use App\Services\DockerService;
    use Illuminate\Support\Facades\Storage;

    class Nginx extends Container{

        protected string $service_name = "nginx";

        const PATH_NGINX_CONF = 'nginx/nginx.conf';
        const PATH_SITES_AVAILABLE = 'nginx/sites-available';
        const PATH_UPSTREAM_CONF = 'nginx/conf.d/upstream.conf';

        const PATH_SITE_TEMPLATE = 'nginx/templates/site.conf';
        const PATH_PROXY_TEMPLATE = 'nginx/templates/proxy.conf';
        const PATH_SSL_PROXY_TEMPLATE = 'nginx/templates/proxy-ssl.conf';

        const PHP_SERVICE_NAME = 'php';

        protected array $service_definition = [
            'restart'     => 'unless-stopped',
            'working_dir' => '/var/www',
            'build'       => [
                'context' => 'https://gitlab.com/defstudio/docker/nginx.git',
            ],
            'expose'      => [80,443],
            'depends_on'  => [
                self::PHP_SERVICE_NAME,
            ],
        ];



        protected array $volumes = [
            self::HOST_SRC_VOLUME_PATH                                 => '/var/www',
            self::HOST_CONFIG_VOLUME_PATH . self::PATH_NGINX_CONF      => '/etc/nginx/nginx.conf',
            self::HOST_CONFIG_VOLUME_PATH . self::PATH_UPSTREAM_CONF   => '/etc/nginx/conf.d/upstream.conf',
            self::HOST_CONFIG_VOLUME_PATH . self::PATH_SITES_AVAILABLE => '/etc/nginx/sites-available',
        ];

        private array $sites = [];
        private array $proxies = [];

        private ?Php $php_service;


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

        public function add_proxy(string $host, int $port, string $proxy_target, int $proxy_port, string $ssl_certificate='', string $ssl_certificate_key='', string $extra=''): void{
            $this->proxies[] = [
                'port' => $port,
                'host' => $host,
                'proxy_target' => $proxy_target,
                'proxy_port' => $proxy_port,
                'ssl_certificate' => $ssl_certificate,
                'ssl_certificate_key' => $ssl_certificate_key,
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
            $this->disk()->put(self::PATH_NGINX_CONF, Storage::get(self::PATH_NGINX_CONF));
        }

        protected function publish_upstream_conf(){
            if(!empty($this->php_service)){
                $template = Storage::get(self::PATH_UPSTREAM_CONF);

                $this->compile_template($template, ['php_service' => self::PHP_SERVICE_NAME]);
                $this->disk()->put(self::PATH_UPSTREAM_CONF, $template);
            }
        }

        protected function publish_sites_available_directory(){
            if($this->disk()->exists(self::PATH_SITES_AVAILABLE)){
                $this->disk()->deleteDirectory(self::PATH_SITES_AVAILABLE);
            }

            $this->disk()->makeDirectory(self::PATH_SITES_AVAILABLE);
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
            $template = Storage::get(self::PATH_SITE_TEMPLATE);

            $this->compile_template($template, $site_data);

            $this->disk()->put(self::PATH_SITES_AVAILABLE . "/" . $site_data['host'] . ".conf", $template);
        }

        protected function publish_proxy(array $proxy_data){
            if(empty($proxy_data['ssl_certificate'])){
                $template = Storage::get(self::PATH_PROXY_TEMPLATE);
            }else{
                $template = Storage::get(self::PATH_SSL_PROXY_TEMPLATE);
            }

            $this->compile_template($template, $proxy_data);
            $this->disk()->put(self::PATH_SITES_AVAILABLE . "/{$proxy_data['host']}.{$proxy_data['port']}.conf", $template);

        }

        public function commands(): array{
            return [
                NginxReload::class,
            ];
        }


    }
