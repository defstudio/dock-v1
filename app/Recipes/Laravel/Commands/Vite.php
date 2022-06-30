<?php


    namespace App\Recipes\Laravel\Commands;

    use App\Exceptions\DockerServiceNotFoundException;
    use App\Services\DockerService;
    use App\Services\TerminalService;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use LaravelZero\Framework\Commands\Command;

    class Vite extends Command{
        protected $signature = 'vite';

        protected $description = 'Launch vite server';

        /**
         * @param DockerService $docker_service
         * @param TerminalService $terminal
         * @return int
         * @throws DockerServiceNotFoundException
         * @throws BindingResolutionException
         */
        public function handle(DockerService $docker_service, TerminalService $terminal){

            $terminal->init($this->output);
            $cwd = getcwd();
            $commands = [
                "docker",
                "run",
                "--rm",
                "--workdir=/var/www",
                '--volume=$(pwd)/src:/var/www',
                '--publish=127.0.0.1:3000:3000',
                'defstudio/node:alpine-lts',
                'npm run dev',
            ];

            dump(implode(" ", $commands));
            return $terminal->execute($commands);
        }
    }
