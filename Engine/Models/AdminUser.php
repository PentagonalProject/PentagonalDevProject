<?php
/**
 * Class AdminUser
 */
use \Pentagonal\StaticHelper\FilterHelper;

/** @noinspection PhpUndefinedClassInspection */
class AdminUser extends CI_Model
{
    protected $table;

    protected $table_name;

    protected $ci;

    protected $max_length = 64;

    protected $min_length = 3;

    protected $user_list = array();

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
                    sprintf(__('Model %s does not load correctly.'), 'Table')
                )
            );
        }
        $this->table_name = $this->table['name'];
    }

    /**
     * @param string $username
     *
     * @return bool|string boolean false if invalid username
     */
    public function sanitizeUsername($username)
    {
        $username = FilterHelper::filterForUsername($username, $this->max_length, $this->min_length);
        return $username;
    }

    public function getDataByUsername($username, $force = false)
    {
        $username = $this->sanitizeUsername($username);
        if (!$username) {
            return null;
        }
        if (isset($this->user_list[$username]) && ! $force) {
            return $this->user_list[$username];
        }
        $user = $this->ci->db->get_where(
            $this->table_name,
            array(
                'LOWER(`username`)' => $username
            ),
            1
        )->row(0, 'array');

        if (empty($user)) {
            $this->user_list[$username] = false;
            return false;
        }
        $this->user_list[$username] = $user;
        return $user;
    }

    public function getDataByEmail($email, $force = false)
    {
    }

    public function exist($username)
    {
        $user = $this->getDataByUsername($username);
        return is_array($user) ? true : $user;
    }

    public function createUser($username, array $property)
    {
        $exist = $this->exist($username);
        if ($exist === null) {
            trigger_error('Invalid username or username does not valid', E_USER_NOTICE);
            return null;
        }
        if ($exist) {
            return false;
        }
        $default = array(
            'username' => $username,
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'password' => '',
            'status' => 'pending',
            'role' => 'unknown',
        );
        $property = array_merge($default, $property);
        foreach ($property as $key => $value) {
            if (!isset($default[$key]) && $key != 'username') {
                unset($property[$key]);
                continue;
            }
            if (!is_string($value)) {
                $property[$key] = $default[$key];
            }
        }
        $property['status'] = strtolower($property['status']);
        $property['role'] = strtolower($property['role']);
        $property['username'] = $username;
        // create temporary token
        $property['token'] = sha1($username.microtime(true)).sha1(serialize($property));
        $this->ci->db->insert($this->table_name, $property);
        $this->getDataByUsername($username, true);
        return $this->exist($username);
    }
}
