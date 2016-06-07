<?php
/**
 * Class CI_Controller
 * override able controller
 */
class CI_Controller
{
    /**
     * Reference to the CI singleton
     *
     * @var object
     */
    private static $instance;

    /**
     * Class constructor override constructor
     *
     * @return  void
     */
    final public function __construct($load_init = true)
    {
        if (!self::$instance) {
            self::$instance =& $this;
        }
        foreach (is_loaded() as $var => $class) {
            $this->$var =& load_class($class);
        }
        $this->load            =& load_class('Loader', 'core');
        $this->load->initialize();
        // load dependency
        $this->initLoadDependency();
        if ($load_init) {
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
        $CI->load->model('DatabaseTableModel', 'model.table');
        $CI->load->model('DataModel', 'model.option');
        $CI->load->model('AdminTemplateModel', 'model.template.admin');
        $CI->load->model('TemplateModel', 'model.template.user');
        if ($CI->load->get('router') && $CI->load->get('router')->class == 'AdminController') {
            $template = $CI
                ->load
                ->get('model.template.admin')
                ->init()
                ->getActiveTemplateDirectory();
            if ($template) {
                $CI->load->setActiveTemplate($template);
            }
        } else {
            $CI->load->setActiveTemplate(
                $CI->load->get('model.template.user')->getActiveTemplateDirectory()
            );
        }
    }

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

    /**
     * Getting module
     *
     * @param string|null $name
     * @return mixed
     */
    final public function getModule($name = null)
    {
        if ($name === null) {
            return $this->{'module@list'};
        }
        if (!is_string($name)) {
            return null;
        }
        $name = strtolower($name);
        return isset($this->{'module@list'}[$name]) ? $this->{'module@list'}[$name] : null;
    }
}
