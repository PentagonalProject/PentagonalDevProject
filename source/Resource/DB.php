<?php
/**
 * Initialize the database
 *
 * @category	Database
 * @author	EllisLab Dev Team
 * @link	https://codeigniter.com/user_guide/database/
 *
 * @param 	string|string[]	$params
 * @param 	bool		$query_builder_override
 *				Determines if query builder should be used or not
 * @return object
 */
function &DB($params = '', $query_builder_override = NULL)
{
    // Load the DB config file if a DSN string wasn't passed
    if (is_string($params) && strpos($params, '://') === false) {
        $db = Processor::config('db');

        if (! isset($db) || empty($db) || !is_array($db) || count($db) === 0) {
            show_error('No database connection settings were found in the database config file.');
        }

        $active_group = config_item('select_db');
        if (!$active_group || is_string($active_group)) {
            $active_group = is_string($active_group) && trim($active_group) !== ''
                ? $active_group
                : 'default';
            if (isset($db[$active_group])) {
                $tmp_db = $db[$active_group];
                $db[$active_group] = $tmp_db;
            } elseif (isset($db['dsn']) && isset($db['dbdriver'])) {
                /**
                 * change back to traditional mode
                 */
                $tmp_db = $db;
                $db = array();
                $db[$active_group] = $tmp_db;
            }
        }

        if ($params !== '') {
            $active_group = $params;
        }

        if ( ! isset($active_group)) {
            show_error('You have not specified a database connection group via $active_group in your configuration.');
        } elseif (! isset($db[$active_group])) {
            show_error('You have specified an invalid database connection group ('.$active_group.') in your configuration.');
        } elseif (! is_array($db[$active_group]) || empty($db[$active_group])) {
            show_error('You have specified an invalid database connection group ('.$active_group.') in your configuration.');
        }
        /** @noinspection PhpIncludeInspection */
        $params = $db[$active_group];
    } elseif (is_string($params)) {
        /**
         * Parse the URL from the DSN string
         * Database settings can be passed as discreet
         * parameters or as a data source name in the first
         * parameter. DSNs must have this prototype:
         * $dsn = 'driver://username:password@hostname/database';
         */
        if (($dsn = @parse_url($params)) === false) {
            show_error('Invalid DB Connection String');
        }

        $params = array(
            'dbdriver'	=> $dsn['scheme'],
            'hostname'	=> isset($dsn['host']) ? rawurldecode($dsn['host']) : '',
            'port'		=> isset($dsn['port']) ? rawurldecode($dsn['port']) : '',
            'username'	=> isset($dsn['user']) ? rawurldecode($dsn['user']) : '',
            'password'	=> isset($dsn['pass']) ? rawurldecode($dsn['pass']) : '',
            'database'	=> isset($dsn['path']) ? rawurldecode(substr($dsn['path'], 1)) : ''
        );

        // Were additional config items set?
        if (isset($dsn['query'])) {
            parse_str($dsn['query'], $extra);

            foreach ($extra as $key => $val) {
                if (is_string($val) && in_array(strtolower($val), array('true', 'false', 'null'))) {
                    $val = var_export($val, true);
                }

                $params[$key] = $val;
            }
        }
    }

    // No DB specified yet? Beat them senseless...
    if (empty($params['dbdriver'])) {
        show_error('You have not selected a database type to connect to.');
    }

    // Load the DB classes. Note: Since the query builder class is optional
    // we need to dynamically create a class that extends proper parent class
    // based on whether we're using the query builder class or not.
    if ($query_builder_override !== null)
    {
        $query_builder = $query_builder_override;
    }
    // Backwards compatibility work-around for keeping the
    // $active_record config variable working. Should be
    // removed in v3.1
    elseif ( ! isset($query_builder) && isset($active_record))
    {
        $query_builder = $active_record;
    }

    require_once(BASEPATH.'database/DB_driver.php');

    if ( ! isset($query_builder) || $query_builder === true) {
        require_once(BASEPATH.'database/DB_query_builder.php');
        if ( ! class_exists('CI_DB', false)) {
            /**
             * CI_DB
             *
             * Acts as an alias for both CI_DB_driver and CI_DB_query_builder.
             *
             * @see	CI_DB_query_builder
             * @see	CI_DB_driver
             */
            class CI_DB extends CI_DB_query_builder { }
        }
    } elseif ( ! class_exists('CI_DB', false)) {
        /**
         * @ignore
         */
        class CI_DB extends CI_DB_driver { }
    }

    // Load the DB driver
    $driver_file = BASEPATH.'database/drivers/'.$params['dbdriver'].'/'.$params['dbdriver'].'_driver.php';

    file_exists($driver_file) OR show_error('Invalid DB driver');
    require_once($driver_file);

    // Instantiate the DB adapter
    $driver = 'CI_DB_'.$params['dbdriver'].'_driver';
    $DB = new $driver($params);

    // Check for a subdriver
    if ( ! empty($DB->subdriver))
    {
        $driver_file = BASEPATH.'database/drivers/'.$DB->dbdriver.'/subdrivers/'.$DB->dbdriver.'_'.$DB->subdriver.'_driver.php';

        if (file_exists($driver_file))
        {
            require_once($driver_file);
            $driver = 'CI_DB_'.$DB->dbdriver.'_'.$DB->subdriver.'_driver';
            $DB = new $driver($params);
        }
    }

    $DB->initialize();
    return $DB;
}
