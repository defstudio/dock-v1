<?php


	namespace App\Containers;


	use App\Exceptions\ContainerException;

    class Composer extends Php{
        protected string $service_name = "composer";

        /**
         * Composer constructor.
         * @throws ContainerException
         */
        public function __construct(){
            parent::__construct();
            $this->set_target('composer');
        }

        public function commands(): array{
            return [
                Commands\Composer::class
            ];
        }
    }
