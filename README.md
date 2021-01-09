# Dock: A simple Docker recipes provider

defstudio/dock is an autonomous docker development configurator


## Usage

<details>
   <summary><strong>Requirements</strong></summary>
   
   **dock** is a laravel-zero system packed in a .phar file, that uses docker and docker-compose in order to build a development environment. In order to work properly it needs a working installation of:
   - php (^7.4)
   - docker
   - docker-compose

</details>

<details>
   <summary><strong>Installation</strong></summary>
   
   **dock** does not require installation, simply download the binary file from [here](https://gitlab.com/defstudio/dock/-/raw/master/builds/dock) or type this from your project folder:
   
   ```bash
   wget --no-cache https://github.com/def-studio/dock/releases/latest/download/foo.zip
   ```
</details>

<details>
   <summary><strong>Building an environment</strong></summary>
   
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
</details>


<details>
   <summary><strong>Building and starting docker containers</strong></summary>
   
   after an `.env` file is created (manually or by running the `php dock init` command) the development environment can be bring to life:
   
   ```bash
   php dock start --build
   ```
   
   the process can take quite a few minutes, so stop for a coffe (or better, a beer), it will end up with a confirmation in your terminal:
   
   ![startup completed](https://gitlab.com/defstudio/dock/-/raw/master/docs/images/recipes-start-done.jpg)
   
   your development system is up and running!
</details>



<details>
   <summary><strong>Update <u>dock</u> executable</strong></summary>
   
   **dock** embeds a self update command that checks current version against the last released version and auto updates itself:
   
   ```bash
   php dock self-update
   ```
   
   ![command-self-update](https://gitlab.com/defstudio/dock/-/raw/master/docs/images/commands-self-update.jpg)
</details>

## Commands

**dock** offers a few commands to mantain and manage the development environment. 

Note that additional command can be added by the active recipe, for more information please check each recipe documentation


<details>
   <summary><strong>Show documentation</strong> (<code>php dock</code>)</summary>
   
   by typing `php dock` command a list with all available commands will be displayed
</details>

<details>
   <summary><strong>Init command</strong> (<code>php dock init</code>)</summary>
   
   the initialization wizard can be started at any time with the `php dock init --force` command
   
   note that in order to load the changes the environment should be shut down with the `php dock stop` command
</details>

<details>
   <summary><strong>Log a service</strong> (<code>php dock log</code>)</summary>
   
   with the `php dock log` command, a service selection prompt will be displayed and will let the user choose a service for showing its live log:
   
   ![log](https://gitlab.com/defstudio/dock/-/raw/master/docs/images/commands-log.jpg)
   
   to bypass the prompt, the service name can be given as parameter for the command, es. `php dock log nginx`

</details>

<details>
   <summary><strong>Log all services</strong> (<code>php dock log:all</code>)</summary>
   
   a condensed log for all services can be displayed with the `php dock log:all` command:
   
   ![log all](https://gitlab.com/defstudio/dock/-/raw/master/docs/images/commands-log-all.jpg)
</details>

<details>
   <summary><strong>Log into a service shell</strong> (<code>php dock shell</code>)</summary>
   
   it is useful, sometimes, to log into a specific container, with the `php dock shell` commands it is possible to select the service to log into:
   
   ![shell command](https://gitlab.com/defstudio/dock/-/raw/master/docs/images/commands-shell.jpg)
   
   to bypass the prompt, the service name can be given as parameter for the command, ie. `php dock log php`
</details>

<details>
   <summary><strong>Display active containers statistics</strong> (<code>php dock stats</code>)</summary>
   
   **dock** embeds docker's `stats` command to display containers memory, cpu, i/o data into its own `php dock stats` command
</details>

<details>
   <summary><strong>List running containers</strong> (<code>php dock list:containers</code>)</summary>
   
   with the `php dock list:containers` command, **dock** will display the list of all running containers in the system
   
   ![list containers](https://gitlab.com/defstudio/dock/-/raw/master/docs/images/commands-list-containers.jpg)
</details>

<details>
   <summary><strong>List available hosts</strong> (<code>php dock list:hosts</code>)</summary>
   
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
</details>

<details>
   <summary><strong>List available URLs</strong> (<code>php dock list:urls</code>)</summary>
   
   like the `list:urls` command, with `php dock list:urls` **dock** can display the list of available urls defined during the build process:
   
   ![list containers](https://gitlab.com/defstudio/dock/-/raw/master/docs/images/commands-list-urls.jpg)
</details>



<details>
   <summary><strong>Start a Lazydocker instance</strong> (<code>php dock lazydocker</code>)</summary>
   
   Starts a [Lazydocker](https://github.com/jesseduffield/lazydocker) instance, a simple terminal UI for docker and docker-compose
</details>



## Tips and Tricks

- instead of writing `php dock [command]` you can setup a console alias for dock: `alias dock="php dock"`, so it will be enough to type `dock [command]`

- to make the **dock** alias persistent between reboots, add `alias dock=./dock` at the end of your `~/.bashrc` file


## Acknowledgements

- **dock** is built with, and depends on, the awesome [Laravel Zero](https://laravel-zero.com/) by [Nuno Maduro](https://github.com/nunomaduro)
- **dock** embeds [Jesse Duffield](https://jesseduffield.com/) 's docker management terminal UI: [lazydocker](https://github.com/jesseduffield/lazydocker) 
