<?php
class PentagonalConfig extends CI_Config
{
    /**
     * Constructor
     *
     * Override Config Core to Auto Detection URL if Config URL does not set
     */
    public function __construct()
    {
        // CI use refference to allow arguments take override nested
        $this->config =& get_config();
        // Set the base_url automatically if none was provided
        if (empty($this->config['base_url'])) {
            /**
             * Auto Detection URL on Code Igniter
             */
            if (isset($_SERVER['HTTP_HOST'])) {
                $base_url = $this->portUrlMessDetector($_SERVER['HTTP_HOST']);
            } elseif (isset($_SERVER['SERVER_NAME'])) {
                $base_url = $this->portUrlMessDetector($_SERVER['HTTP_HOST']);
            } elseif (isset($_SERVER['SERVER_ADDR'])) {
                // this is IPV 6 ipv 6 accessed by http(s)://[11:22:33:44]/
                if (strpos($_SERVER['SERVER_ADDR'], ':') !== false) {
                    $base_url = $this->portUrlMessDetector('['.$_SERVER['SERVER_ADDR'].']');
                } else {
                    $base_url = $this->portUrlMessDetector($_SERVER['SERVER_ADDR']);
                }
            } else {
                // default host , but it will be almost impossible, if server config has not wrong!
                $base_url = 'http://localhost/';
            }
            $this->set_item('base_url', $base_url);
        }
        // do it like core , send log into system
        log_message('info', 'Config Class Initialized');
    }

    /**
     * Fixing the server address & URL
     *
     * @param  string $server_addr url
     * @return string base URL
     */
    protected function portUrlMessDetector($server_addr)
    {
        /**
         * Base on Different port not 80 / 443
         */
        if (isset($_SERVER['SERVER_PORT'])) {
            $server_addr .= $_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443 ? ':'.$_SERVER['SERVER_PORT'] : '';
        }
        $base_url = (is_https() ? 'https' : 'http').'://'. $server_addr
            . substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], basename($_SERVER['SCRIPT_FILENAME'])));
        return $base_url;
    }
}
