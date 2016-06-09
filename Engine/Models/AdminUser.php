<?php
/**
 * Class AdminUser
 */

/** @noinspection PhpUndefinedClassInspection */
class AdminUser extends CI_Model
{
    protected $table;

    protected $table_name;

    protected $ci;

    public function __construct()
    {
        /** @noinspection PhpUndefinedClassInspection */
        parent::__construct();
        $this->ci =& get_instance();
        $this->table = $this
            ->ci
            ->load
            ->get(MODEL_NAME_TABLE)
            ->getDefault('admin');
        if (empty($this->table['name'])) {
            show_error(
                array(
                    __('There was an error.'),
                    __('Model Table does not load correctly.')
                )
            );
        }
        $this->table_name = $this->table['name'];
    }
}
