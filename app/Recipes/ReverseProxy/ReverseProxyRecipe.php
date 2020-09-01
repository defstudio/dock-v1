<?php


	namespace App\Recipes\ReverseProxy;


	use App\Containers\Nginx;
    use App\Exceptions\ContainerException;
    use App\Exceptions\DuplicateNetworkException;
    use App\Exceptions\DuplicateServiceException;
    use App\Recipes\DockerComposeRecipe;
    use App\Recipes\ReverseProxy\Exceptions\ProxyTargetMissingException;
    use Illuminate\Console\Command;
    use Illuminate\Support\Facades\Storage;

    class ReverseProxyRecipe extends DockerComposeRecipe{

        const LABEL = 'ReverseProxy';

        private $external_networks = [];



		protected function customize_init(Command $parent_command, string $env_content): string{
		    if(!Storage::disk('cwd')->exists('targets.json')){
                Storage::disk('cwd')->put('targets.json', json_encode([[
                    'docker_network' => 'example_network',
                    'docker_hostname' => 'example_nginx_1',
                    'exposed_hostname' => 'example.ktm',
                    'exposed_port' => 80,
                ]], JSON_PRETTY_PRINT));
            }
            return $env_content;
		}

        /**
         * @inheritDoc
         * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
         * @throws ProxyTargetMissingException
         */
		public function build(){
		    /** @var Nginx $nginx */
			$nginx = $this->add_container(Nginx::class);

			$nginx->map_port(80, 80);
			$nginx->map_port(443, 443);

			$nginx->unset_service_definition('working_dir');
			$nginx->unset_php_service();

            $targets_json = Storage::disk('cwd')->get('targets.json');

			$targets = json_decode($targets_json);

			foreach($targets as $target){
			    $this->external_networks[] = $target->docker_network;
                $nginx->add_network($target->docker_network);
                $nginx->add_proxy($target->exposed_hostname, $target->docker_hostname, $target->exposed_port);
            }

		}

        /**
         * @throws ContainerException
         * @throws DuplicateNetworkException
         * @throws DuplicateServiceException
         */
		public function setup(){
            parent::setup();

            foreach(array_unique($this->external_networks) as $network){
                $this->docker_service->add_external_network($network);
            }
        }

        /**
		 * @inheritDoc
		 */
		protected function recipe_commands(): array{
			return [];
		}
	}


