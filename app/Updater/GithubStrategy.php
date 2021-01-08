<?php


namespace App\Updater;


use Humbug\SelfUpdate\Updater;
use LaravelZero\Framework\Components\Updater\Strategy\StrategyInterface;
use Phar;

final class GithubStrategy extends \Humbug\SelfUpdate\Strategy\GithubStrategy implements StrategyInterface
{
    protected string $remote_version;

    /**
     * Returns the Download Url.
     *
     * @param array $package
     *
     * @return string
     */
    protected function getDownloadUrl(array $package): string
    {
        $downloadUrl = parent::getDownloadUrl($package);

        dd($downloadUrl.'/'.$this->remote_version.'/'.basename(Phar::running()));
        return $downloadUrl.'/'.$this->remote_version.'/'.basename(Phar::running());
    }

    public function getCurrentRemoteVersion(Updater $updater)
    {
        $this->remote_version = parent::getCurrentRemoteVersion($updater);
        return $this->remote_version;
    }


}
