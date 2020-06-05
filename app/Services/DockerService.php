<?php


    namespace App\Services;


    use App\Containers\Container;
    use App\Exceptions\DuplicateNetworkException;
    use App\Exceptions\DuplicateServiceException;
    use App\Exceptions\ContainerException;
    use App\Exceptions\DockerServiceNotFoundException;
    use Illuminate\Support\Collection;
    use Illuminate\Support\Facades\Storage;
    use Symfony\Component\Yaml\Yaml;

    class DockerService{


        /** @var Container[] $services  */
        private $services = [];

        private $networks = [];


        public function __construct(){
            if(!Storage::disk('cwd')->exists('src')) Storage::disk('cwd')->makeDirectory('src');
        }

        /**
         * @param string $internal_name
         * @param string $network_name
         * @param string $driver
         * @throws DuplicateNetworkException
         */
        public function add_network(string $internal_name, string $network_name, string $driver){
            if(!empty($this->networks[$internal_name])) throw new DuplicateNetworkException("Duplicate network: ". $internal_name);
            $this->networks[$internal_name] = [
              'name' => $network_name,
              'driver' => $driver
            ];
        }

        /**
         * @param Container $service
         * @return Container
         * @throws DuplicateServiceException
         * @throws ContainerException
         */
        public function add_container(Container $service){
            if(!empty($this->services[$service->service_name()])) throw new DuplicateServiceException("Duplicate service: ". $service->service_name());

            $service->setup($this);
            $this->services[$service->service_name()] = $service;

            return $service;
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
              'version' => '3.5',
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

            /** @var Container $service */
            foreach($this->services as $name => $service){
                $service->publish_assets();
                $services[$name] = $service->get_service_definition();
            }

            return $services;
        }

        private function publish_networks(){
            return $this->networks;
        }


        /**
         * @return Container[]
         */
        public function get_services(){
            return $this->services;
        }

        /**
         * @param string $service_name
         * @return Container
         * @throws DockerServiceNotFoundException
         */
        public function service(string $service_name): Container{
            if(empty($this->services[$service_name])) throw new DockerServiceNotFoundException("Service $service_name not found");

            return $this->services[$service_name];
        }


    }
