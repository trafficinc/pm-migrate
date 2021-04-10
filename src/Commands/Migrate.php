<?php

namespace App\Commands;

use App\Config\Config;


class Migrate {

    public $conf;
    protected $skip_errors = false;
    protected $link;
    protected $version = 0;
    protected $migrations_dir;

    private $version_file = '.version';
    private $file_prefix = 'migrate-';
    private $file_postfix = '.php';
    private $debug = false;

    public function __construct(Config $config) {
        $this->conf = $config->getConfig();
        $baseDir = dirname(dirname(__DIR__));
        $this->migrations_dir = $baseDir . DIRECTORY_SEPARATOR . $this->conf['MIGRATIONS_DIR'];
        $this->version_file = $baseDir . DIRECTORY_SEPARATOR . '.version';
        $this->dbconnection();
    }

    private function dbconnection() {
        $this->link = mysqli_connect($this->conf['DB_HOST'].':'.$this->conf['DB_PORT'], $this->conf['DB_USERNAME'], $this->conf['DB_PASSWORD'], $this->conf['DB_DATABASE']);
        if (!$this->link) {
            echo "Failed to connect to the database.\n";
            exit;
        }
        mysqli_query($this->link, "SET NAMES 'utf8'");
    }

    public function check() {
        // Find the latest version or start at 0.
        $this->version = 0;
        $f = @fopen($this->version_file, 'r');
        if ($f) {
            $this->version = intval(fgets($f));
            fclose($f);
        }
    }

    public function version() {
        $this->check();
        echo "Current database version is: $this->version\n";

    }

    public function add($argv) {
        if (!isset($argv[2]) && empty($argv[2])) {
            echo "You need a migration name. [ migrate:add NAME ] \n";
            exit();
        }
        $this->check();
        $migration_name = $argv[2];
        $new_version = $this->version;

        // Check the new version against existing migrations.
        $files = $this->get_migrations();
        $last_file = end($files);
        if ($last_file !== false) {
            $file_version = $this->get_version_from_file($last_file);
            if ($file_version > $new_version) {
                $new_version = $file_version;
            }
        }

        // Create migration file path.
        $new_version = $new_version += 1;
        $path = $this->migrations_dir . $this->file_prefix . sprintf('%04d', $new_version);
        if (@strlen($migration_name)) {
            $path .= '-' . str_replace(' ', '-', $migration_name);
        }
        $path .= $this->file_postfix;

        echo "Adding a new migration script: $path\n";

        $f = @fopen($path, 'w');
        if ($f) {
            fputs($f, "<?php\n\nuse App\Config\Config;\nuse App\Commands\Migrate;\n\n\$query=\"\";\n\n(new Migrate(new Config()))->query(\$query);\n\n");
            fclose($f);
            echo "Done.\n";
        } else {
            echo "Failed.\n";
        }
    }

    public function migrate($argv) {
        $this->check();

        $files = $this->get_migrations();

        $skip_errors = (isset($argv[2]) && $argv[2] === '--skip-errors') ? '--skip-errors' : '';

        // Check to make sure there are no conflicts such as 2 files under the same version.
        $errors = [];
        $last_file = false;
        $last_version = false;
        foreach ($files as $file) {
            $file_version = $this->get_version_from_file($file);
            if ($last_version !== false && $last_version === $file_version) {
                $errors[] = "$last_file --- $file";
            }
            $last_version = $file_version;
            $last_file = $file;
        }
        if (count($errors) > 0) {
            echo "Error: You have multiple files using the same version. " .
                "To resolve, move some of the files up so each one gets a unique version.\n";
            foreach ($errors as $error) {
                echo "  $error\n";
            }
            exit;
        }

        // Run all the new files.
        $found_new = false;
        foreach ($files as $file) {
            $file_version = $this->get_version_from_file($file);
            if ($file_version <= $this->version) {
                continue;
            }
            echo "Running: $file\n";
            $this->query('BEGIN');
            include $this->migrations_dir . $file;
            $this->query('COMMIT');
            echo "Done.\n";

            $version = $file_version;
            $found_new = true;

            // Output the new version number.
            $f = @fopen($this->version_file, 'w');
            if ($f) {
                fputs($f, $version);
                fclose($f);
            } else {
                echo "Failed to output new version to " . $this->version_file . "\n";
            }
        }

        if ($found_new) {
            echo "Migration complete.\n";
        } else {
            echo "Your database is up-to-date.\n";
        }



    }

    public function query($query) {
        echo "Query: $query\n";

        $result = mysqli_query($this->link, $query);
        if (!$result) {
            if ($this->skip_errors) {
                echo "Query failed: " . mysqli_error($this->link) . "\n";
            } else {
                echo "Migration failed: " . mysqli_error($this->link) . "\n";
                echo "Aborting.\n";
                mysqli_query($this->link, 'ROLLBACK');
                mysqli_close($this->link);
                exit;
            }
        }
        return $result;
    }

    function get_migrations()
    {
        // Find all the migration files in the directory and return the sorted.
        $files = array();
        $dir = opendir($this->migrations_dir);
        while ($file = readdir($dir)) {
            if (substr($file, 0, strlen($this->file_prefix)) == $this->file_prefix) {
                $files[] = $file;
            }
        }
        asort($files);
        return $files;
    }

    function get_version_from_file($file)
    {
        return intval(substr($file, strlen($this->file_prefix)));
    }





}