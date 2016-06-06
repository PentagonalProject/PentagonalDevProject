<?php
namespace {
    /**
     * if does not determine root
     */
    if (!defined('ROOT')) {
        return;
    }
    define('CI_VERSION', '3.0.6');
    /**
     * if not our script
     */
    if (isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) != realpath(ROOT)) {
        header('HTTP/1.1 503 Service Unavailable.', true, 503);
        echo 'Invalid File Requested.';
        exit(1);
    }

    // on cli remove arguments
    if (!(PHP_SAPI === 'cli' || defined('STDIN'))) {
        unset($argv, $argc);
    }

    // Set the current directory correctly for CLI requests
    if (defined('STDIN')) {
        chdir(dirname(ROOT));
    }
    if (empty($GLOBALS) || !is_array($GLOBALS)) {
        header('HTTP/1.1 503 Service Unavailable.', true, 503);
        echo 'GLOBALS variable has been override';
        exit(3); // EXIT_CONFIG
    }

    /**
     * Reset Globals for security Reason
     * delete all variable if possible
     */
    array_map(
        function($c) {
            unset($GLOBALS[$c]);
        },
        array_diff(
            array_keys($GLOBALS),
            array(
                'GLOBALS',
                '_COOKIE',
                '_ENV',
                '_FILES',
                '_GET',
                '_POST',
                '_REQUEST',
                '_SERVER',
                '_SESSION',
                'argc',
                'argv',
                'HTTP_RAW_POST_DATA',
                'http_response_header',
                'php_errormsg'
            )
        )
    );

    // config file
    if (!defined('CONFIG')) {
        define('CONFIG', dirname(ROOT) . DIRECTORY_SEPARATOR . 'config.php');
    }

    if (defined('SYSCONST')) {
        header('HTTP/1.1 503 Service Unavailable.', true, 503);
        echo 'Root files only allowed set constant ROOT or CONFIG';
        exit(1); // EXIT_ERROR
    }

    // cache the constant
    define('CONSTANT_COUNT', count(get_defined_constants()));

    if (is_file(CONFIG)) {
        $config = require CONFIG;
    }

    /**
     * if not defined Environment Constant
     */
    if (defined('ENVIRONMENT')) {
        header('HTTP/1.1 503 Service Unavailable.', true, 503);
        echo 'Environment must be declared on configuration file.';
        exit(3); // EXIT_CONFIG
    }

    if (isset($config) || CONSTANT_COUNT <> (count(get_defined_constants()) - 1)) {
        foreach (array(
                     'SOURCE', 'BASEPATH', 'FCPATH', 'APPPATH',
                     'SYSDIR', 'VIEWPATH', 'FILE_READ_MODE',
                     'FILE_WRITE_MODE', 'DIR_READ_MODE', 'DIR_WRITE_MODE',
                     'FOPEN_READ', 'FOPEN_READ_WRITE', 'FOPEN_WRITE_CREATE_DESTRUCTIVE',
                     'FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'FOPEN_WRITE_CREATE',
                     'FOPEN_READ_WRITE_CREATE', 'FOPEN_WRITE_CREATE_STRICT', 'FOPEN_READ_WRITE_CREATE_STRICT',
                     'EXIT_SUCCESS', 'EXIT_ERROR', 'EXIT_CONFIG', 'EXIT_UNKNOWN_FILE',
                     'EXIT_UNKNOWN_CLASS', 'EXIT_UNKNOWN_METHOD', 'EXIT_USER_INPUT',
                     'EXIT_DATABASE', 'EXIT__AUTO_MIN', 'EXIT__AUTO_MAX'
             ) as $val
        ) {
            if (defined($val)) {
                header('HTTP/1.1 503 Service Unavailable.', true, 503);
                echo 'There was defined system constant. System constant could not defined before init';
                exit(3); // EXIT_CONFIG
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Directory
    |--------------------------------------------------------------------------
    */
    $resource = 'Resource';
    $views = 'Views';
    $controller = 'Controller';
    $configpath = 'Config';
    $modelpath = 'Models';
    $temppath = 'Temp';
    $module = 'Modules';
    $templates = 'templates';
    $system = 'system';
    if (isset($config) && !empty($config['path']) && is_array($config['path'])) {
        if (!empty($config['path']['system'])) {
            if (!is_string($config['path']['system'])) {
                header('HTTP/1.1 503 Service Unavailable.', true, 503);
                echo 'Invalid setting for `system` path';
                exit(3); // EXIT_CONFIG
            }
            if (trim($config['path']['system']) != '') {
                $system = preg_replace('/(\\\|\/)+/', DIRECTORY_SEPARATOR, $config['path']['system']);
            }
        }
        if (!empty($config['path']['resource'])) {
            if (!is_string($config['path']['resource'])) {
                header('HTTP/1.1 503 Service Unavailable.', true, 503);
                echo 'Invalid setting for `resource` path';
                exit(3); // EXIT_CONFIG
            }
            if (trim($config['path']['resource']) != '') {
                $resource = preg_replace('/(\\\|\/)+/', DIRECTORY_SEPARATOR, $config['path']['resource']);
            }
        }
        if (!empty($config['path']['views'])) {
            if (!is_string($config['path']['views'])) {
                header('HTTP/1.1 503 Service Unavailable.', true, 503);
                echo 'Invalid setting for `views` path';
                exit(3); // EXIT_CONFIG
            }
            if (trim($config['path']['views']) != '') {
                $views = preg_replace('/(\\\|\/)+/', DIRECTORY_SEPARATOR, $config['path']['views']);
            }
        }

        if (!empty($config['path']['controller'])) {
            if (!is_string($config['path']['controller'])) {
                header('HTTP/1.1 503 Service Unavailable.', true, 503);
                echo 'Invalid setting for `controller` path';
                exit(3); // EXIT_CONFIG
            }
            if (trim($config['path']['controller']) != '') {
                $controller = preg_replace('/(\\\|\/)+/', DIRECTORY_SEPARATOR, $config['path']['controller']);
            }
        }
        if (!empty($config['path']['model'])) {
            if (!is_string($config['path']['model'])) {
                header('HTTP/1.1 503 Service Unavailable.', true, 503);
                echo 'Invalid setting for `controller` path';
                exit(3); // EXIT_CONFIG
            }
            if (trim($config['path']['model']) != '') {
                $modelpath = preg_replace('/(\\\|\/)+/', DIRECTORY_SEPARATOR, $config['path']['model']);
            }
        }
        if (!empty($config['path']['config'])) {
            if (!is_string($config['path']['config'])) {
                header('HTTP/1.1 503 Service Unavailable.', true, 503);
                echo 'Invalid setting for `config` path';
                exit(3); // EXIT_CONFIG
            }
            if (trim($config['path']['controller']) != '') {
                $configpath = preg_replace('/(\\\|\/)+/', DIRECTORY_SEPARATOR, $config['path']['config']);
            }
        }
        if (!empty($config['path']['temp'])) {
            if (!is_string($config['path']['temp'])) {
                header('HTTP/1.1 503 Service Unavailable.', true, 503);
                echo 'Invalid setting for `temp` path';
                exit(3); // EXIT_CONFIG
            }
            if (trim($config['path']['temp']) != '') {
                $configpath = preg_replace('/(\\\|\/)+/', DIRECTORY_SEPARATOR, $config['path']['temp']);
            }
        }
        if (!empty($config['path']['module'])) {
            if (!is_string($config['path']['module'])) {
                header('HTTP/1.1 503 Service Unavailable.', true, 503);
                echo 'Invalid setting for `module` path';
                exit(3); // EXIT_CONFIG
            }
            if (trim($config['path']['module']) != '') {
                $module = preg_replace('/(\\\|\/)+/', DIRECTORY_SEPARATOR, $config['path']['module']);
            }
        }
        if (!empty($config['path']['template'])) {
            if (!is_string($config['path']['template'])) {
                header('HTTP/1.1 503 Service Unavailable.', true, 503);
                echo 'Invalid setting for `templates` path';
                exit(3); // EXIT_CONFIG
            }
            if (trim($config['path']['template']) != '') {
                $templates = preg_replace('/(\\\|\/)+/', DIRECTORY_SEPARATOR, $config['path']['template']);
            }
        }
    }

    define('SOURCE', realpath(__DIR__)); //current source dir
    define('SOURCEPATH', SOURCE . DIRECTORY_SEPARATOR); //current source dir
    define('CONTROLLERPATH', SOURCEPATH . $controller . DIRECTORY_SEPARATOR);
    define('MODELPATH', SOURCEPATH . $modelpath . DIRECTORY_SEPARATOR);
    define('VIEWPATH', SOURCEPATH . DIRECTORY_SEPARATOR . $views . DIRECTORY_SEPARATOR);
    define('CONFIGPATH', SOURCEPATH . $configpath . DIRECTORY_SEPARATOR);
    define('MODULEPATH', SOURCEPATH . $module . DIRECTORY_SEPARATOR);
    define('LANGUAGEPATH', SOURCEPATH . 'Languages' . DIRECTORY_SEPARATOR);
    // admin templates
    define('ADMINTEMPLATEPATH', SOURCEPATH . 'AdminTemplates' . DIRECTORY_SEPARATOR);
    // alias an application path
    define('RESOURCEPATH', SOURCE . DIRECTORY_SEPARATOR . $resource .DIRECTORY_SEPARATOR);

    define('APPPATH', RESOURCEPATH);

    // root
    define('FCPATH', dirname(realpath(ROOT)) . DIRECTORY_SEPARATOR);// Path to the front controller (this file) directory
    define('BASEPATH', RESOURCEPATH . $system . DIRECTORY_SEPARATOR);// Path to the system directory
    define('SYSDIR', basename(BASEPATH)); // Name of the "system" directory
    if (substr($templates,0, 1) == '/') {
        $template_ = realpath($templates);
        if ($template_) {
            $templates = $template_;
        } else {
            $templates = FCPATH . $templates . DIRECTORY_SEPARATOR;
        }
        define('TEMPLATEPATH', $templates);
    } else {
        define('TEMPLATEPATH', FCPATH . $templates . DIRECTORY_SEPARATOR);
    }

    if ($temppath != 'Temp') {
        $temppath = SOURCEPATH . $temppath;
    } else {
        if (!realpath($temppath) || ! is_dir(FCPATH . $temppath)) {
            $temppath = SOURCEPATH . 'Temp';
            if (!is_dir($temppath)) {
                @mkdir($temppath, '755', true);
            }
        } elseif (is_dir(FCPATH . $temppath)) {
            $temppath = FCPATH .$temppath;
        } else {
            $temppath = SOURCEPATH . 'Temp';
        }
    }

    define('TEMPPATH', $temppath . DIRECTORY_SEPARATOR); // Name of the "temp" directory

    if (!is_dir(BASEPATH)) {
        header('HTTP/1.1 503 Service Unavailable.', true, 503);
        echo 'Directory System Does Not exist. Please check your configuration or your folder on source';
        exit(3); // EXIT_CONFIG
    }

    if (!is_dir(APPPATH)) {
        header('HTTP/1.1 503 Service Unavailable.', true, 503);
        echo 'Directory Application Does Not exist. Please check your configuration or your folder on source';
        exit(3); // EXIT_CONFIG
    }

    if (!is_dir(CONTROLLERPATH)) {
        header('HTTP/1.1 503 Service Unavailable.', true, 503);
        echo 'Directory controller Does Not exist. Please check your configuration or your folder on source';
        exit(3); // EXIT_CONFIG
    }

    if (!is_dir(VIEWPATH)) {
        header('HTTP/1.1 503 Service Unavailable.', true, 503);
        echo 'Directory views Does Not exist. Please check your configuration or your folder on source';
        exit(3); // EXIT_CONFIG
    }

    if (!is_dir(MODELPATH)) {
        header('HTTP/1.1 503 Service Unavailable.', true, 503);
        echo 'Directory model Does Not exist. Please check your configuration or your folder on source';
        exit(3); // EXIT_CONFIG
    }

    if (!is_dir(MODULEPATH)) {
        header('HTTP/1.1 503 Service Unavailable.', true, 503);
        echo 'Directory module Does Not exist. Please check your configuration or your folder on source';
        exit(3); // EXIT_CONFIG
    }
    if (!is_dir(LANGUAGEPATH)) {
        header('HTTP/1.1 503 Service Unavailable.', true, 503);
        echo 'Directory language Does Not exist. Please check your configuration or your folder on source';
        exit(3); // EXIT_CONFIG
    }

    if (!is_dir(ADMINTEMPLATEPATH)) {
        header('HTTP/1.1 503 Service Unavailable.', true, 503);
        echo 'Directory Admin Templates Does Not exist. Please check your configuration or your folder on source';
        exit(3); // EXIT_CONFIG
    }

    if (!is_dir(TEMPLATEPATH)) {
        header('HTTP/1.1 503 Service Unavailable.', true, 503);
        echo 'Directory templates Does Not exist. Please check your configuration or your folder on source';
        exit(3); // EXIT_CONFIG
    }

    unset($system,
        $val,
        $views,
        $resource,
        $arr,
        $config,
        $configpath,
        $controller,
        $modelpath
    );

    /*
    |--------------------------------------------------------------------------
    | Display Debug backtrace
    |--------------------------------------------------------------------------
    */
    !defined('SHOW_DEBUG_BACKTRACE') && define('SHOW_DEBUG_BACKTRACE', true);

    /*
    |--------------------------------------------------------------------------
    | File and Directory Modes
    |--------------------------------------------------------------------------
    */
    define('FILE_READ_MODE', 0644);
    define('FILE_WRITE_MODE', 0666);
    define('DIR_READ_MODE', 0755);
    define('DIR_WRITE_MODE', 0755);

    /*
    |--------------------------------------------------------------------------
    | File Stream Modes
    |--------------------------------------------------------------------------
    */
    define('FOPEN_READ', 'rb');
    define('FOPEN_READ_WRITE', 'r+b');
    define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
    define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
    define('FOPEN_WRITE_CREATE', 'ab');
    define('FOPEN_READ_WRITE_CREATE', 'a+b');
    define('FOPEN_WRITE_CREATE_STRICT', 'xb');
    define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

    /*
    |--------------------------------------------------------------------------
    | Exit Status Codes
    |--------------------------------------------------------------------------
    */
    define('EXIT_SUCCESS', 0); // no errors
    define('EXIT_ERROR', 1); // generic error
    define('EXIT_CONFIG', 3); // configuration error
    define('EXIT_UNKNOWN_FILE', 4); // file not found
    define('EXIT_UNKNOWN_CLASS', 5); // unknown class
    define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
    define('EXIT_USER_INPUT', 7); // invalid user input
    define('EXIT_DATABASE', 8); // database error
    define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
    define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code
}
