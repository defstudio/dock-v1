<?php


namespace App\Updater;

use Humbug\SelfUpdate\Updater;
use Illuminate\Support\Str;
use LaravelZero\Framework\Components\Updater\Strategy\StrategyInterface;

final class GithubStrategy extends \Humbug\SelfUpdate\Strategy\GithubStrategy implements StrategyInterface
{
    public function getCurrentLocalVersion(Updater $updater): string
    {
        $version = parent::getCurrentLocalVersion($updater);

        echo "Local version: {$version}\n";
        return $version;
    }

    public function getCurrentRemoteVersion(Updater $updater): string
    {
        $version = parent::getCurrentRemoteVersion($updater);
        echo "Remote version: {$version}\n";
        return $version;
    }

    protected function getDownloadUrl(array $package): string
    {
        $url = parent::getDownloadUrl($package);
        return Str::of($url)->append('dock');
    }


}
