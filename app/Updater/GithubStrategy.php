<?php


namespace App\Updater;

use Humbug\SelfUpdate\Exception\JsonParsingException;
use Humbug\SelfUpdate\Updater;
use Humbug\SelfUpdate\VersionParser;
use LaravelZero\Framework\Components\Updater\Strategy\StrategyInterface;
use Str;

final class GithubStrategy extends \Humbug\SelfUpdate\Strategy\GithubStrategy implements StrategyInterface
{
    public function getCurrentLocalVersion(Updater $updater): string
    {
        $version = parent::getCurrentLocalVersion($updater);
        echo "Local version: $version";
        return $version;
    }

    public function getCurrentRemoteVersion(Updater $updater): string
    {
        $version = parent::getCurrentRemoteVersion($updater);
        echo "Remote version: $version";
        return $version;
    }

    protected function getDownloadUrl(array $package): string
    {
        $url = parent::getDownloadUrl($package);
        return Str::of($url)->append('dock');
    }


}
