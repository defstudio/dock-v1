<?php


    namespace App\Containers;


    use App\Exceptions\ContainerException;
    use Illuminate\Contracts\Filesystem\Filesystem;
    use Illuminate\Support\Facades\Storage;

    class
    Php extends Container{

        protected $service_name = "php";


        protected $service_definition = [
            'restart'     => 'unless-stopped',
            'working_dir' => '/var/www',
            'build'       => [
                'context' => 'git@gitlab.com/defstudio/docker/php.git',
                'args' => ['ENABLE_XDEBUG=1']
            ],
            'expose' => [9000],
        ];

        protected $volumes = [
          self::HOST_SRC_VOLUME_PATH => '/var/www'
        ];

        /**
         * Php constructor.
         * @throws ContainerException
         */
        public function __construct(){
            parent::__construct();
            $this->set_user_uid();
        }

    }
