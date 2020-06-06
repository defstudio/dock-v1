<?php

    namespace App\Commands\Tools;

    use App\Recipes\DockerComposeRecipe;
    use LaravelZero\Framework\Commands\Command;

    class ListHosts extends Command{
        /**
         * The signature of the command.
         *
         * @var string
         */
        protected $signature = 'list:hosts';

        /**
         * The description of the command.
         *
         * @var string
         */
        protected $description = 'Lists all host defined by current recipe';

        /**
         * Execute the console command.
         *
         * @param DockerComposeRecipe $recipe
         * @return mixed
         */
        public function handle(DockerComposeRecipe $recipe){

            $hosts = $recipe->hosts();

            if(empty($hosts)){
                $this->warn("No host is defined by {$recipe->label()} Recipe");
                return 1;
            }


            $this->info("Hosts defined by {$recipe->label()} Recipe:");

            foreach($hosts as $host){
                $this->info("> $host");
            }

            return 0;

        }

    }
