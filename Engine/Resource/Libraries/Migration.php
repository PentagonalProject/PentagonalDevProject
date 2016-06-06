<?php
/**
 * Class CI_Migration
 */
class CI_Migration
{

    /**
     * Whether the library is enabled
     *
     * @var bool
     */
    protected $_migration_enabled = false;

    /**
     * Migration numbering type
     *
     * @var bool
     */
    protected $_migration_type = 'sequential';

    /**
     * Path to migration classes
     *
     * @var string
     */
    protected $_migration_path = null;

    /**
     * Current migration version
     *
     * @var mixed
     */
    protected $_migration_version = 0;

    /**
     * Database table with migration info
     *
     * @var string
     */
    protected $_migration_table = 'migrations';

    /**
     * Whether to automatically run migrations
     *
     * @var bool
     */
    protected $_migration_auto_latest = false;

    /**
     * Migration basename regex
     *
     * @var string
     */
    protected $_migration_regex;

    /**
     * Error message
     *
     * @var string
     */
    protected $_error_string = '';

    /**
     * Initialize Migration Class
     *
     * @param   array   $config
     * @return  void
     */
    public function __construct($config = array())
    {
        // Only run this constructor on main library load
        if (! in_array(get_class($this), array('CI_Migration', config_item('subclass_prefix').'Migration'), true)) {
            return;
        }

        foreach ($config as $key => $val) {
            $this->{'_'.$key} = $val;
        }

        log_message('info', 'Migrations Class Initialized');

        // Are they trying to use migrations while it is disabled?
        if ($this->_migration_enabled !== true) {
            show_error('Migrations has been loaded but is disabled or set up incorrectly.');
        }

        // If not set, set it
        $this->_migration_path !== '' or $this->_migration_path = APPPATH.'migrations/';

        // Add trailing slash if not set
        $this->_migration_path = rtrim($this->_migration_path, '/').'/';

        // They'll probably be using dbforge
        $this->load->dbforge();

        // Make sure the migration table name was set.
        if (empty($this->_migration_table)) {
            show_error('Migrations configuration file (migration.php) must have "migration_table" set.');
        }

        // Migration basename regex
        $this->_migration_regex = ($this->_migration_type === 'timestamp')
            ? '/^\d{14}_(\w+)$/'
            : '/^\d{3}_(\w+)$/';

        // Make sure a valid migration numbering type was set.
        if (! in_array($this->_migration_type, array('sequential', 'timestamp'))) {
            show_error('An invalid migration numbering type was specified: '.$this->_migration_type);
        }

        // If the migrations table is missing, make it
        if (! $this->db->table_exists($this->_migration_table)) {
            $this->dbforge->add_field(array(
                'version' => array('type' => 'BIGINT', 'constraint' => 20),
            ));

            $this->dbforge->create_table($this->_migration_table, true);

            $this->db->insert($this->_migration_table, array('version' => 0));
        }

        // Do we auto migrate to the latest migration?
        if ($this->_migration_auto_latest === true && ! $this->latest()) {
            show_error($this->error_string());
        }
    }

    // --------------------------------------------------------------------

    /**
     * Migrate to a schema version
     *
     * Calls each migration step required to get to the schema version of
     * choice
     *
     * @param   string  $target_version Target schema version
     * @return  mixed   TRUE if no migrations are found, current version string on success, FALSE on failure
     */
    public function version($target_version)
    {
        // Note: We use strings, so that timestamp versions work on 32-bit systems
        $current_version = $this->_get_version();

        if ($this->_migration_type === 'sequential') {
            $target_version = sprintf('%03d', $target_version);
        } else {
            $target_version = (string) $target_version;
        }

        $migrations = $this->find_migrations();

        if ($target_version > 0 && ! isset($migrations[$target_version])) {
            $this->_error_string = sprintf($this->lang->line('No migration could be found with the version number: %s.'), $target_version);
            return false;
        }

        if ($target_version > $current_version) {
            $method = 'up';
        } elseif ($target_version < $current_version) {
            $method = 'down';
            // We need this so that migrations are applied in reverse order
            krsort($migrations);
        } else {
            // Well, there's nothing to migrate then ...
            return true;
        }

        // Validate all available migrations within our target range.
        //
        // Unfortunately, we'll have to use another loop to run them
        // in order to avoid leaving the procedure in a broken state.
        //
        // See https://github.com/bcit-ci/CodeIgniter/issues/4539
        $pending = array();
        foreach ($migrations as $number => $file) {
            // Ignore versions out of our range.
            //
            // Because we've previously sorted the $migrations array depending on the direction,
            // we can safely break the loop once we reach $target_version ...
            if ($method === 'up') {
                if ($number <= $current_version) {
                    continue;
                } elseif ($number > $target_version) {
                    break;
                }
            } else {
                if ($number > $current_version) {
                    continue;
                } elseif ($number <= $target_version) {
                    break;
                }
            }

            // Check for sequence gaps
            if ($this->_migration_type === 'sequential') {
                if (isset($previous) && abs($number - $previous) > 1) {
                    $this->_error_string = sprintf($this->lang->line('There is a gap in the migration sequence near version number: %s.'), $number);
                    return false;
                }

                $previous = $number;
            }

            include_once($file);
            $class = 'Migration_'.ucfirst(strtolower($this->_get_migration_name(basename($file, '.php'))));

            // Validate the migration file structure
            if (! class_exists($class, false)) {
                $this->_error_string = sprintf($this->lang->line('The migration class "%s" could not be found.'), $class);
                return false;
            } elseif (! in_array($method, array_map('strtolower', get_class_methods($class)))) {
                // method_exists() returns true for non-public methods,
                // while is_callable() can't be used without instantiating.
                // Only get_class_methods() satisfies both conditions.
                $this->_error_string = sprintf($this->lang->line('migration_missing_'.$method.'_method'), $class);
                return false;
            }

            $pending[$number] = array($class, $method);
        }

        // Now just run the necessary migrations
        foreach ($pending as $number => $migration) {
            log_message('debug', 'Migrating '.$method.' from version '.$current_version.' to version '.$number);

            $migration[0] = new $migration[0];
            call_user_func($migration);
            $current_version = $number;
            $this->_update_version($current_version);
        }

        // This is necessary when moving down, since the the last migration applied
        // will be the down() method for the next migration up from the target
        if ($current_version <> $target_version) {
            $current_version = $target_version;
            $this->_update_version($current_version);
        }

        log_message('debug', 'Finished migrating to '.$current_version);
        return $current_version;
    }

