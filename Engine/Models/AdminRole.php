<?php
class AdminRole extends CI_Model
{
    /**
     * @var array
     */
    protected $core_role = array(
        'administrator',
        'admin',
        'editor',
    );

    /**
     * Role Name ext on Database
     */
    const ROLE_NAME_DB = 'role';

    /**
     * @var array
     */
    protected $curent_user;

    /**
     * @var array
     */
    protected $_default_core_list_role = array(
        // template
        'manage_template'  => false,
        'upload_template'  => false,
        'delete_template'  => false,
        'edit_template'    => false,
        'switch_template'  => false,
        // module
        'manage_module'     => false,
        'upload_module'     => false,
        'delete_module'     => false,
        'edit_module'       => false,
        'deactivate_module' => false,
        'activate_module'   => false,
        // user
        'manage_user'  => false,
        'add_user'     => false,
        'edit_user'    => false,
        'delete_user'  => false,
        // file
        'manage_attachment'  => false,
        'upload_attachment'  => false,
        'delete_attachment'  => false,
    );

    /**
     * @var array
     */
    protected $list_role = array();

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * get super admin role name
     *
     * @return string
     */
    public function superAdminRoleName()
    {
        return 'administrator';
    }

    /**
     * get Admin role name
     *
     * @return string
     */
    public function adminRoleName()
    {
        return 'admin';
    }

    /**
     * If current user is super admin
     *
     * @return bool
     */
    public function isSuperAdmin()
    {
        return $this->is('administrator');
    }

    /**
     * if current user is admin or administrator
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->isSuperAdmin() || $this->is('admin');
    }

    /**
     * check if on current role
     *
     * @param string $name
     *
     * @return bool
     */
    public function is($name)
    {
        return $name === $this->getUserRole();
    }

    /**
     * @return bool|string
     */
    public function getUserRole()
    {
        static $role;
        if (isset($role)) {
            return $role;
        }
        $role = $this->current_user
                && !empty($this->curent_user[self::ROLE_NAME_DB])
                && is_string($this->curent_user[self::ROLE_NAME_DB])
                // unknown never allowed
                && strtolower(trim($this->curent_user[self::ROLE_NAME_DB])) != 'unknown'
            ? trim(strtolower($this->curent_user[self::ROLE_NAME_DB]))
            : false;

        return $role;
    }

    /**
     * Initialize
     *
     * @return $this
     */
    public function initialize()
    {
        static $hascall;
        if ($hascall) {
            return $this;
        }

        $hascall = true;
        // set default list role
        $this->list_role = $this->_default_core_list_role;

        /**
         * Set super admin allow all
         */
        if ($this->isSuperAdmin()) {
            foreach ($this->list_role as $key => $v) {
                $this->list_role[$key] = true;
            }
        } elseif ($thi->isAdmin()) {
            $this->list_role['manage_template'] = true;
            $this->list_role['manage_module'] = true;
            $this->list_role['manage_user'] = true;
        }

        return $this;
    }

    /**
     * Check if list role has contain
     *
     * @param string $name
     *
     * @return bool
     */
    public function containRole($name)
    {
        if (!is_string($name)) {
            return false;
        }
        $name = trim(strtolower($name));
        return array_key_exists($name, $this->list_role);
    }

    /**
     * Add role to access
     *
     * @param string $name
     * @param bool $default_value
     *
     * @return bool|int   boolean true if added role | integer 1 if exists
     */
    public function addRoleAccessName($name, $default_value = false)
    {
        if (!is_string($name)) {
            return false;
        }

        if (!$this->containRole($name)) {
            return 1;
        }

        $name = trim(strtolower($name));
        $default_value = boolval($default_value);
        $this->list_role[$name] = ($this->isSuperAdmin() ? true : $default_value);

        return true;
    }

    /**
     * Remove role access
     *
     * @param string $name
     *
     * @return bool
     */
    public function removeAccessRole($name)
    {
        if (!is_string($name) || !$this->containRole($name)) {
            return false;
        }
        $name = trim(strtolower($name));
        if (array_key_exists($name, $this->_default_core_list_role)) {
            return false;
        }

        unset($this->list_role[$name]);
        return true;
    }

    /**
     * Set allowed access
     *
     * @param string $name
     *
     * @return bool
     */
    public function allow($name)
    {
        // always allowed
        if ($this->isSuperAdmin()) {
            return true;
        }
        if ($this->containRole($name)) {
            $name = strtolower(trim($name));
            $this->list_role[$name] = true;
            return true;
        }

        return false;
    }

    /**
     * Set Allowed Access
     *
     * @param string $name
     *
     * @return bool
     */
    public function disallow($name)
    {
        // always allowed
        if ($this->isSuperAdmin()) {
            return false;
        }
        if ($this->containRole($name)) {
            $name = strtolower(trim($name));
            $this->list_role[$name] = false;
            return true;
        }

        return false;
    }

    /**
     * Check if has capability
     *
     * @param string $name
     *
     * @return bool
     */
    public function can($name)
    {
        // super admin always allowed
        if ($this->isSuperAdmin()) {
            return true;
        }
        if (!is_string($name) || trim($name) == '') {
            return false;
        }

        $role = trim(strtolower($name));
        return !empty($this->list_role[$role]);
    }
}
