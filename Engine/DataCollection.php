<?php
/**
 * Class DataCollection
 * @abstract
 */
abstract class DataCollection
{
    /**
     * Collection data
     *
     * @var array
     */
    protected $x_data___ = array();

    /**
     * Protected Collection Data
     * @var array
     */
    protected $x_protected_data___ = array();

    /**
     * DATA COLLECTION
     * @todo Help The Data cached save & some data could to be inserted here
     */

    /**
     * Getting all saved data
     *
     * @return array
     */
    final public function getAllData()
    {
        return $this->x_data___;
    }

    /**
     * @param string $index
     *
     * @return bool
     */
    final public function removeData($index)
    {
        if (is_array($index) || is_object($index)) {
            return false;
        }

        unset($this->x_data___[$index]);
        if (in_array($index, $this->x_protected_data___)) {
            $search = array_search($index, $this->x_protected_data___, true);
            unset($this->x_protected_data___[$search]);
        }
        return true;
    }

    /**
     * @param string $index
     *
     * @return bool
     */
    final public function hasData($index)
    {
        if (is_string($index) || is_int($index)) {
            return array_key_exists($index, $this->x_data___);
        }

        return false;
    }

    /**
     * Check if is on protected
     *
     * @param string $index
     *
     * @return bool
     */
    final public function isProtectedData($index)
    {
        if ($this->hasData($index)) {
            return in_array($index, $this->x_protected_data___);
        } elseif(in_array($index, $this->x_protected_data___)) {
            unset($this->x_protected_data___[$index]);
        }

        return false;
    }

    /**
     * @param string $index
     *
     * @return $this
     */
    final public function protectData($index = null)
    {
        if ($index === null && !$this->isProtectedData($index)) {
            $data_key = array_keys($this->x_data___);
            $index = end($data_key);
        }

        if ($this->hasData($index)) {
            $this->x_protected_data___[] = $index;
        }

        return $this;
    }

    /**
     * @param string $index
     * @param mixed  $value
     * @param bool   $protected
     *
     * @return $this
     */
    final public function setData($index, $value = null, $protected = false)
    {
        if (is_array($index)) {
            foreach ($index as $key => $val) {
                $this->x_data___[$key] = $val;
                if ($protected) {
                    $this->protectData($key);
                }
            }
            return $this;
        }
        if (!is_object($index)) {
            if (!$this->isProtectedData($index)) {
                $this->x_data___[$index] = $value;
                $protected && $this->protectData($index);
            }
        }
        return $this;
    }

    /**
     * @param string $index
     * @param mixed $default
     *
     * @return mixed
     */
    final public function getData($index, $default = null)
    {
        return $this->hasData($index)
            ? $this->x_data___[$index]
            : $default;
    }

    /**
     * Clear Collection Data
     */
    public function clearAll()
    {
        $this->x_data___ = array();
        $this->x_protected_data___ = array();
    }

    /**
     * Remove record
     *
     * @param string $name
     */
    public function __unset($name)
    {
        $this->removeData($name);
    }

    /**
     * Magic method Destruct
     */
    public function __destruct()
    {
        $this->clearAll();
    }
}
