<?php
class NoticeRecord
{

    const TABLE_NOTICE = 'system.notice.list';

    protected static $notices = array();
    protected $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->model('DataModel', MODEL_NAME_OPTION);
        $this->ci->load->helper('language');
        $this->initNotices();
    }

    private function initNotices()
    {
        $option = $this->ci->load->get(MODEL_NAME_OPTION);
        if ($option instanceof \DataModel) {
            $options = $option->getFull(self::TABLE_NOTICE);
            if (!empty($options['options_autoload']) && $options['options_autoload'] != 'yes') {
                $option->set(self::TABLE_NOTICE, array(), 'yes');
            }
            $options = isset($options['options_value']) ? $options['options_value'] : null;
            $old_options = $options;
            if (!is_array($options)) {
                $option->set(self::TABLE_NOTICE, array(), 'yes');
            } elseif (!empty($options)) {
                foreach ($options as $key => $value) {
                    if (!is_string($key) || ! trim($key) || !is_array($value)) {
                        unset($options[$key]);
                        continue;
                    }
                    $opt = array();
                    foreach ($value as $k => $v) {
                        if (!is_string($v)) {
                            unset($value[$k]);
                            continue;
                        }
                        $opt[] = $v;
                    }
                    $options[$key] = $opt;
                }
                self::$notices = $options;
                if (count(self::$notices) || ! empty($old_options)) {
                    // reset it
                    $option->set(self::TABLE_NOTICE, array(), 'yes');
                }
            }
        }
    }

    /**
     * Get all notices
     *
     * @return array
     */
    public function getAll()
    {
        return self::$notices;
    }

    public function get($type)
    {
        if (!is_string($type)) {
            return null;
        }
        $type = trim(strtolower($type));
        return $type != '' && isset(self::$notices[$type]) ? self::$notices[$type] : null;
    }

    public function set($type, $value)
    {
        if (!is_string($type) || trim($type) == '') {
            return false;
        }
        $type = trim(strtolower($type));
        $option = $this->ci->load->get(MODEL_NAME_OPTION);
        if ($option instanceof \DataModel) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    if (!is_string($v)) {
                        continue;
                    }
                    $this->set($type, $v);
                }
                return true;
            }
            // reset it
            $options = $option->get(self::TABLE_NOTICE);
            if (! is_array($options)) {
                $options = array();
                $empty = true;
            }
            if (! isset($options[$type]) || ! is_array($options[$type])) {
                $options[$type] = array();
                $empty = true;
            }
            if (!isset(self::$notices[$type]) || !is_array(self::$notices[$type])) {
                self::$notices[$type] = array();
                $empty_2 = true;
            }

            self::$notices[$type][] = $value;
            if (isset($empty) && isset($empty_2)) {
                $option->set(self::TABLE_NOTICE, self::$notices, 'yes');
                return true;
            }

            $options[$type][] = $value;
            //prevent duplicates
            $options[$type] = array_unique($options[$type]);
            $option->set(self::TABLE_NOTICE, $options, 'yes');
            return true;
        }

        return false;
    }

    public function clearNotice($type)
    {
        if (!is_string($type) || trim($type) == '') {
            return false;
        }
        $type = trim(strtolower($type));
        if (isset(self::$notices[$type])) {
            self::$notices[$type] = array();
            $option = $this->ci->load->get(MODEL_NAME_OPTION);
            if ($option instanceof \DataModel) {
                $option->set(self::TABLE_NOTICE, self::$notices, true);
            }
        }
        return true;
    }
}
