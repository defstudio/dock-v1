<?php


    namespace App\Containers;



    class MySql extends Container{
        protected $service_name = 'mysql';

        protected $service_definition = [
            'restart'     => 'always',
            'command'     => [
                '--character-set-server=utf8mb4',
                '--collation-server=utf8mb4_unicode_ci',
                '--default-authentication-plugin=mysql_native_password',
            ],
            'image'       => 'mysql:8',
            'environment' => [
                'MYSQL_DATABASE=database',
                'MYSQL_USER=dbuser',
                'MYSQL_PASSWORD=dbpassword',
                'MYSQL_ROOT_PASSWORD=root',
            ],
            'volumes'     => [
                './volumes/mysql/db/:/var/lib/mysql',
            ],
            'expose'      => [3306],
        ];


        public function set_database($name = 'database'){
            $this->set_environment('MYSQL_DATABASE', $name);
        }

        /**
         * @param string $name
         */
        public function set_user($name = 'dbuser'){
            $this->set_environment('MYSQL_USER', $name);
        }

        /**
         * @param string $password
         */
        public function set_password(string $password = 'dbpassword'){
            $this->set_environment('MYSQL_PASSWORD', $password);
        }

        /**
         * @param string $password
         */
        public function set_root_password(string $password = 'root'){
            $this->set_environment('MYSQL_ROOT_PASSWORD', $password);
        }




    }
