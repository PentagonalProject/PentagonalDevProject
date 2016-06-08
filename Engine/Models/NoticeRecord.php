<?php
class NoticeRecord
{

    const TABLE_NOTICE = 'system.notice.list';

    protected static $notices = array();
    protected $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->model('DataModel', 'model.option');
        $this->ci->load->helper('language');
        $this->initNotices();
    }

    private function initNotices()
    {
        $option = $this->ci->load->get('model.option');
        if ($option instanceof \DataModel) {
            $options = $option->get(self::TABLE_NOTICE);
            if (!is_array($options)) {
                $option->set(self::TABLE_NOTICE, array());
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
                self::$notices = array_merge(self::$notices, $options);
                // reset it
                $option->set(self::TABLE_NOTICE, array());
            }
        }
    }

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
        $option = $this->ci->load->get('model.option');
        if ($option instanceof DataModel) {
            // reset it
            $options = $option->get(self::TABLE_NOTICE);
            if (! is_array($options)) {
                $options = array();
            }
            if (! isset($options[$type]) || ! is_array($options[$type])) {
                $options[$type] = array();
            }

            $options[$type][] = $value;
            self::$notices[$type][] = $value;
            $option->set(self::TABLE_NOTICE, self::$notices);
            return true;
        }

        return false;
    }
}
