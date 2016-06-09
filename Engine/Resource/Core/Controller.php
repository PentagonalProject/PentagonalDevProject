<?php
/**
 * Class CI_Controller
 * @override CI_Controller
 */
class CI_Controller
{
    /**
     * Reference to the CI singleton
     *
     * @var object
     */
    private static $instance;

    private static $call = 0;

    /**
     * Class constructor override constructor
     *
     * @param bool $load_init
     * @return CI_Controller
     */
    final public function __construct($load_init = true)
    {
        /**
         * prevent module to print property
         * module controller could call get_instance();
         */
        if (self::$call > 1) {
            return;
        }

        self::$call++;

        if (!self::$instance) {
            self::$instance =& $this;
        }

        foreach (is_loaded() as $var => $class) {
            self::get_instance()->$var =& load_class($class);
        }

        $this->load =& load_class('Loader', 'core');
        $this->load->initialize();
        // load dependency
        $this->initLoadDependency();
        if ($load_init) {
            // remap
            foreach (self::$instance as $key => $value) {
                $this->$key = $value;
            }
            // call before mapping
            $this->beforeMapping();
            log_message('info', 'Controller Class Initialized');
        }
    }

    final private function initLoadDependency()
    {
        $CI =& get_instance();
        /**
         * Always Load 2 helper for language and url
         */
        $CI->load->helper(
            array(
                'language',
                'url',
            )
        );

        if (!isset($CI->{'module@list'})) {
            $CI->{'module@list'} = array();
        }

        $CI->load->database();
        /**
         * Load model
         */
        $CI->load->model('DatabaseTableModel', MODEL_NAME_TABLE);
        $CI->load->model('DataModel', MODEL_NAME_OPTION);
        $CI->load->model('AdminTemplateModel', MODEL_NAME_TEMPLATE_ADMIN);
        $CI->load->model('TemplateModel', MODEL_NAME_TEMPLATE_USER);
        $CI->load->model('NoticeRecord', MODEL_NAME_NOTICE);
        if (is_admin_area()) {
            $template = $CI
                ->load
                ->get(MODEL_NAME_TEMPLATE_ADMIN)
                ->init()
                ->getActiveTemplateDirectory();
            if ($template) {
                $CI->load->setActiveTemplate($template);
            }
        } else {
            $CI->load->setActiveTemplate(
                $CI->load->get(MODEL_NAME_TEMPLATE_USER)->getActiveTemplateDirectory()
            );
        }
    }

    /**
     * Before Mapping or call index
     */
    public function beforeMapping()
    {
    }

    // --------------------------------------------------------------------

    /**
     * Get the CI singleton
     *
     * @static
     * @return  object
     */
    final public static function &get_instance()
    {
        if (!self::$instance) {
            self::$instance = new self(false);
        }
        return self::$instance;
    }

    /**
     * Get mapping object loaded
     *
     * @param string $name
     * @return mixed|null
     */
    final public function getMapped($name)
    {
        if (is_string($name) && isset($this->$name)) {
            return $this->$name;
        }

        return null;
    }

    final public function __unset($name)
    {
        if ($name == 'module@list') {
            return;
        }
    }

    /**
     * Getting module
     *
     * @param string|null $name
     * @return mixed
     */
    final public function &getModule($name = null)
    {
        if ($name === null) {
            return $this->{'module@list'};
        }
        if (!is_string($name)) {
            return null;
        }
        $name = strtolower($name);
        $module = isset($this->{'module@list'}[$name]) ? $this->{'module@list'}[$name] : null;
        return $module;
    }
}
