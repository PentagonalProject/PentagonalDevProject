<?php
use Pentagonal\StaticHelper\StringHelper;

/**
 * Class DataModel
 */

/** @noinspection PhpUndefinedClassInspection */
class DataModel extends CI_Model
{
    /** @noinspection PhpUndefinedClassInspection */
    /**
     * @var CI_Controller
     */
    protected $ci;

    /**
     * @var array
     */
    protected $table;

    /**
     * @var string
     */
    protected $table_name;

    /**
     * @var array
     */
    protected $temporary_data = array();

    /**
     * DataModel constructor.
     */
    public function __construct()
    {
        /** @noinspection PhpUndefinedClassInspection */
        parent::__construct();
        $this->ci =& get_instance();
        $this->ci->load->model('DatabaseTableModel', MODEL_NAME_TABLE);
        $this->ci->load->database();
        $this->table = $this->ci->{MODEL_NAME_TABLE}->getDefault('option');
        if (empty($this->table['name'])) {
            show_error(
                array(
                    __('There was an error.'),
                    sprintf(__('Model %s does not load correctly.'), 'Table')
                )
            );
        }
        $this->table_name = $this->table['name'];
        /** @noinspection PhpUndefinedFieldInspection */
        $record = $this->ci->db->get_where(
            $this->table_name,
            array(
                'LOWER(options_autoload)' => 'yes'
            )
        )->result_array();
        foreach ($record as $array) {
            $array['options_value'] = StringHelper::maybeUnserialize($array['options_value']);
            $this->setData($array['options_name'], $array);
        }
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        $time = microtime();
        $retval = $this->get($name, $time);
        return $retval !== $time;
    }

    /**
     * Get option value
     *
     * @param string $name
     * @param null   $default
     * @param bool   $force
     * @param bool   $allow_temporary false to use original even temporrary data exists
     *
     * @return mixed|null
     */
    public function get($name, $default = null, $force = false, $allow_temporary = true)
    {
        if (!is_string($name)) {
            return $default;
        }

        $name = trim($name);
        if (!isset($this->x_data___[$name]) || $force) {
            $this->x_data___[$name] = $this->ci->db->get_where(
                $this->table_name,
                array(
                    'options_name' => $name
                )
            )->row(0, 'array');
            if (isset($this->x_data___[$name]['options_value'])) {
                $this->x_data___[$name]['options_value'] =
                    StringHelper::maybeUnserialize($this->x_data___[$name]['options_value']);
            }
        }

        if (empty($this->x_data___[$name])
            || ! array_key_exists('options_value', $this->x_data___[$name])
        ) {
            // set again
            $this->x_data___[$name] = array();
            if ($allow_temporary && isset($this->temporary_data[$name])) {
                return $this->temporary_data[$name];
            }

            return $default;
        }

        return $this->x_data___[$name]['options_value'];
    }

    /**
     * Get full data from options
     *
     * @param string $name
     * @param null   $default
     * @param bool   $force
     *
     * @return mixed|null
     */
    public function getFull($name, $default = null, $force = false)
    {
        $name = trim($name);
        $time = microtime();
        if ($this->get($name, $time, $force, false) === $time) {
            return $default;
        }

        return $this->x_data___[$name];
    }

    /**
     * Update option
     *
     * @param string      $name
     * @param mixed       $value
     * @param bool|string $autoload
     *
     * @return bool
     */
    public function update($name, $value, $autoload = null)
    {
        return $this->set($name, $value, $autoload);
    }

    /**
     * Set Option Value
     *
     * @param string           $name     options name
     * @param mixed            $value    options value
     * @param string|bool|null $autoload the autoload
     * @return bool
     */
    public function set($name, $value, $autoload = null)
    {
        if (!is_string($name)) {
            return null;
        }

        /**
         * Prevent Save closure data
         */
        if ($value instanceof Closure) {
            trigger_error('Could not save closure data', E_USER_NOTICE);
            return null;
        }
        /**
         * if is and object convert all object to std class
         */
        if (is_object($value)) {
            $std = new \stdClass();
            foreach (get_object_vars($value) as $key => $v) {
                $std->{$key} = $v;
            }
            $value = $std;
        } elseif (! is_string($value) && !is_null($value)) {
            // all except string & null will be serialize even if as boolean
            // to make sure getting data as the real value
            $value = serialize($value);
        }

        $name = trim($name);
        $autoload = $autoload === true ? 'yes' : $autoload;
        $status = $this->getFull($name, null, false);
        if ($status === null) {
            $autoload = $autoload != 'yes' ? 'no' : 'yes';
            $result = $this->ci->db->insert(
                $this->table_name,
                array(
                    'options_name'     => $name,
                    'options_value'    => $value,
                    'options_autoload' => $autoload,
                )
            );
            $this->getFull($name, null, true);
        } else {
            $autoload = ! is_string($autoload) || ! in_array(strtolower($autoload), array('yes', 'no'))
                ? $status['options_autoload']
                : $autoload;
            $autoload = ! in_array(strtolower($autoload), array('yes', 'no'))
                ? 'no'
                : strtolower($autoload);
            if ($autoload == $status['options_autoload'] && $value == $status['options_value']) {
                return true;
            }
            $result                 = $this->ci->db->update(
                $this->table_name,
                array(
                    'options_name'     => $name,
                    'options_value'    => $value,
                    'options_autoload' => $autoload,
                ),
                array(
                    'id' => $status['id']
                )
            );
            $this->x_data___[$name] = array(
                'id' => $status['id'],
                'options_name'     => $name,
                'options_value'    => $value,
                'options_autoload' => $autoload,
            );
        }

        /**
         * Temporary Result
         */
        return $result;
    }

    /**
     * Set Temporary Data only without affecting database
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return bool|null
     */
    public function temporary($name, $value)
    {
        if (!is_string($name)) {
            return null;
        }
        $name = trim($name);
        $this->temporary_data[$name] = $value;

        return true;
    }

    /**
     * Getting temporary setted data
     *
     * @param string $name
     *
     * @return mixed|null
     */
    public function getTemporary($name)
    {
        if (!is_string($name)) {
            return null;
        }
        $name = trim($name);
        return isset($this->temporary_data[$name]) ? $this->temporary_data[$name] : null;
    }

    /**
     * Magic Method
     *
     * @param string $name
     *
     * @return mixed|null
     */
    public function __get($name)
    {
        return $this->get($name);
    }
}
