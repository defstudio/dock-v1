<?php

    namespace App\Commands;

     use App\Recipes\DockerComposeRecipe;
    use App\Services\DockerService;
    use App\Services\TerminalService;
    use Illuminate\Support\Facades\Storage;
    use LaravelZero\Framework\Commands\Command;

    /**
     * Class Init
     * @package App\Commands
     */
    class Init extends Command{

        protected $signature = 'init
                                {--force : force overwriting current configuration}';
        protected $description = 'Initialize a new recipe';


        /**
         * Execute the console command.
         *
         * @param DockerService $docker_service
         * @param TerminalService $terminal
         * @return mixed
         */
        public function handle(DockerService $docker_service, TerminalService $terminal){
            $terminal->init($this->output);

            if(Storage::disk('cwd')->exists('.env')){
                if(!$this->option('force')){
                    $this->warn('A .env configuration file exists for this project. run "init --force" command to overwrite it');
                    return 1;
                }

                if(!$this->confirm("This command will overwrite your .env file. Continue?")){
                    return 1;
                }

                $this->task("Making a backup copy of current .env file", function(){
                    Storage::disk('cwd')->delete('.env.backup');
                    Storage::disk('cwd')->move('.env', '.env.backup');
                });
            }


            $recipes = config('recipes', []);

            $recipes_labels = array_keys($recipes);

            $recipes_label = $this->choice("Select a recipe:", $recipes_labels);

            $recipe_class = $recipes[$recipes_label];

            /** @var DockerComposeRecipe $recipe */
            $recipe = $this->app->make($recipe_class);


            return $recipe->init($this);


        }

    }
