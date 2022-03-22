<?php /** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection LaravelFunctionsInspection */


namespace App\Services\SSL;


use App\Containers\CertbotCloudflare;
use App\Containers\CertbotStandalone;
use App\Contracts\SSLService;
use App\Recipes\DockerComposeRecipe;
use App\Traits\InteractsWithEnvContent;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Certbot implements SSLService
{
    use InteractsWithEnvContent;

    public function init_recipe(Command $parent_command, string $env_content): string
    {

        $challenge_modes = $parent_command->choice('Certbot challenge mode', [
            'dns-cloudflare',
            'standalone',
        ], 'dns-cloudflare', multiple: true);

        $this->set_env($env_content, 'CERTBOT_CHALLENGE_MODE', implode(",", $challenge_modes));

        if (in_array('dns-cloudflare', $challenge_modes)) {
            $env_content = $this->init_certbot_dns_challenge($parent_command, $env_content);
        }

        if (in_array('standalone', $challenge_modes)) {
            $env_content = $this->init_standalone_challenge($parent_command, $env_content);
        }

        return $env_content;
    }

    private function init_certbot_dns_challenge(Command $parent_command, string $env_content): string
    {

        $parent_command->info("Cloudflare DNS challenge setup");

        $token = $parent_command->ask('Cloudflare Token');

        $this->set_env($env_content, 'CLOUDFLARE_TOKEN', $token);

        return $env_content;
    }

    private function init_standalone_challenge(Command $parent_command, string $env_content): string
    {
        return $env_content;
    }

    public function challenge_modes(): Collection
    {
        $challenge_modes = explode(',', env('CERTBOT_CHALLENGE_MODE'));

        return collect($challenge_modes)
            ->map(fn (string $challenge_mode) => strtolower(trim($challenge_mode)));
    }

    public function build_ssl_provider(DockerComposeRecipe $recipe): void
    {
        $challenge_modes = $this->challenge_modes();

        if ($challenge_modes->contains('dns-cloudflare')) {
            $this->build_certbot_dns_cloudflare($recipe);
        }

        if ($challenge_modes->contains('standalone')) {
            $this->build_certbot_standalone($recipe);
        }
    }

    /**
     * @param DockerComposeRecipe $recipe
     *
     * @throws BindingResolutionException
     */
    private function build_certbot_dns_cloudflare(DockerComposeRecipe $recipe): void
    {
        $recipe->add_container(CertbotCloudflare::class, [
            'cloudflare_token' => env('CLOUDFLARE_TOKEN'),
        ]);
    }

    private function build_certbot_standalone(DockerComposeRecipe $recipe): void
    {
        $recipe->add_container(CertbotStandalone::class);
    }

    public function compute_certificate_location(string $hostname): string
    {
        return "/etc/letsencrypt/live/$hostname/fullchain.pem";
    }

    public function compute_certificate_key_location(string $hostname): string
    {
        return "/etc/letsencrypt/live/$hostname/privkey.pem";
    }

    public function reserved_ports(): array
    {
        if ($this->challenge_modes()->contains('standalone')) {
            return [80];
        }

        return [];
    }
}
