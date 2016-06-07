<?php
class PentagonalLoader extends CI_Loader
{
    // All these are set automatically. Don't mess with them.
    /**
     * Nesting level of the output buffering mechanism
     *
     * @var	int
     */
    protected $_ci_ob_level;

    /**
     * List of paths to load view from
     *
     * @var	array
     */
    protected $_ci_view_paths =    array(VIEWPATH    => true);

    /**
     * List of paths to load libraries from
     *
     * @var	array
     */
    protected $_ci_library_paths =    array(APPPATH, BASEPATH);

    /**
     * List of paths to load models from
     *
     * @var	array
     */
    protected $_ci_model_paths =    array(APPPATH);

    /**
     * List of paths to load helpers from
     *
     * @var	array
     */
    protected $_ci_helper_paths =    array(APPPATH, BASEPATH);

    /**
     * List of cached variables
     *
     * @var	array
     */
    protected $_ci_cached_vars =    array();

    /**
     * List of loaded classes
     *
     * @var	array
     */
    protected $_ci_classes =    array();

    /**
     * List of loaded models
     *
     * @var	array
     */
    protected $_ci_models =    array();

    /**
     * List of loaded helpers
     *
     * @var	array
     */
    protected $_ci_helpers =    array();

    /**
     * List of class name mappings
     *
     * @var	array
     */
    protected $_ci_varmap =    array(
        'unit_test' => 'unit',
        'user_agent' => 'agent'
    );

    protected $active_theme;

    // --------------------------------------------------------------------

    /**
     * set Active Theme
     *
     * @param $name
     */
    public function setActiveTemplate($name)
    {
        if (is_string($name)) {
            $name = preg_replace('/(\\\|\/)+/', DS, $name);
            $name = rtrim($name, DS) . DS;
            if (($name !== TEMPLATEPATH && $name !== ADMINTEMPLATEPATH)
                && (strpos($name, TEMPLATEPATH) !== false || strpos($name, ADMINTEMPLATEPATH) !== false)
                && is_dir($name)
            ) {
                $this->active_theme = $name;
            }
        }
    }

    /**
     * @return null|mixed
     */
    public function getActiveTemplate()
    {
        return $this->active_theme;
    }

