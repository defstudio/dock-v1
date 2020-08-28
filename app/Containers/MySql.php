<?php


    namespace App\Containers;


    use App\Services\TerminalService;
    use Illuminate\Contracts\Container\BindingResolutionException;

    class MySql extends Container{

        const HOST_DB_VOLUME_PATH = './volumes/mysql/db/';

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
                'MYSQL_DATABASE'      => 'database',
                'MYSQL_USER'          => 'dbuser',
                'MYSQL_PASSWORD'      => 'dbpassword',
                'MYSQL_ROOT_PASSWORD' => 'root',
            ],
            'expose'      => [3306],
        ];

        protected $volumes = [
            self::HOST_DB_VOLUME_PATH => '/var/lib/mysql',
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

        /**
         * @param $backup_folder
         *
         * @return bool
         * @throws BindingResolutionException
         */
        public function backup($backup_folder): bool{
            /** @var TerminalService $terminal */
            $terminal = app()->make(TerminalService::class);

            // $this->execute($terminal, [
            //    'echo',
            //     '"[client]\nuser=root\npassword='.env('MYSQL_ROOT_PASSWORD').'"',
            //     '>',
            //     '~/.my.cnf',
            // ]);

            $result = $this->execute_in_shell_command_line($terminal, [
                "mysqldump",
                env('MYSQL_DATABASE'),
                '>',
                'backup.sql',
            ]);


            if($result == 0){
                return true;
            } else{
                return false;
            }

        }


    }
