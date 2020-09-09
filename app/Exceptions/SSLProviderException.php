<?php


	namespace App\Exceptions;


	class SSLProviderException extends \Exception{

	    public static function provider_not_found($provider_class){
	        return new self("No SSL provider found with class $provider_class");
        }

	}