    /**
     * Model Loader
     *
     * Loads and instantiates models.
     *
     * @param	string	$model		Model name
     * @param	string	$name		An optional object name to assign to
     * @param	bool	$db_conn	An optional database connection configuration to initialize
     * @return	object
     */
    public function model($model, $name = '', $db_conn = false)
    {
        if (empty($model)) {
            return $this;
        } elseif (is_array($model)) {
            foreach ($model as $key => $value) {
                is_int($key) ? $this->model($value, '', $db_conn) : $this->model($key, $value, $db_conn);
            }

            return $this;
        }

        $path = '';

        // Is the model in a sub-folder? If so, parse out the filename and path.
        if (($last_slash = strrpos($model, '/')) !== false) {
            // The path is in front of the last slash
            $path = substr($model, 0, ++$last_slash);

            // And the model name behind it
            $model = substr($model, $last_slash);
        }

        if (empty($name)) {
            $name = $model;
        }

        if (in_array($name, $this->_ci_models, true)) {
            return $this;
        }

        $CI =& get_instance();
        if (isset($CI->$name)) {
            throw new RuntimeException('The model name you are loading is the name of a resource that is already being used: '.$name);
        }

        if ($db_conn !== false && ! class_exists('CI_DB', false)) {
            if ($db_conn === true) {
                $db_conn = '';
            }

            $this->database($db_conn, false, true);
        }

        // Note: All of the code under this condition used to be just:
        //
        //       load_class('Model', 'core');
        //
        //       However, load_class() instantiates classes
        //       to cache them for later use and that prevents
        //       MY_Model from being an abstract class and is
        //       sub-optimal otherwise anyway.
        if (! class_exists('CI_Model', false)) {
            $app_path = APPPATH.'Core'.DS;
            if (file_exists($app_path.'Model.php')) {
                require_once($app_path.'Model.php');
                if (! class_exists('CI_Model', false)) {
                    throw new RuntimeException($app_path."Model.php exists, but doesn't declare class CI_Model");
                }
            } elseif (! class_exists('CI_Model', false)) {
                require_once(BASEPATH.'core'.DS.'Model.php');
            }

            $class = config_item('subclass_prefix').'Model';
            if (file_exists($app_path.$class.'.php')) {
                require_once($app_path.$class.'.php');
                if (! class_exists($class, false)) {
                    throw new RuntimeException($app_path.$class.".php exists, but doesn't declare class ".$class);
                }
            }
        }

        $model = ucfirst($model);
        if (! class_exists($model, false)) {
            if (file_exists(MODELPATH . $path . $model . '.php')) {
                require_once(MODELPATH . $path . $model . '.php');
                if (! class_exists($model, false)) {
                    throw new RuntimeException(MODELPATH . $path . $model . ".php exists, but doesn't declare class ".$model);
                }
            } else {
                foreach ($this->_ci_model_paths as $mod_path) {
                    if (!file_exists($mod_path . 'Models/' . $path . $model . '.php')) {
                        continue;
                    }

                    require_once($mod_path . 'models/' . $path . $model . '.php');
                    if (!class_exists($model, false)) {
                        throw new RuntimeException($mod_path . "Models/" . $path . $model . ".php exists, but doesn't declare class " . $model);
                    }

                    break;
                }
            }

            if (! class_exists($model, false)) {
                throw new RuntimeException('Unable to locate the model you have specified: '.$model);
            }
        } elseif (! is_subclass_of($model, 'CI_Model')) {
            throw new RuntimeException("Class ".$model." already exists and doesn't extend CI_Model");
        }

        $this->_ci_models[] = $name;
        $CI->$name = new $model();
        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Database Loader
     *
     * @param	mixed	$params		Database configuration options
     * @param	bool	$return 	Whether to return the database object
     * @param	bool	$query_builder	Whether to enable Query Builder
     *					(overrides the configuration setting)
     *
     * @return	object|bool	Database object if $return is set to true,
     *					false on failure, CI_Loader instance in any other case
     */
    public function database($params = '', $return = false, $query_builder = null)
    {
        // Grab the super object
        $CI =& get_instance();

        // Do we even need to load the database class?
        if ($return === false && $query_builder === null
            && isset($CI->db) && is_object($CI->db) && ! empty($CI->db->conn_id)
        ) {
            return false;
        }

        require_once(RESOURCEPATH. 'Database'.DS.'DB.php');

        if ($return === true) {
            return DB($params, $query_builder);
        }

        // Initialize the db variable. Needed to prevent
        // reference errors with some configurations
        $CI->db = '';

        // Load the DB class
        $CI->db =& DB($params, $query_builder);
        return $this;
    }

    /**
     * Helper Loader
     *
     * @param	string|string[]	$helpers	Helper name(s)
     * @return	object
     */
    public function helper($helpers = array())
    {
        foreach ($this->_ci_prep_filename($helpers, '_helper') as $helper) {
            if (isset($this->_ci_helpers[$helper])) {
                continue;
            }

            $helpers = 'Helper';
            // Is this a helper extension request?
            $ext_helper = config_item('subclass_prefix').$helper;
            $ext_loaded = false;
            foreach ($this->_ci_helper_paths as $path) {
                if (file_exists($path.'Helpers/'.$ext_helper.'.php')) {
                    include_once($path.'Helpers/'.$ext_helper.'.php');
                    $ext_loaded = true;
                } elseif (file_exists($path.'helpers/'.$ext_helper.'.php')) {
                    include_once($path.'helpers/'.$ext_helper.'.php');
                    $helpers = 'helpers';
                    $ext_loaded = true;
                }
            }

            // If we have loaded extensions - check if the base one is here
            if ($ext_loaded === true) {
                $base_helper = BASEPATH.'helpers/'.$helper.'.php';
                if (! file_exists($base_helper)) {
                    show_error('Unable to load the requested file: helpers/'.$helper.'.php');
                }

                include_once($base_helper);
                $this->_ci_helpers[$helper] = true;
                log_message('info', 'Helper loaded: '.$helper);
                continue; // break in here
            }

            // No extensions found ... try loading regular helpers and/or overrides
            foreach ($this->_ci_helper_paths as $path) {
                if (file_exists($path.'Helpers/'.$helper.'.php')) {
                    include_once($path.'Helpers/'.$helper.'.php');
                    $this->_ci_helpers[$helper] = true;
                    log_message('info', 'Helper loaded: '.$helper);
                    break;
                } elseif (file_exists($path.'helpers/'.$helper.'.php')) {
                    include_once($path.'helpers/'.$helper.'.php');
                    $this->_ci_helpers[$helper] = true;
                    log_message('info', 'Helper loaded: '.$helper);
                    $helpers = 'helpers';
                    break;
                }
            }

            // unable to load the helper
            if (! isset($this->_ci_helpers[$helper])) {
                show_error('Unable to load the requested file: '.$helpers.'/'.$helper.'.php');
            }
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Load Helpers
     *
     * An alias for the helper() method in case the developer has
     * written the plural form of it.
     *
     * @uses	CI_Loader::helper()
     * @param	string|string[]	$helpers	Helper name(s)
     * @return	object
     */
    public function helpers($helpers = array())
    {
        return $this->helper($helpers);
    }

    // --------------------------------------------------------------------

    /**
     * Language Loader
     *
     * Loads language files.
     *
     * @param	string|string[]	$files	List of language file names to load
     * @param	string		Language name
     * @return	object
     */
    public function loadTextDomain($textdomain, $path)
    {
        get_instance()->lang->loadTextDomain($textdomain, $path);
        return $this;
    }

    /**
     * Language Loader
     *
     * Register Text Domain
     *
     * @param	string	$textdomain	List of language file names to load
     * @param	string	$path full directory
     * @return	object
     */
    public function language($textdomain, $path = '')
    {
        if ($path == '') {
            return $this;
        }
        return $this->loadTextDomain($textdomain, $path);
    }

    // --------------------------------------------------------------------

    /**
     * Config Loader
     *
     * Loads a config file (an alias for CI_Config::load()).
     *
     * @uses	CI_Config::load()
     * @param	string	$file			Configuration file name
     * @param	bool	$use_sections		Whether configuration values should be loaded into their own section
     * @param	bool	$fail_gracefully	Whether to just return false or display an error message
     * @return	bool	true if the file was loaded correctly or false on failure
     */
    public function config($file, $use_sections = false, $fail_gracefully = false)
    {
        return get_instance()->config->load($file, $use_sections, $fail_gracefully);
    }

    // --------------------------------------------------------------------

    /**
     * Driver Loader
     *
     * Loads a driver library.
     *
     * @param	string|string[]	$library	Driver name(s)
     * @param	array		$params		Optional parameters to pass to the driver
     * @param	string		$object_name	An optional object name to assign to
     *
     * @return	object|bool	Object or false on failure if $library is a string
     *				and $object_name is set. CI_Loader instance otherwise.
     */
    public function driver($library, $params = null, $object_name = null)
    {
        if (is_array($library)) {
            foreach ($library as $key => $value) {
                if (is_int($key)) {
                    $this->driver($value, $params);
                } else {
                    $this->driver($key, $params, $value);
                }
            }

            return $this;
        } elseif (empty($library)) {
            return false;
        }

        if (! class_exists('CI_Driver_Library', false)) {
            // We aren't instantiating an object here, just making the base class available
            require BASEPATH.'libraries/Driver.php';
        }

        // We can save the loader some time since Drivers will *always* be in a subfolder,
        // and typically identically named to the library
        if (! strpos($library, '/')) {
            $library = ucfirst($library).'/'.$library;
        }

        return $this->library($library, $params, $object_name);
    }

    // --------------------------------------------------------------------

    /**
     * Add Package Path
     *
     * Prepends a parent path to the library, model, helper and config
     * path arrays.
     *
     * @see	CI_Loader::$_ci_library_paths
     * @see	CI_Loader::$_ci_model_paths
     * @see CI_Loader::$_ci_helper_paths
     * @see CI_Config::$_config_paths
     *
     * @param	string	$path		Path to add
     * @param 	bool	$view_cascade	(default: true)
     * @return	object
     */
    public function add_package_path($path, $view_cascade = true)
    {
        $path = rtrim($path, '/').'/';

        array_unshift($this->_ci_library_paths, $path);
        array_unshift($this->_ci_model_paths, $path);
        array_unshift($this->_ci_helper_paths, $path);

        $this->_ci_view_paths = array($path.'Views/' => $view_cascade) + $this->_ci_view_paths;

        // Add config file path
        $config =& $this->_ci_get_component('config');
        $config->_config_paths[] = $path;

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Get Package Paths
     *
     * Return a list of all package paths.
     *
     * @param	bool	$include_base	Whether to include BASEPATH (default: false)
     * @return	array
     */
    public function get_package_paths($include_base = false)
    {
        return ($include_base === true) ? $this->_ci_library_paths : $this->_ci_model_paths;
    }

    // --------------------------------------------------------------------

    /**
     * Remove Package Path
     *
     * Remove a path from the library, model, helper and/or config
     * path arrays if it exists. If no path is provided, the most recently
     * added path will be removed removed.
     *
     * @param	string	$path	Path to remove
     * @return	object
     */
    public function remove_package_path($path = '')
    {
        $config =& $this->_ci_get_component('config');

        if ($path === '') {
            array_shift($this->_ci_library_paths);
            array_shift($this->_ci_model_paths);
            array_shift($this->_ci_helper_paths);
            array_shift($this->_ci_view_paths);
            array_pop($config->_config_paths);
        } else {
            $path = rtrim($path, '/').'/';
            foreach (array('_ci_library_paths', '_ci_model_paths', '_ci_helper_paths') as $var) {
                if (($key = array_search($path, $this->{$var})) !== false) {
                    unset($this->{$var}[$key]);
                }
            }

            if (isset($this->_ci_view_paths[$path.'Views/'])) {
                unset($this->_ci_view_paths[$path.'Views/']);
            }

            if (($key = array_search($path, $config->_config_paths)) !== false) {
                unset($config->_config_paths[$key]);
            }
        }

        // make sure the resource default paths are still in the array
        $this->_ci_library_paths = array_unique(array_merge($this->_ci_library_paths, array(APPPATH, BASEPATH)));
        $this->_ci_helper_paths = array_unique(array_merge($this->_ci_helper_paths, array(APPPATH, BASEPATH)));
        $this->_ci_model_paths = array_unique(array_merge($this->_ci_model_paths, array(APPPATH)));
        $this->_ci_view_paths = array_merge($this->_ci_view_paths, array(SOURCEPATH.'Views/' => true));
        $config->_config_paths = array_unique(array_merge($config->_config_paths, array(APPPATH)));

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Internal CI Library Loader
     *
     * @used-by	CI_Loader::library()
     * @uses	CI_Loader::_ci_init_library()
     *
     * @param	string	$class		Class name to load
     * @param	mixed	$params		Optional parameters to pass to the class constructor
     * @param	string	$object_name	Optional object name to assign to
     * @return	void
     */
    protected function _ci_load_library($class, $params = null, $object_name = null)
    {
        // Get the class name, and while we're at it trim any slashes.
        // The directory path can be included as part of the class name,
        // but we don't want a leading slash
        $class = str_replace('.php', '', trim($class, '/'));

        // Was the path included with the class name?
        // We look for a slash to determine this
        if (($last_slash = strrpos($class, '/')) !== false) {
            // Extract the path
            $subdir = substr($class, 0, ++$last_slash);

            // Get the filename from the path
            $class = substr($class, $last_slash);
        } else {
            $subdir = '';
        }

        $class = ucfirst($class);

        // Is this a stock library? There are a few special conditions if so ...
        if (file_exists(BASEPATH.'libraries/'.$subdir.$class.'.php')) {
            return $this->_ci_load_stock_library($class, $subdir, $params, $object_name);
        }

        // Let's search for the requested library file and load it.
        foreach ($this->_ci_library_paths as $path) {
            // BASEPATH has already been checked for
            if ($path === BASEPATH) {
                continue;
            }

            $filepath = $path.'Libraries/'.$subdir.$class.'.php';
            if (!file_exists($filepath)) {
                // case sensitive
                $filepath = $path.'libraries/'.$subdir.$class.'.php';
            }

            // Safety: Was the class already loaded by a previous call?
            if (class_exists($class, false)) {
                // Before we deem this to be a duplicate request, let's see
                // if a custom object name is being supplied. If so, we'll
                // return a new instance of the object
                if ($object_name !== null) {
                    $CI =& get_instance();
                    if (! isset($CI->$object_name)) {
                        return $this->_ci_init_library($class, '', $params, $object_name);
                    }
                }

                log_message('debug', $class.' class already loaded. Second attempt ignored.');
                return;
            }
            // Does the file exist? No? Bummer...
            elseif (! file_exists($filepath)) {
                continue;
            }

            include_once($filepath);
            return $this->_ci_init_library($class, '', $params, $object_name);
        }

        // One last attempt. Maybe the library is in a subdirectory, but it wasn't specified?
        if ($subdir === '') {
            return $this->_ci_load_library($class.'/'.$class, $params, $object_name);
        }

        // If we got this far we were unable to find the requested class.
        log_message('error', 'Unable to load the requested class: '.$class);
        show_error('Unable to load the requested class: '.$class);
    }

    // --------------------------------------------------------------------

    /**
     * Internal CI Stock Library Loader
     *
     * @used-by	CI_Loader::_ci_load_library()
     * @uses	CI_Loader::_ci_init_library()
     *
     * @param	string	$library	Library name to load
     * @param	string	$file_path	Path to the library filename, relative to libraries/
     * @param	mixed	$params		Optional parameters to pass to the class constructor
     * @param	string	$object_name	Optional object name to assign to
     * @return	void
     */
    protected function _ci_load_stock_library($library_name, $file_path, $params, $object_name)
    {
        $prefix = 'CI_';

        if (class_exists($prefix.$library_name, false)) {
            if (class_exists(config_item('subclass_prefix').$library_name, false)) {
                $prefix = config_item('subclass_prefix');
            }

            // Before we deem this to be a duplicate request, let's see
            // if a custom object name is being supplied. If so, we'll
            // return a new instance of the object
            if ($object_name !== null) {
                $CI =& get_instance();
                if (! isset($CI->$object_name)) {
                    return $this->_ci_init_library($library_name, $prefix, $params, $object_name);
                }
            }

            log_message('debug', $library_name.' class already loaded. Second attempt ignored.');
            return;
        }

        $paths = $this->_ci_library_paths;
        array_pop($paths); // BASEPATH
        array_pop($paths); // APPPATH (needs to be the first path checked)
        array_unshift($paths, APPPATH);

        foreach ($paths as $path) {
            if (file_exists($path = $path.'Libraries/'.$file_path.$library_name.'.php')
                || file_exists($path = $path.'libraries/'.$file_path.$library_name.'.php')
            ) {
                // Override
                include_once($path);
                if (class_exists($prefix.$library_name, false)) {
                    return $this->_ci_init_library($library_name, $prefix, $params, $object_name);
                } else {
                    log_message('debug', $path.' exists, but does not declare '.$prefix.$library_name);
                }
            }
        }

        include_once(BASEPATH.'libraries/'.$file_path.$library_name.'.php');

        // Check for extensions
        $subclass = config_item('subclass_prefix').$library_name;
        foreach ($paths as $path) {
            if (file_exists($path = $path.'Libraries/'.$file_path.$subclass.'.php')
                || file_exists($path = $path.'libraries/'.$file_path.$subclass.'.php')
            ) {
                include_once($path);
                if (class_exists($subclass, false)) {
                    $prefix = config_item('subclass_prefix');
                    break;
                } else {
                    log_message('debug', $path.' exists, but does not declare '.$subclass);
                }
            }
        }

        return $this->_ci_init_library($library_name, $prefix, $params, $object_name);
    }

    // --------------------------------------------------------------------

    /**
     * Internal CI Library Instantiator
     *
     * @used-by	CI_Loader::_ci_load_stock_library()
     * @used-by	CI_Loader::_ci_load_library()
     *
     * @param	string		$class		Class name
     * @param	string		$prefix		Class name prefix
     * @param	array|null|bool	$config		Optional configuration to pass to the class constructor:
     *						false to skip;
     *						null to search in config paths;
     *						array containing configuration data
     * @param	string		$object_name	Optional object name to assign to
     * @return	void
     */
    protected function _ci_init_library($class, $prefix, $config = false, $object_name = null)
    {
        // Is there an associated config file for this class? Note: these should always be lowercase
        if ($config === null) {
            // Fetch the config paths containing any package paths
            $config_component = $this->_ci_get_component('config');

            if (is_array($config_component->_config_paths)) {
                $found = false;
                foreach ($config_component->_config_paths as $path) {
                    // We test for both uppercase and lowercase, for servers that
                    // are case-sensitive with regard to file names. Load global first,
                    // override with environment next
                    if (file_exists(CONFIGPATH.strtolower($class).'.php')) {
                        include(CONFIGPATH . strtolower($class).'.php');
                        $found = true;
                    } elseif (file_exists(CONFIGPATH.ucfirst(strtolower($class)).'.php')) {
                        include(CONFIGPATH . ucfirst(strtolower($class)).'.php');
                        $found = true;
                    } elseif (file_exists($path.'Config/'.strtolower($class).'.php')) {
                        include($path.'Config/'.strtolower($class).'.php');
                        $found = true;
                    } elseif (file_exists($path.'Config/'.ucfirst(strtolower($class)).'.php')) {
                        include($path.'Config/'.ucfirst(strtolower($class)).'.php');
                        $found = true;
                    } elseif (file_exists($path.'config/'.strtolower($class).'.php')) {
                        include($path.'config/'.strtolower($class).'.php');
                        $found = true;
                    } elseif (file_exists($path.'config/'.ucfirst(strtolower($class)).'.php')) {
                        include($path.'config/'.ucfirst(strtolower($class)).'.php');
                        $found = true;
                    }

                    if (file_exists(CONFIGPATH.ENVIRONMENT.'/'.strtolower($class).'.php')) {
                        include(CONFIGPATH  .ENVIRONMENT.'/'.strtolower($class).'.php');
                        $found = true;
                    } elseif (file_exists(CONFIGPATH.ENVIRONMENT.'/'.ucfirst(strtolower($class)).'.php')) {
                        include(CONFIGPATH.ENVIRONMENT.'/'.ucfirst(strtolower($class)).'.php');
                        $found = true;
                    } elseif (file_exists($path.'Config/'.ENVIRONMENT.'/'.strtolower($class).'.php')) {
                        include($path.'Config/'.ENVIRONMENT.'/'.strtolower($class).'.php');
                        $found = true;
                    } elseif (file_exists($path.'Config/'.ENVIRONMENT.'/'.ucfirst(strtolower($class)).'.php')) {
                        include($path.'Config/'.ENVIRONMENT.'/'.ucfirst(strtolower($class)).'.php');
                        $found = true;
                    } elseif (file_exists($path.'config/'.ENVIRONMENT.'/'.strtolower($class).'.php')) {
                        include($path.'config/'.ENVIRONMENT.'/'.strtolower($class).'.php');
                        $found = true;
                    } elseif (file_exists($path.'config/'.ENVIRONMENT.'/'.ucfirst(strtolower($class)).'.php')) {
                        include($path.'config/'.ENVIRONMENT.'/'.ucfirst(strtolower($class)).'.php');
                        $found = true;
                    }

                    // Break on the first found configuration, thus package
                    // files are not overridden by default paths
                    if ($found === true) {
                        break;
                    }
                }
            }
        }

        $class_name = $prefix.$class;

        // Is the class name valid?
        if (! class_exists($class_name, false)) {
            log_message('error', 'Non-existent class: '.$class_name);
            show_error('Non-existent class: '.$class_name);
        }

        // Set the variable name we will assign the class to
        // Was a custom class name supplied? If so we'll use it
        if (empty($object_name)) {
            $object_name = strtolower($class);
            if (isset($this->_ci_varmap[$object_name])) {
                $object_name = $this->_ci_varmap[$object_name];
            }
        }

        // Don't overwrite existing properties
        $CI =& get_instance();
        if (isset($CI->$object_name)) {
            if ($CI->$object_name instanceof $class_name) {
                log_message('debug', $class_name." has already been instantiated as '".$object_name."'. Second attempt aborted.");
                return;
            }

            show_error("Resource '".$object_name."' already exists and is not a ".$class_name." instance.");
        }

        // Save the class name and object name
        $this->_ci_classes[$object_name] = $class;

        // Instantiate the class
        $CI->$object_name = isset($config)
            ? new $class_name($config)
            : new $class_name();
    }

    /**
     * Internal CI Data Loader
     *
     * Used to load view and files.
     *
     * Variables are prefixed with _ci_ to avoid symbol collision with
     * variables made available to view files.
     *
     * @used-by	CI_Loader::view()
     * @used-by	CI_Loader::file()
     * @param	array	$_ci_data	Data to load
     * @return	object
     */
    protected function _ci_load($_ci_data)
    {
        // Set the default data variables
        foreach (array('_ci_view', '_ci_vars', '_ci_path', '_ci_return') as $_ci_val) {
            $$_ci_val = isset($_ci_data[$_ci_val]) ? $_ci_data[$_ci_val] : false;
        }

        $file_exists = false;

        // Set the path to the requested file
        if (is_string($_ci_path) && $_ci_path !== '') {
            $_ci_x = explode('/', $_ci_path);
            $_ci_file = end($_ci_x);
        } else {
            $_ci_ext = pathinfo($_ci_view, PATHINFO_EXTENSION);
            $_ci_file = ($_ci_ext === '') ? $_ci_view.'.php' : $_ci_view;
            /**
             * Customize the view Path
             * add template directory
             */
            $the_view_path = $this->_ci_view_paths;
            if (is_string($this->getActiveTemplate()) && $this->getActiveTemplate() != '') {
                $path = $this->getActiveTemplate();
                if ($path && ($path = realpath($path))) {
                    if (($path !== TEMPLATEPATH && $path !== ADMINTEMPLATEPATH)
                        && (strpos($path, TEMPLATEPATH) !== false || strpos($path, ADMINTEMPLATEPATH) !== false)
                        && is_dir($path)
                    ) {
                        $path .= DS;
                        $the_view_path = array_merge(
                            array(
                                $path => true
                            ),
                            $the_view_path
                        );
                    }
                }
            }

            foreach ($the_view_path as $_ci_view_file => $cascade) {
                if (file_exists($_ci_view_file.$_ci_file)) {
                    $_ci_path = $_ci_view_file.$_ci_file;
                    $file_exists = true;
                    break;
                }

                if ( ! $cascade) {
                    break;
                }
            }
        }

        if ( ! $file_exists && ! file_exists($_ci_path)) {
            show_error('Unable to load the requested file: '.$_ci_file);
        }

        // This allows anything loaded using $this->load (view, files, etc.)
        // to become accessible from within the Controller and Model functions.
        $_ci_CI =& get_instance();
        foreach (get_object_vars($_ci_CI) as $_ci_key => $_ci_var) {
            if ( ! isset($this->$_ci_key)) {
                $this->$_ci_key =& $_ci_CI->$_ci_key;
            }
        }

        /*
         * Extract and cache variables
         *
         * You can either set variables using the dedicated $this->load->vars()
         * function or via the second parameter of this function. We'll merge
         * the two types and cache them so that view that are embedded within
         * other view can have access to these variables.
         */
        if (is_array($_ci_vars)) {
            foreach (array_keys($_ci_vars) as $key) {
                if (strncmp($key, '_ci_', 4) === 0) {
                    unset($_ci_vars[$key]);
                }
            }

            $this->_ci_cached_vars = array_merge($this->_ci_cached_vars, $_ci_vars);
        }
        extract($this->_ci_cached_vars);

        /*
         * Buffer the output
         *
         * We buffer the output for two reasons:
         * 1. Speed. You get a significant speed boost.
         * 2. So that the final rendered template can be post-processed by
         *	the output class. Why do we need post processing? For one thing,
         *	in order to show the elapsed page load time. Unless we can
         *	intercept the content right before it's sent to the browser and
         *	then stop the timer it won't be accurate.
         */
        ob_start();

        // If the PHP installation does not support short tags we'll
        // do a little string replacement, changing the short tags
        // to standard PHP echo statements.
        if ( ! is_php('5.4') && ! ini_get('short_open_tag') && config_item('rewrite_short_tags') === true) {
            echo eval('?>'.preg_replace('/;*\s*\?>/', '; ?>', str_replace('<?=', '<?php echo ', file_get_contents($_ci_path))));
        } else {
            include($_ci_path); // include() vs include_once() allows for multiple view with the same name
        }

        log_message('info', 'File loaded: '.$_ci_path);

        // Return the file data if requested
        if ($_ci_return === true) {
            $buffer = ob_get_contents();
            @ob_end_clean();
            return $buffer;
        }

        /*
         * Flush the buffer... or buff the flusher?
         *
         * In order to permit view to be nested within
         * other view, we need to flush the content back out whenever
         * we are beyond the first level of output buffering so that
         * it can be seen and included properly by the first included
         * template and any subsequent ones. Oy!
         */
        if (ob_get_level() > $this->_ci_ob_level + 1) {
            ob_end_flush();
        } else {
            $_ci_CI->output->append_output(ob_get_contents());
            @ob_end_clean();
        }

        return $this;
    }

    /**
     * View Loader
     *
     * Loads "view" files.
     *
     * @param	string	$view	View name
     * @param	array	$vars	An associative array of data
     *				to be extracted for use in the view
     * @param	bool	$return	Whether to return the view output
     *				or leave it to the Output class
     * @return	object|string
     */
    public function view($view, $vars = array(), $return = false)
    {
        return $this->_ci_load(
            array('_ci_view' => $view,
                  '_ci_vars' => $this->_ci_object_to_array($vars),
                  '_ci_return' => $return
            )
        );
    }
    public function get($name)
    {
        $CI =& get_instance();
        if (isset($CI->$name)) {
            return $CI->$name;
        }
    }
    // --------------------------------------------------------------------

    /**
     * CI Autoloader
     *
     * Loads component listed in the config/autoload.php file.
     *
     * @used-by	CI_Loader::initialize()
     * @return	void
     */
    protected function _ci_autoloader()
    {
        // no support autoloader
    }
}
