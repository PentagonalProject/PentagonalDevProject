<?php
use Pentagonal\StaticHelper\PathHelper;

/**
 * Module loader & Validator
 *
 * Class CI_ModuleLoader
 */
final class CI_ModuleLoader
{
    /**
     * @const string
     */
    const MODULE_NAMESPACE = 'Module';

    /**
     * @const string
     */
    const MODULE_DIR = MODULEPATH;

    /**
     * @const string
     */
    const MODULE_OPTION = 'system.active.modules';

    /**
     * @var CI_ModuleLoader
     */
    private static $instance;

    /**
     * @var object
     */
    protected $ci;

    /**
     * @var array
     */
    protected static $loaded_module = array();

    /**
     * @var array
     */
    protected static $list_module = array();

    /**
     * @var array
     */
    protected static $list_invalid_module = array();

    /**
     * @var bool
     */
    protected static $hascall = false;

    /**
     * CI_ModuleLoader constructor.
     */
    public function __construct()
    {
        if (!self::$instance) {
            $this->ci =& get_instance();
            $this->ci->load =& load_class('Loader', 'Core');
            self::$instance =& $this;
            if (! class_exists('CI_Module', false)) {
                $app_path = RESOURCEPATH.'Core'.DS;
                if (file_exists($app_path.'Module.php')) {
                    /** @noinspection PhpIncludeInspection */
                    require_once($app_path . 'Module.php');
                    if (!class_exists('CI_Module', false)) {
                        throw new RuntimeException($app_path . "Module.php exists, but doesn't declare class CI_Module");
                    }
                } else {
                    throw new RuntimeException($app_path . "Module.php does not exists");
                }
            }
            $this->getPrivateModuleListAll();
        }
    }

    /**
     * Sanitize directory name of module
     *
     * @param string $string
     *
     * @return array|bool|string
     */
    private function sanitizeDirectoryKeyname($string)
    {
        $string = trim($string);
        if ($string == '' || $string[0] == '-' || $string[0] == '_'
            || preg_match('/[^a-z0-9\-\_]/i', $string)
            || strpos($string, '--') !== false
            || strpos($string, '__') !== false
        ) {
            return false;
        }

        $string = explode('-', $string);
        foreach ($string as $v) {
            if (stripos('abcdefghijklmnopqrstuvwzyz', $v[0]) === false) {
                return false;
            }
        }
        $string = implode('\\', $string);
        return $string;
    }

    /**
     * Get Lst of available modules
     *
     * @return array
     */
    private function getPrivateModuleListAll()
    {
        foreach((array) PathHelper::readDirList(MODULEPATH, 1) as $value) {
            $each = $this->testModuleFromFile($value);
            if (!is_array($each)) {
                self::$list_invalid_module[] = $value;
                continue;
            }
            $value = strtolower($value);
            self::$list_module[$value] = $each;
        }

        return self::$list_module;
    }

    /**
     * Test validity of module
     *
     * @param string $module
     *
     * @return array|bool
     */
    private function testModuleFromFile($module)
    {
        if (strpos($module, ' ') || trim($module) == ''
            || stripos('abcdefghijklmnopqrstuvwzyz', $module[0]) === false
        ) {
            return false;
        }
        if (is_file(MODULEPATH . $module . DS . $module . '.php')
            && is_readable(MODULEPATH . $module . DS . $module . '.php')
        ) {
            $file = realpath(MODULEPATH . $module . DS . $module . '.php');
            // sanitize
            $module_name = $this->sanitizeDirectoryKeyname($module);
            /**
             * Validate module
             */
            if ($file && is_string($module_name)) {
                // getting content of module files
                $container = @file_get_contents($file, null, null, 0 , 3048);
                $container = trim($container);
                /**
                 * Validate Module content
                 */
                if ($container || strtolower(substr($container, 0, 5)) == '<?php') {
                    // temporary modules for @uses to remove class Name
                    $the_modulename = '';
                    if (strpos('\\', $module_name)) {
                        $the_modulename = explode('\\', $module_name);
                        array_pop($the_modulename);
                        $the_modulename = '\\'.implode('\\', $the_modulename);
                    }
                    // full of class name
                    $namespace = self::MODULE_NAMESPACE . rtrim($the_modulename,'\\');
                    /**
                     * Validate within regex before include into core
                     */
                    if (preg_match('/namespace\s*'.preg_quote($namespace, '/').'\s*\;/i', $container)
                        && preg_match('/class\s*('.$module_name.')\s*extends\s*((?:\\\+)?CI_Module)/i', $container, $match)
                        && ! empty($match[1])
                    ) {
                        /**
                         * Check if uses valid extends
                         */
                        if (strpos($match[2], '\\') === false && !preg_match('/use\s*CI_Module/i', $container)) {
                            unset($container);
                            return false;
                        }
                        // freed
                        unset($container);

                        /**
                         * Returning valid of modules without include it into core
                         */
                        return array(
                            'namespace' => $namespace,
                            'class' => $match[1],
                            'classname' => '\\'.$namespace.'\\'.$match[1],
                            'file' => $file,
                            'active' => false
                        );
                    }
                }
                // freed
                unset($container);
            }
        }

        return false;
    }

