<?php
class CI_Model extends DataCollection
{
    /**
     * Class constructor
     *
     * @return	void
     */
    public function __construct()
    {
        log_message('info', 'Model Class Initialized');
    }

    /**
     * __get magic
     *
     * Allows models to access CI's loaded classes using the same
     * syntax as controllers.
     *
     * @param	string	$key
     */
    public function __get($key)
    {
        return get_instance()->$key;
    }
}
