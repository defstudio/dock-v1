<?php

    namespace App\Commands;

    use App\Updater\Updater;
    use Illuminate\Console\Scheduling\Schedule;
    use LaravelZero\Framework\Commands\Command;

    class Update extends Command{
        /**
         * The signature of the command.
         *
         * @var string
         */
        protected $signature = 'app:update';

        /**
         * The description of the command.
         *
         * @var string
         */
        protected $description = 'Self-updates dock app';

        /**
         * Execute the console command.
         *
         * @param Updater $updater
         * @return mixed
         */
        public function handle(Updater $updater){
            $this->output->title('Checking for a new version..');

            $result = $updater->update($this->output);

            return 0;
        }

        /**
         * Define the command's schedule.
         *
         * @param \Illuminate\Console\Scheduling\Schedule $schedule
         * @return void
         */
        public function schedule(Schedule $schedule): void{
            $schedule->command(static::class)->daily();
        }
    }
