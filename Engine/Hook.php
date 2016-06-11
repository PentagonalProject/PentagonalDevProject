<?php
use Pentagonal\Hookable\Hookable;

/**
 * Hook able Instance
 */
class Hook
{
    /**
     * @var Hookable
     */
    private static $hook;

    /**
     * @var Hook
     */
    private static $instance;

    /**
     * Hook constructor.
     */
    public function __construct()
    {
        if (!self::$instance) {
            self::$instance =& $this;
            self::$hook = new Hookable();
        }
    }

    /**
     * @return Hook
     */
    public static function getInstance()
    {
        (!self::$instance) && new self();
        return self::$instance;
    }

    /**
     * @param $name string
     * @param $arguments array
     * @return bool|mixed
     */
    public function __call($name, array $arguments)
    {
        $Instance = self::getInstance();
        if (method_exists($Instance::$hook, $name)) {
            return call_user_func_array(array($Instance::$hook, $name), $arguments);
        }
        return trigger_error(sprintf('Call to undefined method %s', $name), E_USER_ERROR);
    }

    /**
     * @param $name string
     * @param array $arguments
     * @return bool|mixed
     */
    public static function __callStatic($name, array $arguments)
    {
        return self::getInstance()->__call($name, $arguments);
    }

    /**
     * PHP5 Magic Method
     */
    public function __destruct()
    {
        self::$hook = null;
    }
}