    /**
     * Check if module is active
     *
     * @param string $moduleName
     *
     * @return bool
     */
    public function isModuleActive($moduleName)
    {
        if (!is_string($moduleName)) {
            return false;
        }
        $moduleName = strtolower($moduleName);
        return in_array($moduleName, self::$list_module) && !empty(self::$list_module[$moduleName]['active']);
    }

    /**
     * Activate the available of modules
     *
     * @return void
     */
    public function activateAvailableModule()
    {
        $option = $this->ci->load->get(MODEL_NAME_OPTION);
        $this->ci->load->model('NoticeRecord', MODEL_NAME_NOTICE);
        $record = $this->ci->load->get(MODEL_NAME_NOTICE);
        if ($option instanceof \DataModel) {
            // does not allow running twice or more
            if (self::$hascall) {
                return;
            }
            self::$hascall       = true;
            $active_module = $option->get(self::MODULE_OPTION, null);
            if (!is_array($active_module)) {
                $active_module = array();
                $option->set(self::MODULE_OPTION, $active_module);
                return;
            }
            if (!empty($active_module)) {
                $tmp_module = $active_module;
                foreach ($active_module as $key => $value) {
                    if (! is_string($value) || strlen($value) < 3 || ! $this->checkModule($value)) {
                        unset($active_module[$key]);
                        continue;
                    }
                    $value = strtolower($value);
                    $module = $this->activateModule($value);
                    if (!is_object($module) || ! $module instanceof CI_Module) {
                        if ($record instanceof \NoticeRecord) {
                            $record->set(
                                'error',
                                sprintf(
                                    'There was error on module `%s` is not a valid Module',
                                    $value
                                )
                            );
                        }
                        unset($active_module[$key]);
                        continue;
                    }
                }
                $active_module = array_values($active_module);
                if ($tmp_module !== $active_module) {
                    $option->set(self::MODULE_OPTION, $active_module);
                }
            }
        }
    }

    /**
     * Check the module if has listed on valid
     *
     * @param string $moduleName
     *
     * @return bool|mixed
     */
    public function checkModule($moduleName)
    {
        if (!is_string($moduleName)) {
            return false;
        }

        $moduleName = strtolower(trim($moduleName));
        return $moduleName && isset(self::$list_module[$moduleName])
            ? self::$list_module[$moduleName]
            : false;
    }

    /**
     * Get module active
     *
     * @param string $moduleName
     *
     * @return array|null  aray containes 'module' => object module, detailis on detail of module
     */
    public function getModule($moduleName)
    {
        $ci = & get_instance();
        $module =& $ci->getModule($moduleName);
        if (!empty($module) && ($the_module = $this->checkModule($moduleName))) {
            return array(
                'module' => $module,
                'detail' => $the_module,
            );
        }

        return null;
    }

    /**
     * Check if module has been loaded
     *
     * @param string $moduleName
     *
     * @return bool
     */
    public function isModuleLoaded($moduleName)
    {
        $module_ = $this->checkModule($moduleName);
        if (!$module_) {
            return false;
        }
        $moduleName = strtolower(trim($moduleName));
        if (!empty($module_['active']) && !empty($module_['classname'])) {
            if ($this->ci->getModule($moduleName) instanceof $module_['classname']) {
                self::$loaded_module[$moduleName] = true;
                return true;
            }
        }
        return false;
    }

