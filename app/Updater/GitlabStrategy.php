<?php

    namespace App\Updater;

    use Humbug\SelfUpdate\Updater;
    use LaravelZero\Framework\Components\Updater\Strategy\StrategyInterface;

    final class GitlabStrategy implements StrategyInterface{

        const API_URL = 'https://gitlab.com/defstudio/dock';

        private $package_name;

        private $local_version;

        private $remote_version;

        private $phar_name;


        public function get_download_url(){

        }

        public function download(Updater $updater){
            // TODO: Implement download() method.
        }

        public function getCurrentRemoteVersion(Updater $updater){
            // TODO: Implement getCurrentRemoteVersion() method.
        }

        public function getCurrentLocalVersion(Updater $updater){
            $this->local_version;
        }

        public function setCurrentLocalVersion($version){
            $this->local_version = $version;
        }

        public function setPharName($name){
            $this->phar_name = $name;
        }

        public function setPackageName($name){
            $this->package_name = $name;
        }

    }
