<?php


	namespace App\Traits;


	use Illuminate\Support\Str;

    trait InteractsWithEnvContent{

        protected function comment_env(string &$env_content, string $key){
            $env_content = preg_replace("/^$key=/m", "#$key=", $env_content);
        }

        protected function uncomment_env(string &$env_content, string $key){
            $env_content = preg_replace("/^#$key=/m", "$key=", $env_content);
        }

        protected function set_env(string &$env_content, string $key, $value){
            $this->uncomment_env($env_content, $key);
            if(Str::contains($env_content, $key)){
                $env_content = preg_replace("/^$key=.*\$/m", "$key=$value", $env_content);
            }else{
                $env_content .= "\n$key=$value";
            }

        }

        protected function get_env(string $env_content, string $key){
            $matches=[];
            if(preg_match("/^$key=(.*)\$/m", $env_content, $matches)){
                return $matches[1];
            }else{
                return "";
            }
        }
	}
