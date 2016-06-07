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
                echo 'There was defined system constant. System constant could not defined before init : (' .$val .')';
                exit(3); // EXIT_CONFIG
            }
        }
    }

    ! defined('DS') && define('DS', DIRECTORY_SEPARATOR);
    if (DS !== DS) {
        header('HTTP/1.1 503 Service Unavailable.', true, 503);
        echo 'There was defined system constant. Constant `DS` must be equal with DS';
        exit(3); // EXIT_CONFIG
    }

    $configs = isset($config) ? $config : null;

    /*
    |--------------------------------------------------------------------------
    | Directory
    |--------------------------------------------------------------------------
    */
    $system     = 'System';
    $resource   = 'Resource';
    $view       = 'Views';
    $controller = 'Controller';
    $config     = 'Config';
    $model      = 'Models';
    $temp       = 'Temp';
    $module     = 'Modules';
    $template   = 'template';
    $asset      = 'assets';
    $upload     = 'uploads';
    if (!empty($configs['path']) && is_array($configs['path'])) {
        foreach (array(
            'system',
            'resource',
            'view',
            'controller',
            'config',
            'model',
            'temp',
            'module',
            'template',
            'asset',
            'upload',
        ) as $v) {
            if (!empty($configs['path'][$v])) {
                if (!is_string($configs['path']['system'])) {
                    header('HTTP/1.1 503 Service Unavailable.', true, 503);
                    printf('Invalid setting for `%s` path', $v);
                    exit(3);
                }
                if (trim($configs['path'][$v]) != '') {
                    $$v = preg_replace('/(\\\|\/)+/', DS, $configs['path'][$v]);
                }
            }
        }
    }

    define('SOURCE', realpath(__DIR__)); //current source dir
    define('SOURCEPATH', SOURCE . DS); //current source dir
    define('CONTROLLERPATH', SOURCEPATH . $controller . DS);
    define('MODELPATH', SOURCEPATH . $model . DS);
    define('VIEWPATH', SOURCEPATH . $view . DS);
    define('CONFIGPATH', SOURCEPATH . $config . DS);
    define('MODULEPATH', SOURCEPATH . $module . DS);
    define('LANGUAGEPATH', SOURCEPATH . 'Languages' . DS);
    define('ADMINTEMPLATEPATH', SOURCEPATH . 'AdminTemplates' . DS); // admin template
    define('RESOURCEPATH', SOURCE . DS . $resource .DS); // alias an application path
    define('APPPATH', RESOURCEPATH);
    define('FCPATH', dirname(realpath(ROOT)) . DS);// Path to the front controller (this file) directory
    define('BASEPATH', RESOURCEPATH . $system . DS);// Path to the system directory
    define('SYSDIR', basename(BASEPATH)); // Name of the "system" directory
    define('ASSETPATH', FCPATH . $asset . DS); // Name of the "system" directory
    define('UPLOADPATH', FCPATH . $upload . DS); // Name of the "system" directory

    /**
     * Validate Template Path
     */
    if (substr($template,0, 1) == '/') {
        $template_ = realpath($template);
        if ($template_) {
            $template = $template_;
        } else {
            $template = FCPATH . $template . DS;
        }
        define('TEMPLATEPATH', $template);
    } else {
        define('TEMPLATEPATH', FCPATH . $template . DS);
    }

    /**
     * Validate Temp path
     */
    if ($temp != 'Temp') {
        $temp = SOURCEPATH . $temp;
    } else {
        if (!realpath($temp) || ! is_dir(FCPATH . $temp)) {
            $temp = SOURCEPATH . 'Temp';
            if (!is_dir($temp)) {
                @mkdir($temp, '755', true);
            }
        } elseif (is_dir(FCPATH . $temp)) {
            $temp = FCPATH . $temp;
        } else {
            $temp = SOURCEPATH . 'Temp';
        }
    }

    define('TEMPPATH', $temp . DS); // Name of the "temp" directory

    /**
     * Check Upload Path
     */
    if (strpos(UPLOADPATH, realpath(SOURCEPATH)) === 0) {
        header('HTTP/1.1 503 Service Unavailable.', true, 503);
        echo 'Invalid setting for `upload` path. Upload path must be not in engine application directory';
        exit(3); // EXIT_CONFIG
    }
    if (strpos(UPLOADPATH, realpath(TEMPLATEPATH)) === 0) {
        header('HTTP/1.1 503 Service Unavailable.', true, 503);
        echo 'Invalid setting for `upload` path. Upload path must be not in template directory';
        exit(3); // EXIT_CONFIG
    }
    if (strpos(UPLOADPATH, realpath(ASSETPATH)) === 0) {
        header('HTTP/1.1 503 Service Unavailable.', true, 503);
        echo 'Invalid setting for `upload` path. Upload path must be not in asset directory';
        exit(3); // EXIT_CONFIG
    }
    if (strpos(UPLOADPATH, realpath(FCPATH)) !== 0) {
        header('HTTP/1.1 503 Service Unavailable.', true, 503);
        echo 'Invalid setting for `upload` path. Upload path must be in root directory';
        exit(3); // EXIT_CONFIG
    }
    /**
     * Re CHecking Upload Path
     */
    if (!is_dir(UPLOADPATH)) {
        if (file_exists(UPLOADPATH)) {
            header('HTTP/1.1 503 Service Unavailable.', true, 503);
            echo 'Looks like your upload path exist and as not a directory.';
            exit(3); // EXIT_CONFIG
        } elseif (!@mkdir(UPLOADPATH, 755, true)) {
            header('HTTP/1.1 503 Service Unavailable.', true, 503);
            echo 'Could not create upload directory';
            exit(3); // EXIT_CONFIG
        }
        if (!file_exists(UPLOADPATH .'index.html') && is_writable(UPLOADPATH)) {
            // add index
            @file_put_contents(UPLOADPATH . 'index.html', '');
        }
    }

    /**
     * Check Directory Existences
     */
    foreach (array(
        SOURCEPATH => 'Source',
        BASEPATH => 'System',
        APPPATH  => 'Application',
        CONTROLLERPATH => 'Controller',
        VIEWPATH => 'Views',
        MODELPATH => 'Model',
        MODULEPATH => 'Module',
        LANGUAGEPATH => 'Language',
        ADMINTEMPLATEPATH => 'Admin Templates',
        TEMPLATEPATH => 'Templates',
        TEMPPATH => 'Temporary',
        ASSETPATH => 'assets',
    ) as $k => $v) {
        if (!is_dir($k)) {
            header('HTTP/1.1 503 Service Unavailable.', true, 503);
            echo 'Directory ' . $v . ' Does Not exist. Please check your configuration or your folder on source';
        }
    }

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

    unset($k,
        $v,
        $system ,
        $resource,
        $view,
        $controller,
        $config,
        $model,
        $temp,
        $module,
        $template,
        $asset,
        $upload
    );
}
