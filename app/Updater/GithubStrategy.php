<?php


namespace App\Updater;

use Humbug\SelfUpdate\Exception\JsonParsingException;
use Humbug\SelfUpdate\Updater;
use Humbug\SelfUpdate\VersionParser;
use Illuminate\Support\Str;
use LaravelZero\Framework\Components\Updater\Strategy\StrategyInterface;

final class GithubStrategy extends \Humbug\SelfUpdate\Strategy\GithubStrategy implements StrategyInterface
{
    public function getCurrentRemoteVersion(Updater $updater)
    {
        /** Switch remote request errors to HttpRequestExceptions */
        set_error_handler(array($updater, 'throwHttpRequestException'));
        $packageUrl = $this->getApiUrl();
        $package = json_decode(humbug_get_contents($packageUrl), true);
        restore_error_handler();

        if (null === $package || json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonParsingException(
                'Error parsing JSON package data'
                . (function_exists('json_last_error_msg') ? ': ' . json_last_error_msg() : '')
            );
        }

        $versions = array_keys($package['packages'][$this->getPackageName()]);
        $versionParser = new VersionParser($versions);
        if ($this->getStability() === self::STABLE) {
            echo 'remote version: ' .  $versionParser->getMostRecentStable() . PHP_EOL. PHP_EOL;
        } elseif ($this->getStability() === self::UNSTABLE) {
            echo 'remote version: ' . $versionParser->getMostRecentUnstable() . PHP_EOL. PHP_EOL;
        } else {
            echo 'remote version: ' . $versionParser->getMostRecentAll() . PHP_EOL. PHP_EOL;
        }

        return parent::getCurrentRemoteVersion($updater);
    }

    protected function getDownloadUrl(array $package): string
    {
        $url = parent::getDownloadUrl($package);
        return Str::of($url)->append('dock');
    }
}
