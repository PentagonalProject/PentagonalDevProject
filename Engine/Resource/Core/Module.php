<?php
class CI_Module
{
    /**
     * Class constructor
     *
     * @return	void
     */
    final public function __construct()
    {
        log_message('info', 'Module Class '.get_class($this).' Initialized');
    }

    /**
     * @param string|null $name
     * @return mixed
     */
    final public function getModule($name = null)
    {
        return get_instance()->getModule($name);
    }

    /**
     * Call before Route Initiate
     * as initial Module called
     */
    public function initial()
    {
    }

    /**
     * __get magic
     *
     * Allows models to access CI's loaded classes using the same
     * syntax as controllers.
     *
     * @param	string	$key
     */
    final public function __get($key)
    {
        return get_instance()->$key;
    }
}
