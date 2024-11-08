<?php


    namespace App\Services;


    use App\Commands\Log\Log;
    use App\Containers\Container;
    use App\Exceptions\DuplicateNetworkException;
    use App\Exceptions\DuplicateServiceException;
    use App\Exceptions\ContainerException;
    use App\Exceptions\DockerServiceNotFoundException;
    use Illuminate\Support\Collection;
    use Illuminate\Support\Facades\Storage;
    use Symfony\Component\Yaml\Yaml;

    class DockerService{


        /** @var Container[] $containers */
        private $containers = [];

        private $networks = [];


        public function __construct(){
            if(!Storage::disk('cwd')->exists('src')){
                Storage::disk('cwd')->makeDirectory('src');
            }

        }

        /**
         * @param string $internal_name
         * @param string $network_name
         * @param string $driver
         *
         * @throws DuplicateNetworkException
         */
        public function add_network(string $internal_name, string $network_name, string $driver){
            if(!empty($this->networks[$internal_name])) throw new DuplicateNetworkException("Duplicate network: " . $internal_name);
            $this->networks[$internal_name] = [
                'name'   => $network_name,
                'driver' => $driver,
            ];
        }

        /**
         * @param string $network_name
         *
         * @throws DuplicateNetworkException
         */
        public function add_external_network(string $network_name){
            if(!empty($this->networks[$network_name])) throw new DuplicateNetworkException("Duplicate network: " . $network_name);
            $this->networks[$network_name] = [
                'external' => true,
            ];
        }

        /**
         * @param Container $container
         *
         * @return Container
         * @throws DuplicateServiceException
         * @throws ContainerException
         */
        public function add_container(Container $container){
            if(!empty($this->containers[$container->service_name()])) throw new DuplicateServiceException("Duplicate service: " . $container->service_name());

            $container->setup($this);
            $this->containers[$container->service_name()] = $container;

            return $container;
        }

        /**
         * @throws ContainerException
         */
        public function publish(){
            return $this->publish_docker_compose();
        }

        /**
         * @throws ContainerException
         */
        private function publish_docker_compose(){
            $docker_compose = [
                'version'  => '3.5',
                'services' => $this->publish_services(),
                'networks' => $this->publish_networks(),
            ];


            $yaml = Yaml::dump($docker_compose, 5);

            $result = Storage::disk('cwd')->put('docker-compose.yml', $yaml);

            return $result;
        }

        /**
         * @return array
         * @throws ContainerException
         */
        private function publish_services(): array{
            $services = [];

            foreach($this->containers as $name => $container){
                $container->publish_assets();
                $services[$name] = $container->get_service_definition();
            }

            return $services;
        }

        private function publish_networks(){
            return $this->networks;
        }


        /**
         * @return Container[]
         */
        public function get_containers(){
            return $this->containers;
        }

        /**
         * @param string $service_name
         *
         * @return Container
         * @throws DockerServiceNotFoundException
         */
        public function service(string $service_name): Container{
            if(empty($this->containers[$service_name])) throw new DockerServiceNotFoundException("Service $service_name not found");

            return $this->containers[$service_name];
        }


    }
