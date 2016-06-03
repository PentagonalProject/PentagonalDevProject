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
    public function __construct()
    {
        self::$instance =& $this;
        foreach (is_loaded() as $var => $class) {
            $this->$var =& load_class($class);
        }

        $this->load =& load_class('Loader', 'core');
        $this->{'module@list'} = array();
        $this->load->initialize();
        log_message('info', 'Controller Class Initialized');
    }

    // --------------------------------------------------------------------

    /**
     * Get the CI singleton
     *
     * @static
     * @return	object
     */
    public static function &get_instance()
    {
        return self::$instance;
    }

    public function getModule($name = null)
    {
        if ($name === null) {
            return $this->{'module@list'};
        }
        $retval = isset($this->{'module@list'}[$name]) ? $this->{'module@list'}[$modules] : null;
        return $retval;
    }
}
