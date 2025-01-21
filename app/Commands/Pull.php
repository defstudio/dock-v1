<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace App\Commands;

    use App\Services\TerminalService;
    use Illuminate\Support\Stringable;
    use LaravelZero\Framework\Commands\Command;

    class Pull extends Command{
        /**
         * The signature of the command.
         *
         * @var string
         */
        protected $signature = 'pull';

        /**
         * The description of the command.
         *
         * @var string
         */
        protected $description = 'pull latest version of services images';

        /**
         * Execute the console command.
         *
         * @param TerminalService $terminal
         * @return mixed
         */
        public function handle(TerminalService $terminal){
            $terminal->init($this->output);

            $this->info('Pulling updated images...');

            $exit_code = $terminal->execute([
                ...((new Stringable(env('DOCKER_COMPOSE_COMMAND', 'docker compose')))
                    ->explode(' ')
                    ->toArray()),
                'pull'
            ]);

            if($exit_code==0){
                $this->info('Done');
            }

            return $exit_code;
        }
    }
