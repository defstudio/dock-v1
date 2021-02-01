<?php /** @noinspection PhpUnused */

/** @noinspection PhpRedundantCatchClauseInspection */


namespace App\Recipes;


	use App\Containers\Container;
    use App\Exceptions\ContainerException;
    use App\Exceptions\DuplicateServiceException;
    use App\Services\DockerService;
    use App\Traits\InteractsWithEnvContent;
    use Illuminate\Console\Command;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use Illuminate\Contracts\Filesystem\FileNotFoundException;
    use Illuminate\Support\Facades\Storage;

    abstract class DockerComposeRecipe{

        use InteractsWithEnvContent;

	    const LABEL = null;

	    const ENV_FILE_TEMPLATE = null;

        protected DockerService $docker_service;

        /** @var Container[] $services */
        private array $containers = [];

        private array $exposed_hosts = [];

        private array $exposed_addresses = [];


        public function __construct(DockerService $docker_service){
            $this->docker_service = $docker_service;
        }

        /**
         * Initialize the recipe
         * @param Command $parent_command
         * @return int
         */
        public function init(Command $parent_command): int{
            try{
                $env_content = Storage::get('env/'.$this->label());
            } catch(FileNotFoundException $e){
                $parent_command->error('Cannot find this recipe .env file template');
                return 1;
            }

            $env_content = $this->customize_init($parent_command, $env_content);

            Storage::disk('cwd')->put('.env', $env_content);

            return 0;
        }

        protected abstract function customize_init(Command $parent_command, string $env_content): string;

	    public function label(){
	        return static::LABEL;
        }

        /**
         * Compute and builds the recipe
         * @return mixed
         */
	    public abstract function build();

        /**
         * @throws DuplicateServiceException
         * @throws ContainerException
         */
        public function setup(){
            foreach($this->containers as $container){
                $this->docker_service->add_container($container);
            }
        }

        /**
         * Retrieves commands defined by the recipe and by its containers
         * @return string[]
         */
        public function commands(): array{
            $commands = $this->recipe_commands();

            foreach($this->containers as $container){
                foreach($container->commands() as $command){
                    $commands[] = $command;
                }
            }
            return array_unique($commands);
        }

        /**
         * Retrieves commands defined by the recipe
         * @return string[]
         */
        protected abstract function recipe_commands(): array;

        /**
         * Returns hosts exposed by the recipe
         * @return array
         */
        public function hosts(): array{
            return array_unique($this->exposed_hosts);
        }

        /**
         * Returns the reachable urls of the services
         * @return array
         */
        public function urls(): array{
            return $this->exposed_addresses;
        }

        /**
         * Add a container to the recipe
         * @param string $class
         * @param array $arguments
         * @return Container
         * @throws BindingResolutionException
         */
        public function add_container(string $class, array $arguments = []): Container{

            $container = app()->make($class, $arguments);
            $this->containers[] = $container;
            return $container;
        }

        /**
         * Add an host exposed by the recipe
         * @param $hostname
         */
        protected function add_exposed_host($hostname){
            $this->exposed_hosts[] = $hostname;
        }

        /**
         * Add a reachable address for one of the recipe services
         * @param string $label
         * @param string $protocol
         * @param $uri
         * @param $port
         */
        protected function add_exposed_address(string $label, string $protocol, $uri, $port){
            if($port==80||$port==443){
                $port = "";
            }else{
                $port = ":$port";
            }
            $this->exposed_addresses[$label] = "$protocol://{$uri}{$port}";
        }

    }
