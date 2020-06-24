<?php


    namespace App\Containers;


    use App\Services\TerminalService;
    use Illuminate\Contracts\Container\BindingResolutionException;
    
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

        /**
         * @param $backup_folder
         * @return bool
         * @throws BindingResolutionException
         */
        public function backup($backup_folder): bool{
            /** @var TerminalService $terminal */
            $terminal = app()->make(TerminalService::class);

            $backup_file = config('filesystems.disks.backup.root')."/$backup_folder/mysql.sql";

            $result = $this->execute_in_shell_command_line($terminal, [
                "mysqldump",
                "-uroot",
                "-proot",
                env('MYSQL_DATABASE'),
                '>',
                $backup_file,
            ]);

            //Elimina la prima riga del file di backup, quella
            //che conterrà il messaggio di errore per la password
            //usata in command line
            $firstline = false;
            if($handle = fopen($backup_file,'c+')){
                if(!flock($handle,LOCK_EX)){fclose($handle);}
                $offset = 0;
                $len = filesize($backup_file);
                while(($line = fgets($handle,4096)) !== false){
                    if(!$firstline ){
                        $firstline = $line;
                        $offset = strlen($firstline);
                        continue;
                    }
                    $pos = ftell($handle);
                    fseek($handle,$pos-strlen($line)-$offset);
                    fputs($handle,$line);
                    fseek($handle,$pos);
                }
                fflush($handle);
                ftruncate($handle,($len-$offset));
                flock($handle,LOCK_UN);
                fclose($handle);
            }


            if($result==0){
                return true;
            }else{
                return false;
            }

        }


    }
