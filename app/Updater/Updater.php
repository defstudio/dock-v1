<?php


	namespace App\Updater;

    use Illuminate\Console\OutputStyle;

    class Updater{

        private $old_version;
        private $new_version;

        private $new_version_available;

        public function update(OutputStyle $output){



            $result = $this->perform_update();

            if ($result) {
                $output->success(sprintf('Updated from version %s to %s.', $this->old_version,$this->new_version));
                exit(0);
            } elseif (! $this->new_version) {
                $output->success('There are no stable versions available.');
            } else {
                $output->success('You have the latest version installed.');
            }
        }

        private function perform_update():bool {

            if($this->new_version_available===false || (!is_bool($this->new_version_available) && !$this->has_update())){
                return false;
            }

            $this->backup_phar();
            $this->download_phar();
            $this->replate_phar();

            return true;
        }

        private function backup_phar(){

        }

        private function download_phar(){

        }

        private function replate_phar(){

        }

        private function has_update(){
            $this->new_version_available = $this->check_new_version_available();
            return $this->new_version_available;
        }

        private function check_new_version_available(){
            $this->new_version = $this->get_current_remote_version();
            $this->old_version = $this->get_current_local_version();

            if (!empty($this->new_version) && ($this->new_version !== $this->old_version)) {
                return true;
            }
            return false;
        }

        private function get_current_remote_version():string {
            return "";
        }

        private function get_current_local_version():string{
            return config('app.version');
        }
    }
