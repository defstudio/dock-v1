<?php


namespace App\Recipes\Angular;


use App\Recipes\Angular\Containers\AngularCli;
use App\Recipes\DockerComposeRecipe;
use Illuminate\Console\Command;

class AngularRecipe extends DockerComposeRecipe
{

    const LABEL = 'Angular';

    protected function customize_init(Command $parent_command, string $env_content): string
    {
        $parent_command->warn('Warning: this recipe is intended for development purposes only');

        return $env_content;
    }

    /**
     * @inheritDoc
     */
    public function build()
    {
        $angular_cli = $this->add_container(AngularCli::class);
        $angular_cli->map_port(4200);
    }

    /**
     * @inheritDoc
     */
    protected function recipe_commands(): array
    {
        return [];
    }
}
