<?php

/**
 * Class CI_Controller
 * overrideable controller
 */
class CI_Controller
{

    /**
     * Reference to the CI singleton
     *
     * @var	object
     */
    private static $instance;

    /**
     * Class constructor override constructor
     *
     * @return	void
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
        $this->load->helper(
            array(
                'language'
            )
        );
        log_message('info', 'Controller Class Initialized');
    }

    // --------------------------------------------------------------------

    /**
     * Get the CI singleton
     *
     * @static
     * @return	object
     */
    final public static function &get_instance()
    {
        return self::$instance;
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
