<?php


    namespace App\Containers;


    use App\Exceptions\ContainerException;
    use App\Services\DockerService;
    use App\Services\TerminalService;
    use Illuminate\Contracts\Filesystem\Filesystem;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    abstract class Container{

        protected $service_name;

        protected $service_definition;

        protected $networks = [];

        /**
         * Container constructor.
         * @throws ContainerException
         */
        public function __construct(){
            if(empty($this->service_name)){
                throw new ContainerException("Service name missing for image " . static::class);
            }

            if(empty($this->service_definition)) {
                throw new ContainerException("Service definition missing for image " . static::class);
            }

        }

        /**
         * Returns this service name
         * @param null $new_service_name
         * @return string
         */
        public function service_name($new_service_name = null): string{
            if(!empty($new_service_name)){
                $this->service_name = $new_service_name;
            }

            return $this->service_name;
        }

        /**
         * Assign a network to the container
         * @param $name
         * @noinspection PhpUnused
         * @return Container
         */
        public function add_network($name){
            $this->networks[] = $name;
            return $this;
        }

        /**
         * Set Container user
         * @param string|null $user (default value current_user_id:current_group_id
         * @return Container
         */
        public function set_user_uid(string $user=null){
            if(empty($user)){
                $current_uid = getmyuid();
                $user = "$current_uid:$current_uid";
            }
            $this->service_definition['user'] = $user;

            return $this;

        }

        /**
         * Map an host port to container port
         * @param int $external Port on host system
         * @param int $internal Port on container (default = $esternal)
         * @return Container
         */
        public function map_port(int $external, int $internal=0){

            if(empty($internal)) $internal = $external;

            $this->service_definition['ports'][] = "$external:$internal";

            $this->service_definition['ports'] = array_unique($this->service_definition['ports']);

            return $this;
        }

        /**
         * Sets an environment variable for the container
         * @param $key
         * @param $value
         * @noinspection PhpUnused
         * @return Container
         */
        public function set_environment($key, $value){
            $this->service_definition['environment'][$key] = $value;
            return $this;
        }

        /**
         * Set container dependency
         * @param string $service_name
         * @return $this
         */
        public function depends_on(string $service_name){
            $this->service_definition['depends_on'][] = $service_name;
            $this->service_definition['depends_on'] = array_unique($this->service_definition['depends_on']);
            return $this;
        }


        /**
         * Retrieve and environment value from the container
         * @param string $key
         * @param string|null $default
         * @return string
         */
        public function get_environment(string $key, string $default=null){
            foreach($this->service_definition['environment']??[] as $environment_definition){
                if(Str::startsWith($environment_definition, "$key=")){
                   return Str::replaceFirst($environment_definition, "$key=", "");
                }
            }

            return $default;
        }

        /**
         * @return array
         */
        public function get_service_definition(): array{

            foreach($this->networks as $network){
                $this->service_definition['networks'][] = $network;
            }


            if(!empty($this->service_definition['networks'])){
                $this->service_definition['networks'] = array_unique( $this->service_definition['networks']);
            }

            return $this->service_definition;
        }

        public function publish_assets(){

        }

        public function setup(DockerService $service){

        }

        public function execute(TerminalService $terminal, array $commands, string $input=null){

            $service_command = [
                'docker-compose',
                'run',
                $this->service_name(),
            ];

            $commands = array_merge($service_command, $commands);

            $result = $terminal->execute($commands, $input);

            return $result;
        }

        public function execute_in_shell_command_line(TerminalService $terminal, array $commands){
            $service_command = [
                'docker-compose',
                'exec',
                $this->service_name(),
            ];

            $commands = array_merge($service_command, $commands);

            $result = $terminal->execute_in_shell_command_line($commands);
            return $result;
        }

        /**
         * Returns an array of commands provided by the container
         * @return array
         */
        public function commands(): array{
            return [];
        }


		public function backup(string $backup_folder): bool {
            return true;
		}

		protected function disk(): Filesystem{
            return Storage::disk('configs');
        }

        protected function compile_template(string &$template, array $data){
            foreach($data as $key => $value){
                $template = str_replace("[$key]", $value, $template);
            }
            return $template;
        }


    }
