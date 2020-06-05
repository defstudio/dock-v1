<?php

    namespace App\Commands\Tools;

    use App\Contracts\DockerComposeRecipe;
    use LaravelZero\Framework\Commands\Command;

    class ListUrls extends Command{
        /**
         * The signature of the command.
         *
         * @var string
         */
        protected $signature = 'list:urls';

        /**
         * The description of the command.
         *
         * @var string
         */
        protected $description = 'List all URLs where current recipe containers can be reached';

        /**
         * Execute the console command.
         *
         * @param DockerComposeRecipe $recipe
         * @return mixed
         */
        public function handle(DockerComposeRecipe $recipe){

            $urls = $recipe->urls();

            if(empty($urls)){
                $this->warn("No URL is defined by {$recipe->label()} Recipe");
                return 1;
            }


            $this->info("URLs defined by {$recipe->label()} Recipe:");

            $rows = [];
            foreach($urls as $label=>$url){
                $rows[] = [$label, $url];
            }

            $this->table(["Description", "URL"], $rows);

            return 0;

        }

    }
