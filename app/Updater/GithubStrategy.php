<?php


namespace App\Updater;

use Illuminate\Support\Str;
use LaravelZero\Framework\Components\Updater\Strategy\StrategyInterface;

final class GithubStrategy extends \Humbug\SelfUpdate\Strategy\GithubStrategy implements StrategyInterface
{
    protected function getDownloadUrl(array $package): string
    {
        $url = parent::getDownloadUrl($package);
        return Str::of($url)->append('dock');
    }
}
