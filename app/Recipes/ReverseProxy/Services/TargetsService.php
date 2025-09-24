<?php /** @noinspection PhpUnhandledExceptionInspection */


namespace App\Recipes\ReverseProxy\Services;


use App\Containers\Nginx;
use App\Recipes\ReverseProxy\Exceptions\ProxyTargetInvalidException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;

class TargetsService
{

    const TARGETS_FILE = 'targets.json';

    const TARGETS_TEMPLATE = [
        [
            'active' => 0,
            'project' => '[project_name]',
            'port' => 443,
            'hostname' => 'example.ktm',
            'ssl_certificate' => '/etc/letsencrypt/live/example.ktm/fullchain.pem',
            'ssl_certificate_key' => '/etc/letsencrypt/live/example.ktm/privkey.pem',
        ],
        [
            'active' => 0,
            'destination_hostname' => '[project_name]_nginx_1',
            'destination_port' => 443,
            'hostname' => 'example.ktm',
            'port' => 443,
            'ssl_certificate' => '/etc/letsencrypt/live/example.ktm/fullchain.pem',
            'ssl_certificate_key' => '/etc/letsencrypt/live/example.ktm/privkey.pem',
        ],
    ];

    private function disk(): Filesystem
    {
        return Storage::disk('cwd');
    }

    public function init_targets_file()
    {
        if (!$this->disk()->exists(self::TARGETS_FILE)) {
            $this->set_targets(self::TARGETS_TEMPLATE);
        }
    }

    private function get_target_id(object $target): string
    {
        $project = $target->project ?? $target->hostname;
        return "{$project}:{$target->port}";
    }

    public function get_target($target_id): ?object
    {
        $targets = $this->get_targets();

        foreach ($targets as $target) {
            if ($this->get_target_id($target) == $target_id) {
                return $target;
            }
        }

        return null;
    }

    public function set_target($new_target): void
    {
        $targets = $this->get_targets();

        foreach ($targets as $index => $target) {
            if ($this->get_target_id($target) == $this->get_target_id($new_target)) {
                $targets[$index] = $new_target;

                $this->set_targets($targets);
                return;
            }
        }

        $targets[] = $new_target;
        $this->set_targets($targets);
    }

    public function get_targets(): array
    {
        $targets = json_decode($this->disk()->get(self::TARGETS_FILE));
        if (empty($targets)) {
            throw new ProxyTargetInvalidException("targets.json file is invalid");
        }

        return $targets;
    }

    private function set_targets(array $targets): void
    {
        $this->disk()->put(self::TARGETS_FILE, json_encode($targets, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function make_proxies(Nginx &$nginx)
    {

        foreach ($this->get_targets() as $target) {
            if (!$this->target_active($target)) {
                continue;
            }

            $destination_hostname = $target->destination_hostname ?? "{$target->project}_nginx_1";
            $destination_port = $target->destination_port ?? $target->port;
            $hostname = $target->hostname;
            $port = $target->port;
            $proxy_protocol = $target->proxy_protocol ?? 'http';
            $ssl_certificate = $target->ssl_certificate ?? '';
            $ssl_certificate_key = $target->ssl_certificate_key ?? '';

            $target->ssl_client_certificate ??= '';
            $ssl_client_certificate = $target->ssl_client_certificate
                ? "    ssl_client_certificate /etc/nginx/client-certificates/$target->ssl_client_certificate;\n    ssl_verify_client on;\n    ssl_verify_depth 2;"
                : ''
            ;

            $nginx->add_proxy(
                host               : $hostname,
                port               : $port,
                proxy_target       : $destination_hostname,
                proxy_port         : $destination_port,
                ssl_certificate    : $ssl_certificate,
                ssl_certificate_key: $ssl_certificate_key,
                ssl_client_certificate: $ssl_client_certificate,
                proxy_protocol     : $proxy_protocol
            );

            foreach ($target->subdomains ?? [] as $subdomain => $visibility) {

                if ($visibility == 'local') {
                    $extra = "allow 192.168.1.0/24; \n deny all;";
                } else {
                    $extra = '';
                }

                $nginx->add_proxy(
                    "$subdomain.$hostname",
                    $port,
                    $destination_hostname,
                    $destination_port,
                    $ssl_certificate,
                    $ssl_certificate_key,
                    $extra,
                    ssl_client_certificate: $ssl_client_certificate,
                );
            }
        }
    }

    private function target_active(object $target): bool
    {
        return ($target->active ?? 1) == 1;
    }

    public function reload_targets(Nginx $nginx, Command $parent_command): int
    {
        $nginx->reset_proxies();
        $this->make_proxies($nginx);
        $nginx->publish_assets();

        return $parent_command->call('nginx:restart');
    }
}
