<?php

    namespace App\Commands\Docker;

    use App\Services\DockerService;
    use App\Services\TerminalService;
    use Illuminate\Support\Facades\Storage;
    use LaravelZero\Framework\Commands\Command;

    /**
     * Class Shell
     * @package App\Commands
     */
    class Lazydocker extends Command{
        /**
         * The signature of the command.
         *
         * @var string
         */
        protected $signature = 'lazydocker';

        /**
         * The description of the command.
         *
         * @var string
         */
        protected $description = 'Run lazydocker for containers management';

        /**
         * Execute the console command.
         *
         * @param TerminalService $terminal
         * @param DockerService $docker_service
         * @return mixed
         */
        public function handle(TerminalService $terminal){

            $terminal->execute_in_shell_command_line([' "curl https://raw.githubusercontent.com/jesseduffield/lazydocker/master/scripts/install_update_linux.sh | bash"']);

            $terminal->execute_in_shell_command_line(['lazydocker']);

            return true;
        }



    }
