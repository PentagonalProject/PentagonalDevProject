<?php
use Pentagonal\StaticHelper\PathHelper;

final class CI_ModuleLoader
{
    const MODULE_NAMESPACE = 'Module';

    const MODULE_DIR = MODULEPATH;

    const MODULE_OPTION = 'system.active.modules';

    private static $instance;

    protected $ci;

    protected static $loaded_module = array();

    protected static $list_module = array();

    protected static $list_invalid_module = array();

    protected static $error = array();

    protected static $hascall = false;
    /**
     * CI_ModuleLoader constructor.
     */
    public function __construct()
    {
        if (!self::$instance) {
            $this->ci =& get_instance();
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
        $string = implode('\\', $string);
        return $string;
    }

    private function getPrivateModuleListAll()
    {
        foreach(PathHelper::readDirList(MODULEPATH, 1) as $value) {
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

    private function testModuleFromFile($module)
    {
        if (strpos($module, ' ')) {
            return false;
        }
        if (is_file(MODULEPATH . $module . DS . $module . '.php')
            && is_readable(MODULEPATH . $module . DS . $module . '.php')
        ) {
            $file = realpath(MODULEPATH . $module . DS . $module . '.php');
            $module_name = $this->sanitizeDirectoryKeyname($module);
            if ($file && is_string($module_name)) {
                $container = @file_get_contents($file, null, null, 0 , 3048);
                $container = trim($container);
                if ($container || strtolower(substr($container, 0, 5)) == '<?php') {
                    $the_modulename = '';
                    if (strpos('\\', $module_name)) {
                        $the_modulename = explode('\\', $module_name);
                        array_pop($the_modulename);
                        $the_modulename = '\\'.implode('\\', $the_modulename);
                    }
                    $namespace = self::MODULE_NAMESPACE . rtrim($the_modulename,'\\');
                    if (preg_match('/namespace\s*'.preg_quote($namespace, '/').'\s*\;/i', $container)
                        && preg_match('/class\s*('.$module_name.')\s*extends\s*((?:\/+)?CI_Module)/i', $container, $match)
                        && ! empty($match[1])
                    ) {
                        if (strpos($match[2], '\\') === false && !preg_match('/use\s*CI_Module/i', $container)) {
                            unset($container);
                            return false;
                        }
                        unset($container);
                        return array(
                            'namespace' => $namespace,
                            'class' => $match[1],
                            'classname' => '\\'.$namespace.'\\'.$match[1],
                            'file' => $file,
                            'active' => false
                        );
                    }
                }
                unset($container);
            }
        }

        return false;
    }

    public function isModuleActive($moduleName)
    {
        if (!is_string($moduleName)) {
            return false;
        }
        $moduleName = strtolower($moduleName);
        return in_array($moduleName, self::$list_module) && !empty(self::$list_module[$moduleName]['active']);
    }

    public function activateAvailableModule()
    {
        $option = $this->ci->load->get('model.option');
        $this->ci->load->model('NoticeRecord', 'model.notice');
        $record = $this->ci->load->get('model.notice');
        if ($option instanceof \DataModel) {
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
                    if (! is_string($value) || ! $this->checkModule($value)) {
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

    public function checkModule($moduleName)
    {
        if (!is_string($moduleName)) {
            return false;
        }

        $moduleName = strtolower(trim($moduleName));
        return $moduleName && !isset(self::$list_module[$moduleName])
            ? self::$list_module[$moduleName]
            : false;
    }

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

    public function activateModule($moduleName)
    {
        if ($this->isModuleLoaded($moduleName)) {
            return $this->ci->getModule($moduleName);
        }

        $option = $this->ci->load->get('model.option');
        $record = $this->ci->load->get('model.notice');

        $moduleName = strtolower(trim($moduleName));
        $module_ = self::$list_module[$moduleName];
        $options = array_keys(self::$loaded_module);

        if ($option instanceof \DataModel) {
            $options_ = $option->get(self::MODULE_OPTION);
            if (!is_array($options_)) {
                $options_ = array();
            }
            $options = array_merge($options, $options_);
        }
        try {
            if (!class_exists($module_['classname'])) {
                /** @noinspection PhpIncludeInspection */
                require_once $module_['file'];
            }
            $module = new $module_['classname']();
        } catch(Exception $e) {
            $search_key = array_search($moduleName, $options, true);
            if ($search_key !== false) {
                unset($options[$search_key]);
            }
            $option->set(self::MODULE_NAMESPACE, $options);
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

            redirect(current_really_url());
            return false;
        }

        if ($module instanceof CI_Module) {
            self::$list_module[$moduleName]['name']        = $module->getModuleName();
            self::$list_module[$moduleName]['uri']         = $module->getModuleUri();
            self::$list_module[$moduleName]['author']      = $module->getModuleAuthor();
            self::$list_module[$moduleName]['version']     = $module->getModuleVersion();
            self::$list_module[$moduleName]['description'] = $module->getModuleDescription();
            self::$list_module[$moduleName]['author_uri'] = $module->getModuleAuthorUri();
            self::$list_module[$moduleName]['description'] = $module->getModuleDescription();
            self::$list_module[$moduleName]['active']      = true;
            self::$loaded_module[$moduleName]              = true;
            $options[]                                     = $moduleName;
            $this->ci->{'module@list'}[$moduleName] = $module;
            $option->set(self::MODULE_NAMESPACE, $options);
            return $module;
        }

        $search_key = array_search($moduleName, $options, true);
        if ($search_key) {
            unset($options[$search_key]);
            $option->set(self::MODULE_NAMESPACE, $options);
        }

        return false;
    }

    public function getAllModule()
    {
        return self::$list_module;
    }

    public function getInvalidModule()
    {
        return self::$list_invalid_module;
    }

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