    // --------------------------------------------------------------------

    /**
     * Sets the schema to the latest migration
     *
     * @return  mixed   Current version string on success, FALSE on failure
     */
    public function latest()
    {
        $migrations = $this->find_migrations();

        if (empty($migrations)) {
            $this->_error_string = $this->lang->line('No migrations were found.');
            return false;
        }

        $last_migration = basename(end($migrations));

        // Calculate the last migration step from existing migration
        // filenames and proceed to the standard version migration
        return $this->version($this->_get_migration_number($last_migration));
    }

    // --------------------------------------------------------------------

    /**
     * Sets the schema to the migration version set in config
     *
     * @return  mixed   TRUE if no migrations are found, current version string on success, FALSE on failure
     */
    public function current()
    {
        return $this->version($this->_migration_version);
    }

    // --------------------------------------------------------------------

    /**
     * Error string
     *
     * @return  string  Error message returned as a string
     */
    public function error_string()
    {
        return $this->_error_string;
    }

    // --------------------------------------------------------------------

    /**
     * Retrieves list of available migration scripts
     *
     * @return  array   list of migration file paths sorted by version
     */
    public function find_migrations()
    {
        $migrations = array();

        // Load all *_*.php files in the migrations path
        foreach (glob($this->_migration_path.'*_*.php') as $file) {
            $name = basename($file, '.php');

            // Filter out non-migration files
            if (preg_match($this->_migration_regex, $name)) {
                $number = $this->_get_migration_number($name);

                // There cannot be duplicate migration numbers
                if (isset($migrations[$number])) {
                    $this->_error_string = sprintf($this->lang->line('There are multiple migrations with the same version number: %s.'), $number);
                    show_error($this->_error_string);
                }

                $migrations[$number] = $file;
            }
        }

        ksort($migrations);
        return $migrations;
    }

    // --------------------------------------------------------------------

    /**
     * Extracts the migration number from a filename
     *
     * @param   string  $migration
     * @return  string  Numeric portion of a migration filename
     */
    protected function _get_migration_number($migration)
    {
        return sscanf($migration, '%[0-9]+', $number)
            ? $number : '0';
    }

    // --------------------------------------------------------------------

    /**
     * Extracts the migration class name from a filename
     *
     * @param   string  $migration
     * @return  string  text portion of a migration filename
     */
    protected function _get_migration_name($migration)
    {
        $parts = explode('_', $migration);
        array_shift($parts);
        return implode('_', $parts);
    }

    // --------------------------------------------------------------------

    /**
     * Retrieves current schema version
     *
     * @return  string  Current migration version
     */
    protected function _get_version()
    {
        $row = $this->db->select('version')->get($this->_migration_table)->row();
        return $row ? $row->version : '0';
    }

    // --------------------------------------------------------------------

    /**
     * Stores the current schema version
     *
     * @param   string  $migration  Migration reached
     * @return  void
     */
    protected function _update_version($migration)
    {
        $this->db->update($this->_migration_table, array(
            'version' => $migration
        ));
    }

    // --------------------------------------------------------------------

    /**
     * Enable the use of CI super-global
     *
     * @param   string  $var
     * @return  mixed
     */
    public function __get($var)
    {
        return get_instance()->$var;
    }
}
