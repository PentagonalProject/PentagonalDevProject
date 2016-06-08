<?php
abstract class CI_Module
{
    /**
     * @var string
     */
    protected $module_name = '';

    /**
     * @var null|string numeric
     */
    protected $module_version = null;

    protected $module_author = null;

    protected $module_description = null;

    protected $module_author_uri = null;

    protected $module_uri = null;

    /**
     * Class constructor
     *
     * @return	void
     */
    final public function __construct()
    {
        log_message('info', 'Module Class '.$this->getModuleName().' Initialized');
    }

    final public function getModuleName()
    {
        if (!is_string($this->module_name) || trim($this->module_name) == '') {
            $this->module_name = get_called_class();
        }
        return $this->module_name;
    }

    final public function getModuleVersion()
    {
        if (!is_string($this->module_version) && ! is_numeric($this->module_version)
            && !is_null($this->module_version)
        ) {
            $this->module_version = null;
        }
        return $this->module_version;
    }

    final public function getModuleAuthor()
    {
        if (!is_string($this->module_author) && !is_null($this->module_author)) {
            $this->module_author = null;
        }
        return $this->module_author;
    }

    final public function getModuleAuthorUri()
    {
        if (!is_string($this->module_author_uri) && !is_null($this->module_author_uri)) {
            $this->module_author_uri = null;
        }

        return $this->module_author_uri;
    }

    final public function getModuleUri()
    {
        if (!is_string($this->module_uri) && !is_null($this->module_uri)) {
            $this->module_uri = null;
        }

        return $this->module_uri;
    }

    final public function getModuleDescription()
    {
        if (!is_string($this->module_description) && !is_null($this->module_description)) {
            $this->module_description = null;
        }
        return $this->module_description;
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
