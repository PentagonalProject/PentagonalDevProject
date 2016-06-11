<?php
/**
 * Class DatabaseTableModel
 */

/** @noinspection PhpUndefinedClassInspection */
class DatabaseTableModel extends CI_Model
{
    protected $table_default = array(
        'option'  => array(
            'name' => 'options',
            'table' => array(
                'id',
                'options_name',
                'options_value',
                'options_autoload',
            )
        ),
        'user'    => array(
            'name' => 'users',
            'table' => array(
                'id',
                'username',
                'first_name',
                'last_name',
                'email',
                'password',
                'token',
                'date_create',
                'date_update',
                'status',
                'role',
            ),
        ),
        'session' => array(
            'name' => 'sessions',
            'table' => array(
                'id',
                'ip_address',
                'timestamp',
                'data',
            )
        ),
        'admin'   => array(
            'name' => 'admins',
            'table' => array(
                'id',
                'username',
                'first_name',
                'last_name',
                'email',
                'password',
                'token',
                'date_create',
                'date_update',
                'status',
                'role',
            ),
        ),
    );

    protected $table = array();

    public function __construct()
    {
        parent::__construct();
        $this->table = $this->table_default;
    }

    public function get($name)
    {
        if (is_string($name)) {
            return isset($this->table[$name]) ? $this->table[$name] : null;
        }
        return null;
    }

    public function getTableName($name)
    {
        if (is_string($name)) {
            $retval = $this->get($name);
            return !empty($retval['name']) ? $retval['name'] : null;
        }
        return null;
    }

    public function getDefault($name)
    {
        if (is_string($name)) {
            return isset($this->table_default[$name]) ? $this->table_default[$name] : null;
        }
        return null;
    }

    public function sanitize($key)
    {
        if (!is_string($key) || preg_match('/[^a-z0-9\_\-]/i', trim($key))) {
            return null;
        }

        return trim($key);
    }

    public function set($name, $value)
    {
        if (!is_string($name)) {
            trigger_error(
                __('Invalid key name for table definition.'),
                E_USER_NOTICE
            );
            return false;
        }
        if (!is_array($value) || empty($value['name'])
            || empty($value['table']) || !is_array($value['table'])
            || !is_string($value['name'])
        ) {
            return false;
        }

        if ($value['name'] = $this->sanitize($value['name'])) {
            $this->table[$name] = $value;
            return true;
        }

        return false;
    }

}
