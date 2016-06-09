<?php
class PentagonalLog extends CI_Log
{
    /**
     * Class constructor
     *
     * @return	void
     */
    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct()
    {
        $config =& get_config();

        $this->_log_path = $config['log_path']
                && is_string($config['log_path'])
                && trim($config['log_path']) != ''
                && is_dir($config['log_path'])
            ? $config['log_path']
            : TEMPPATH .'logs/';
        $this->_file_ext = (isset($config['log_file_extension']) && $config['log_file_extension'] !== '')
            ? ltrim($config['log_file_extension'], '.') : 'php';

        file_exists($this->_log_path) || mkdir($this->_log_path, 0755, true);

        if (! is_dir($this->_log_path) || ! is_really_writable($this->_log_path)) {
            $this->_enabled = false;
        }

        if (is_numeric($config['log_threshold'])) {
            $this->_threshold = (int) $config['log_threshold'];
        } elseif (is_array($config['log_threshold'])) {
            $this->_threshold = 0;
            $this->_threshold_array = array_flip($config['log_threshold']);
        }

        if (! empty($config['log_date_format'])) {
            $this->_date_fmt = $config['log_date_format'];
        }

        if (! empty($config['log_file_permissions']) && is_int($config['log_file_permissions'])) {
            $this->_file_permissions = $config['log_file_permissions'];
        }
    }
}
