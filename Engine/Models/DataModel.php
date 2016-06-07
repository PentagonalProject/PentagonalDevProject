<?php
use Pentagonal\StaticHelper\StringHelper;

class DataModel extends CI_Model
{
    protected $ci;
    protected $table;
    protected $cached_record = array();
    protected $temporary_data = array();

    public function __construct()
    {
        parent::__construct();
        $this->ci =& get_instance();
        $this->ci->load->model('DatabaseTableModel', 'model.table');
        $this->table = $this->ci->{'model.table'}->getDefault('option');
        $record = $this->ci->db->get_where(
            $this->table['name'],
            array(
                'LOWER(options_autoload)' => 'yes'
            )
        )->result_array();
        foreach ($record as $array) {
            $array['options_value'] = StringHelper::maybeUnserialize($array['options_value']);
            $this->cached_record[$array['options_name']] = $array;
        }
    }

    public function has($name)
    {
        $time = microtime();
        $retval = $this->get($name, $time);
        return $retval !== $time;
    }

    public function get($name, $default = null, $force = false, $allow_temporary = true)
    {
        if (!is_string($name)) {
            return $default;
        }

        $name = trim($name);
        if (!isset($this->cached_record[$name]) || $force) {
            $this->cached_record[$name] = $this->ci->db->get_where(
                $this->table['name'],
                array(
                    'options_name' => $name
                )
            )->row(0, 'array');
            if (isset($this->cached_record[$name]['options_value'])) {
                $this->cached_record[$name]['options_value'] =
                    StringHelper::maybeUnserialize($this->cached_record[$name]['options_value']);
            }
        }

        if (empty($this->cached_record[$name])
            || ! array_key_exists('options_value', $this->cached_record[$name])
        ) {
            // set again
            $this->cached_record[$name] = array();
            if ($allow_temporary && isset($this->temporary_data[$name])) {
                return $this->temporary_data[$name];
            }

            return $default;
        }

        return $this->cached_record[$name]['options_value'];
    }

    public function getFull($name, $default = null, $force = false)
    {
        $name = trim($name);
        $time = microtime();
        if ($this->get($name, $time, $force, false) === $time) {
            return $default;
        }

        return $this->cached_record[$name];
    }

    /**
     * Update option
     *
     * @param string $name
     * @param mixed $value
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

        if (! is_null($value) && ! is_string($value) && ! is_bool($value)) {
            $value = StringHelper::maybeSerialize($value);
        }

        $name = trim($name);
        $autoload = $autoload === true ? 'yes' : $autoload;
        $status = $this->getFull($name, null, false);
        if ($status === null) {
            $autoload = $autoload != 'yes' ? 'no' : 'yes';
            $result = $this->ci->db->insert(
                $this->table['name'],
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
            $result = $this->ci->db->update(
                $this->table['name'],
                array(
                    'options_name'     => $name,
                    'options_value'    => $value,
                    'options_autoload' => $autoload,
                ),
                array(
                    'id' => $status['id']
                )
            );
            $this->cached_record[$name] = array(
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

    public function temporary($name, $value)
    {
        if (!is_string($name)) {
            return null;
        }
        $name = trim($name);
        $this->temporary_data[$name] = $value;

        return true;
    }

    public function getTemporary($name)
    {
        if (!is_string($name)) {
            return null;
        }
        $name = trim($name);
        return isset($this->temporary_data[$name]) ? $this->temporary_data[$name] : null;
    }

    public function __get($name)
    {
        return $this->get($name);
    }
}