    /**
     * Doing activate of available & valid module
     *
     * @param string $moduleName
     *
     * @return bool|mixed
     */
    public function activateModule($moduleName)
    {
        if ($this->isModuleLoaded($moduleName)) {
            return $this->ci->getModule($moduleName);
        }
        $option = $this->ci->load->get(MODEL_NAME_OPTION);
        $record = $this->ci->load->get(MODEL_NAME_NOTICE);
        $moduleName = strtolower(trim($moduleName));
        $module_ = self::$list_module[$moduleName];
        $options = array_keys(self::$loaded_module);
        if ($option instanceof \DataModel) {
            $options_ = $option->getFull(self::MODULE_OPTION);
            if (empty($options_['options_value']) || !empty($options_['options_autoload']) && $options_['options_autoload'] != 'yes') {
                empty($options_['options_value'])  && $options_ = array();
                $options_['options_value'] = !empty($options_['options_value']) && is_array($options_['options_value'])
                    ? $options_['options_value']
                    : array();
                $option->set(self::MODULE_OPTION, $options_['options_value'], 'yes');
            }
            $options_ = $options_['options_value'];
            $options = array_merge($options, $options_);
        }
        try {
            // check if modules has been required before
            if (!class_exists($module_['classname'])) {
                /** @noinspection PhpIncludeInspection */
                require_once $module_['file'];
            }
            $module = new $module_['classname']();
        } catch(Exception $e) {
            // remove modules
            $search_key = array_search($moduleName, $options, true);
            if ($search_key !== false) {
                unset($options[$search_key]);
            }
            // set options of module
            $option->set(self::MODULE_OPTION, $options);
            if ($record instanceof \NoticeRecord) {
                $record->set(
                    'error',
                    sprintf(
                        'There was error on module `%s` during activated with error : %s',
                        $moduleName,
                        (
                            "Message: " . $e->getMessage()
                            . "\n File: " . $e->getFile()
                            . "\n Line: " . $e->getLine()
                        )
                    )
                );
            }
            // redirect
            redirect(current_really_url());
            return false;
        }

        /**
         * Recheck if modules has instance of CI_Module
         */
        if ($module instanceof \CI_Module) {
            self::$list_module[$moduleName]['name']        = $module->getModuleName();
            self::$list_module[$moduleName]['uri']         = $module->getModuleUri();
            self::$list_module[$moduleName]['author']      = $module->getModuleAuthor();
            self::$list_module[$moduleName]['version']     = $module->getModuleVersion();
            self::$list_module[$moduleName]['description'] = $module->getModuleDescription();
            self::$list_module[$moduleName]['author_uri'] = $module->getModuleAuthorUri();
            self::$list_module[$moduleName]['description'] = $module->getModuleDescription();
            self::$list_module[$moduleName]['active']      = true;
            self::$loaded_module[$moduleName]              = true;
            $options_ = $options;
            $options[]                                     = $moduleName;
            $options = array_unique($options);
            $options = array_values($options);
            // add into CI core
            $this->ci->{'module@list'}[$moduleName] = $module;
            if ($options_ !== $options) {
                $option->set(self::MODULE_OPTION, $options);
            }
            return $module;
        }

        $search_key = array_search($moduleName, $options, true);
        if ($search_key) {
            unset($options[$search_key]);
            $option->set(self::MODULE_OPTION, $options);
        }

        return false;
    }

    /**
     * Get allavailable modules even it has not been activated
     *
     * @return array
     */
    public function getAllModule()
    {
        return self::$list_module;
    }

    /**
     * getting invalid modules even it not valid
     *
     * @return array
     */
    public function getInvalidModule()
    {
        return self::$list_invalid_module;
    }

    /**
     * get active Modules
     *
     * @return array
     */
    public function getActiveModule()
    {
        $modules = array();
        foreach ($this->getAllModule() as $key => $value) {
            if (isset(self::$loaded_module[$key]) && empty($value['active'])) {
                $modules[$key] = $value;
            }
        }

        return $modules;
    }

    /**
     * Get module info
     *
     * @param string $moduleName
     *
     * @return bool|mixed
     */
    public function getInfo($moduleName)
    {
        return $this->checkModule($moduleName);
    }

    /**
     * @return CI_ModuleLoader
     */
    public function getInstance()
    {
        (!self::$instance) && new self();
        return self::$instance;
    }

}
