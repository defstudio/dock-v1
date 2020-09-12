<?php


    namespace App\Recipes\Wordpress\Containers;


    use App\Containers\Php;
    use Illuminate\Support\Facades\Storage;

    class Wordpress extends Php{

        const UPLOADS_INI_CONF = 'wordpress/uploads.ini';


        public function get_service_definition(): array{

            $service_definition = parent::get_service_definition();

            $service_definition['build']['context'] = 'https://gitlab.com/defstudio/docker/wordpress.git';
            $service_definition['volumes'][] = './configs/' . self::UPLOADS_INI_CONF . ":/usr/local/etc/php/custom.d";


            $service_definition['environment']['WORDPRESS_DB_HOST'] = $service_definition['environment']['WORDPRESS_DB_HOST'] ?? 'db';
            $service_definition['environment']['MYSQL_ROOT_PASSWORD'] = $service_definition['environment']['MYSQL_ROOT_PASSWORD'] ?? 'root';
            $service_definition['environment']['WORDPRESS_DB_NAME'] = $service_definition['environment']['WORDPRESS_DB_NAME'] ?? 'database';
            $service_definition['environment']['WORDPRESS_DB_USER'] = $service_definition['environment']['WORDPRESS_DB_USER'] ?? 'dbuser';
            $service_definition['environment']['WORDPRESS_DB_PASSWORD'] = $service_definition['environment']['WORDPRESS_DB_PASSWORD'] ?? 'dbpassword';
            $service_definition['environment']['WORDPRESS_TABLE_PREFIX'] = $service_definition['environment']['WORDPRESS_TABLE_PREFIX'] ?? 'wp_';

            $service_definition['environment']['PHP_INI_SCAN_DIR'] = '/usr/local/etc/php/custom.d:/usr/local/etc/php/conf.d';

            return $service_definition;
        }

        public function set_db_hostname(string $value = 'db'){
            $this->set_environment('WORDPRESS_DB_HOST', $value);
        }

        public function set_db_root_password(string $value = 'root'){
            $this->set_environment('MYSQL_ROOT_PASSWORD', $value);
        }

        public function set_db_name(string $value = 'database'){
            $this->set_environment('WORDPRESS_DB_NAME', $value);
        }

        public function set_db_user(string $value = 'dbuser'){
            $this->set_environment('WORDPRESS_DB_USER', $value);
        }

        public function set_db_password(string $value = 'dbpassword'){
            $this->set_environment('WORDPRESS_DB_PASSWORD', $value);
        }

        public function set_db_tables_prefix(string $value = 'wp_'){
            $this->set_environment('WORDPRESS_TABLE_PREFIX', $value);
        }

        public function publish_assets(){
           $this->publish_uploads_ini();
        }

        private function publish_uploads_ini(){
            $this->disk()->put(self::UPLOADS_INI_CONF, Storage::get(self::UPLOADS_INI_CONF));
        }

    }
