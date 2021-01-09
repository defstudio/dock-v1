<?php


	namespace App\Recipes\Laravel\Commands;


	use App\Exceptions\DockerServiceNotFoundException;
    use App\Services\DockerService;
    use App\Services\TerminalService;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use LaravelZero\Framework\Commands\Command;

    class Install extends Command{
        protected $signature = 'laravel:install';

        protected $description = 'Installs laravel';

        /**
         * @param DockerService $docker_service
         * @param TerminalService $terminal
         * @return int
         * @throws DockerServiceNotFoundException
         * @throws BindingResolutionException
         */
        public function handle(DockerService $docker_service, TerminalService $terminal){

            $terminal->init($this->output);


            if(!$this->task("Laravel Installation", function()use($docker_service, $terminal){
                return $docker_service->service('composer')->run($terminal, [
                    "composer",
                    "create-project",
                    '--prefer-dist',
                    'laravel/laravel',
                    '.'
                ]);
            })) return 1;



            $init_command = app()->make(Init::class, []);
            $init_command->setOutput($this->output);
            $init_command->handle($docker_service, $terminal);

            return 0;
        }
	}
