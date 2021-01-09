<?php


namespace App\Updater;

use Humbug\SelfUpdate\Updater;
use LaravelZero\Framework\Components\Updater\Strategy\StrategyInterface;

final class GithubStrategy extends \Humbug\SelfUpdate\Strategy\GithubStrategy implements StrategyInterface
{
    public function getCurrentLocalVersion(Updater $updater)
    {
        $version = parent::getCurrentLocalVersion($updater);
        dump("Local version: $version");
        return $version;
    }

    public function getCurrentRemoteVersion(Updater $updater)
    {
        $version = parent::getCurrentRemoteVersion($updater);
        dump("Remote version: $version");
        return $version;
    }

    protected function getDownloadUrl(array $package)
    {
        $url = parent::getDownloadUrl($package)."dock";

        dump("Download url: $url");
        return $url;
    }


}
