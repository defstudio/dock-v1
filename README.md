# Dock: A simple Docker recipes provider

defstudio/dock is an autonomous docker development configurator


## Usage

#### Requirements

**dock** is a laravel-zero system packed in a .phar file, that uses docker and docker-compose in order to build a development environment. In order to work properly it needs a working installation of:
- php (^7.3)
- docker
- docker-compose



#### Installation 

**dock** does not require installation, simply download the binary file from [here](https://gitlab.com/defstudio/dock/-/raw/master/builds/dock) or type this from your project folder:

```bash
wget https://gitlab.com/defstudio/dock/-/raw/master/builds/dock
```



#### Building an environment

in order to build a development environment, **dock** uses a "recipe" system that configure the proper services for the development target.

the first step is .env file initialization:

```bash
php dock init
```

the system will let you choose a list of recipes:

![recipes-prompt](https://gitlab.com/defstudio/dock/-/raw/master/docs/images/recipes-prompt.jpg)

and a configuration wizard will start, if available:

![recipe wizard](https://gitlab.com/defstudio/dock/-/raw/master/docs/images/recipes-wizard.jpg)

the process will end up with an `env` file and a `src` folder in your project root:

```
Project Root
|-- dock
|-- .env
|-- src/
```



#### Building and starting docker images

after an `.env` file is created (manually or by running the `php dock init` command) the development environment can be bring to life:

```bash
php dock start --build
```

the process can take quite a few minutes, so stop for a coffe (or better, a beer), it will end up with a confirmation in your terminal:

![startup completed](https://gitlab.com/defstudio/dock/-/raw/master/docs/images/recipes-start-done.jpg)

your development system is up and running!



#### Update **dock** executable

**dock** embeds a self update command that checks current version against the last released version and auto updates itself:

```bash
php dock self-update
```

![command-self-update](https://gitlab.com/defstudio/dock/-/raw/master/docs/images/commands-self-update.jpg)



## Commands

**dock** offers a few commands to mantain and manage the development environment. 

Note that additional command can be added by the active recipe, for more information please check each recipe documentation


<details>
    <summary>Show documentation (`php dock`)</summary>
    by typing `php dock` command a list with all available commands will be displayed
</details>





#### Init command (`php dock init`)

the initialization wizard can be started at any time with the `php dock init --force` command

note that in order to load the changes the environment should be shut down with the `php dock stop` command



#### Log a service (`php dock log`)

with the `php dock log` command, a service selection prompt will be displayed and will let the user choose a service for showing its live log:

![log](https://gitlab.com/defstudio/dock/-/raw/master/docs/images/commands-log.jpg)

to bypass the prompt, the service name can be given as parameter for the command, es. `php dock log nginx`



#### Log all services (`php dock log:all`)

a condensed log for all services can be displayed with the `php dock log:all` command:

![log all](https://gitlab.com/defstudio/dock/-/raw/master/docs/images/commands-log-all.jpg)



#### Log into a service shell (`php dock shell`)

it is useful, sometimes, to log into a specific container, with the `php dock shell` commands it is possible to select the service to log into:

![shell command](https://gitlab.com/defstudio/dock/-/raw/master/docs/images/commands-shell.jpg)

to bypass the prompt, the service name can be given as parameter for the command, ie. `php dock log php`


#### Display active containers statistics (`php dock stats`)

**dock** embeds docker's `stats` command to display containers memory, cpu, i/o data into its own `php dock stats` command


### List running containers (`php dock list:containers`)

with the `php dock list:containers` command, **dock** will display the list of all running containers in the system

![list containers](https://gitlab.com/defstudio/dock/-/raw/master/docs/images/commands-list-containers.jpg)


#### List available hosts (`php dock list:hosts`)

usually you will bind your services to a custom hostname, in order to simplify the addressing during development.

this means that the OS _hosts_ file should be updated to include the mapping between these hostnames and the local ip address.

to obtain a list of the hostnames defined by the build process, type `php dock list:hosts` in your terminal:

![list containers](https://gitlab.com/defstudio/dock/-/raw/master/docs/images/commands-list-hosts.jpg)

in this example, you should append this entries to your _hosts_ file

```
127.0.0.1         laravel.ktm
127.0.0.1         mysql.laravel.ktm
127.0.0.1         mail.laravel.ktm
```

#### List available URLs (`php dock list:urls`)

like the `list:urls` command, with `php dock list:urls` **dock** can display the list of available urls defined during the build process:

![list containers](https://gitlab.com/defstudio/dock/-/raw/master/docs/images/commands-list-urls.jpg)



## Tips and Tricks

- instead of writing `php dock [command]` you can run directly the **dock** file by making it executable (`chmod +x dock` in your terminal), this way you can execute command with `./dock [command]`

- a simpler way to run command is by creating a console alias for dock: `alias dock=./dock`, so it will be enough to type `dock [command]`

- to make the **dock** alias persistent between reboots, add `alias dock=./dock` at the end of your `~/.bashrc` file


## Acknowledgements

**dock** is built with, and depends on, the awesome [Laravel Zero](https://laravel-zero.com/) by [Nuno Maduro](https://github.com/nunomaduro) 
