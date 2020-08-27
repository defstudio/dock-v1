# Dock: simple docker recipes provider

defstudio/dock is an autonomous docker development configurator


## Usage

#### Requirements

`dock` is a laravel-zero system packed in a .phar file, that uses docker and docker-compose in order to build a development environment. In order to work properly it needs a working installation of:
- php (^7.3)
- docker
- docker-compose

#### Installation 

`dock` does not require installation, simply download the binary file from [here](https://gitlab.com/defstudio/dock/-/raw/master/builds/dock) or type this from your project folder:

`wget https://gitlab.com/defstudio/dock/-/raw/master/builds/dock`

#### Building an environment

in order to build a development environment, `dock` uses a "recipe" system that configure the proper services for the development target.

the first step is .env file initialization:

`php dock init`

the system will let you choose a list of recipes:

<img src="https://gitlab.com/defstudio/dock/-/raw/master/docs/images/recipes-prompt.jpg" alt="recipe prompt">

and a configuration wizard will start, if available:

<img src="https://gitlab.com/defstudio/dock/-/raw/master/docs/images/recipes-wizard.jpg" alt="recipe wizard">

the process will end up with an `env` file and a `src` folder in your project root:

 ```
Project Root
|-- dock
|-- .env
|-- src/
```

#### Building and starting docker images

after an `.env` file is created (manually or by running the `php dock init` command) the development environment can be bring to life:

`php dock start --build`

the process can take quite a few minutes, so stop for a coffe (or better, a beer), it will end up with a confirmation in your terminal:

<img src="https://gitlab.com/defstudio/dock/-/raw/master/docs/images/recipes-start-done.jpg" alt="startup completed">

your development system is up and running!

## Commands

`dock` offers a few commands to mantain and manage the development environment. 

Note that additional command can be added by the active recipe, for more information please check each recipe documentation

#### Show documentation (`php dock`)

by typing `php dock` command a list with all available commands will be displayed

#### Init command (`php dock init`)

the initialization wizard can be started at any time with the `php dock init --force` command

note that in order to load the changes the environment should be shut down with the `php dock stop` command

#### Log a service (`php dock log`)

with the `php dock log` command, a service selection prompt will be displayed and will let the user choose a service for showing its live log:

<img src="https://gitlab.com/defstudio/dock/-/raw/master/docs/images/commands-log.jpg" alt="log">

to bypass the prompt, the service name can be given as parameter for the command es. `php dock log nginx`

### Log all services (`php dock log:all`)

a condensed log for all services can be displayed with the `php dock log:all` command:



## Tips and Tricks

- instead of writing `php dock [command]` you can run directly the `dock` file by making it executable (`chmod +x dock` in your terminal), this way you can execute command with `./dock [command]`

- a simpler way to run command is by creating a console alias for dock: `alias dock=./dock`, so it will be enough to type `dock [command]`

- to make the `dock` alias persistent between reboots, add `alias dock=./dock` at the end of your `~/.bashrc` file



