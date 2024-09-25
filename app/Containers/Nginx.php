<?php
/** @noinspection PhpUnused */

/** @noinspection LaravelFunctionsInspection */


namespace App\Containers;


use App\Containers\Commands\NginxRestart;
use App\Exceptions\DuplicateServiceException;
use App\Exceptions\ContainerException;
use App\Services\DockerService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Nginx extends Container
{

    protected string $service_name = "nginx";

    const PATH_NGINX_CONF = 'nginx/nginx.conf';
    const PATH_SITES_AVAILABLE = 'nginx/sites-available';

    const PATH_UPSTREAM_CONF = 'nginx/conf.d/upstream.conf';
    const PATH_BACKEND_NOT_FOUND_CONF = 'nginx/sites-available/backend_not_found.conf';

    const PATH_SITE_TEMPLATE = 'nginx/templates/site.conf';
    const PATH_SSL_SITE_TEMPLATE = 'nginx/templates/site-ssl.conf';
    const PATH_PROXY_TEMPLATE = 'nginx/templates/proxy.conf';
    const PATH_SSL_PROXY_TEMPLATE = 'nginx/templates/proxy-ssl.conf';
    const PATH_WEBSOCKET_SSL_PROXY_TEMPLATE = 'nginx/templates/proxy-ssl-ws.conf';

    const PHP_SERVICE_NAME = 'php';

    protected array $service_definition = [
        'restart' => 'unless-stopped',
        'working_dir' => '/var/www',
        'build' => [
            'context' => 'https://github.com/defstudio/docker-nginx.git#main',
        ],
        'depends_on' => [
            self::PHP_SERVICE_NAME,
        ],
    ];


    protected array $volumes = [
        self::HOST_SRC_VOLUME_PATH => '/var/www',
        self::HOST_CONFIG_VOLUME_PATH . self::PATH_NGINX_CONF => '/etc/nginx/nginx.conf',
        self::HOST_CONFIG_VOLUME_PATH . self::PATH_SITES_AVAILABLE => '/etc/nginx/sites-available',
    ];

    private array $sites = [];
    private array $proxies = [];

    private ?Php $php_service;

    private bool $enable_backend_not_found = false;


    public function __construct(Php $php_service)
    {
        parent::__construct();

        $this->php_service = $php_service;

        if (!empty(env('EXPOSE_INTERNAL_HOST'))) {
            $this->expose_internal_host();
        }
    }

    public function add_site(string $host, int $port = 80, $root = "/var/www", ?string $ssl_certificate = null, string $ssl_certificate_key = null, string $extra = ''): self
    {
        $this->sites[] = [
            'host' => $host,
            'port' => $port,
            'root' => $root,
            'ssl_certificate' => $ssl_certificate,
            'ssl_certificate_key' => $ssl_certificate_key,
            'extra' => $extra,
            'index_support' => env('NGINX_SUPPORT_INDEX', false) ? '$uri/' : '',
        ];

        return $this;
    }

    public function add_proxy(string $host, int $port, string $proxy_target, int $proxy_port, ?string $ssl_certificate = null, string $ssl_certificate_key = null, string $extra = '', string $proxy_protocol = 'http'): self
    {
        $this->proxies[] = [
            'port' => $port,
            'host' => $host,
            'proxy_target' => $proxy_target,
            'proxy_port' => $proxy_port,
            'ssl_certificate' => $ssl_certificate,
            'ssl_certificate_key' => $ssl_certificate_key,
            'extra' => $extra,
            'proxy_protocol' => $proxy_protocol,
        ];

        return $this;
    }

    public function reset_proxies(): self
    {
        $this->proxies = [];

        return $this;
    }

    public function reset_sites(): self
    {
        $this->proxies = [];

        return $this;
    }

    public function set_php_service(Php $php_service)
    {
        $this->php_service = $php_service;
    }

    public function unset_php_service()
    {
        $this->php_service = null;
        unset($this->service_definition['depends_on']);
    }

    public function enable_backend_not_found_page(): self
    {
        $this->enable_backend_not_found = true;

        return $this;
    }

    public function expose_internal_host(): self
    {
        $this->set_service_definition('extra_hosts', ['host.docker.internal:host-gateway']);
        return $this;
    }



    /**
     * @param DockerService $service
     *
     * @throws DuplicateServiceException
     * @throws ContainerException
     */
    public function setup(DockerService $service): void
    {

        if (!empty($this->php_service)) {
            $this->php_service->service_name(self::PHP_SERVICE_NAME);
            $service->add_container($this->php_service);
        }
    }


    public function publish_assets()
    {
        $this->publish_nginx_conf();
        $this->publish_sites_available_directory();
        $this->publish_sites();
        $this->publish_upstream_conf();
        $this->publish_backend_not_found();
    }

    protected function publish_nginx_conf(): void
    {
        $template = Storage::get(self::PATH_NGINX_CONF);

        $this->compile_template($template, [
            'robots' => env('ENABLE_ROBOTS', true)
                ? ''
                : 'add_header  X-Robots-Tag "noindex, nofollow, nosnippet, noarchive";'
        ]);

        $this->disk()->put(self::PATH_NGINX_CONF, $template);
    }

    protected function publish_upstream_conf(): void
    {
        if (!empty($this->php_service)) {
            $template = Storage::get(self::PATH_UPSTREAM_CONF);

            $this->compile_template($template, ['php_service' => self::PHP_SERVICE_NAME]);
            $this->disk()->put(self::PATH_UPSTREAM_CONF, $template);

            $this->set_volume(self::HOST_CONFIG_VOLUME_PATH . self::PATH_UPSTREAM_CONF, '/etc/nginx/conf.d/upstream.conf');
        }
    }

    protected function publish_backend_not_found(): void
    {
        if (!$this->enable_backend_not_found) {
            return;
        }

        $this->disk()->put(self::PATH_BACKEND_NOT_FOUND_CONF, Storage::get(self::PATH_BACKEND_NOT_FOUND_CONF));
    }

    protected function publish_sites_available_directory()
    {
        if ($this->disk()->exists(self::PATH_SITES_AVAILABLE)) {
            $this->disk()->deleteDirectory(self::PATH_SITES_AVAILABLE);
        }

        $this->disk()->makeDirectory(self::PATH_SITES_AVAILABLE);
    }

    protected function publish_sites()
    {
        foreach ($this->sites as $site) {
            $this->publish_site($site);
        }
        foreach ($this->proxies as $proxy) {
            $this->publish_proxy($proxy);
        }
    }

    protected function publish_site(array $site_data)
    {
        if (empty($site_data['ssl_certificate'])) {
            $template = Storage::get(self::PATH_SITE_TEMPLATE);
        } else {
            $template = Storage::get(self::PATH_SSL_SITE_TEMPLATE);
        }


        $this->compile_template($template, $site_data);

        $this->disk()->put(self::PATH_SITES_AVAILABLE . "/{$site_data['host']}.{$site_data['port']}.conf", $template);
    }

    protected function publish_proxy(array $proxy_data)
    {
        if (empty($proxy_data['ssl_certificate'])) {
            $template = Storage::get(self::PATH_PROXY_TEMPLATE);
        } else {
            $template = Storage::get(self::PATH_SSL_PROXY_TEMPLATE);
        }

        $this->compile_template($template, $proxy_data);
        $this->disk()->put(
            Str::of( self::PATH_SITES_AVAILABLE)
            ->append("/", $proxy_data['host'])
            ->append(".", $proxy_data['port'])
            ->append('.conf')
            , $template
        );

    }

    public function commands(): array
    {
        return [
            NginxRestart::class,
        ];
    }
}
