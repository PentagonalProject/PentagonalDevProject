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
    final public function __construct()
    {
        self::$instance =& $this;
        foreach (is_loaded() as $var => $class) {
            $this->$var =& load_class($class);
        }

        $this->load =& load_class('Loader', 'core');
        $this->{'module@list'} = array();
        $this->load->initialize();
        // load dependency
        $this->initLoadDependency();
        // call before mapping
        $this->beforeMapping();
        log_message('info', 'Controller Class Initialized');
    }

    final private function initLoadDependency()
    {
        /**
         * Always Load 2 helper for language and url
         */
        $this->load->helper(
            array(
                'language',
                'url',
            )
        );
        // load database
        $this->load->database();
        /**
         * Load model
         */
        $this->load->model('DatabaseTableModel', 'model.table');
        $this->load->model('DataModel', 'model.option');
        $this->load->model('AdminTemplateModel', 'model.template.admin');
        $this->load->model('TemplateModel', 'model.template.user');
        if ($this->load->get('router')->class == 'AdminController') {
            $template = $this
                ->load
                ->get('model.template.admin')
                ->init()
                ->getActiveTemplateDirectory();
            if ($template) {
                $this->load->setActiveTheme($template);
            }
        } else {
            $this->load->setActiveTheme(
                $this->load->get('model.template.user')->getActiveTemplateDirectory()
            );
        }
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
        return self::$instance;
    }

    /**
     * Instantiate Before Mapping
     */
    public function beforeMapping()
    {
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

        return isset($this->{'module@list'}[$name]) ? $this->{'module@list'}[$name] : null;
    }
}
