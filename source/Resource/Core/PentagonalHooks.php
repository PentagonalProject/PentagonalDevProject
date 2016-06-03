<?php
class PentagonalHooks extends CI_Hooks
{

    /**
     * Class constructor
     *
     * @return	void
     */
    public function __construct()
    {
        $CFG =& load_class('Config', 'core');
        log_message('info', 'Hooks Class Initialized');

        // If hooks are not enabled in the config file
        // there is nothing else to do
        if ($CFG->item('enable_hooks') === false) {
            return;
        }

        // Grab the "hooks" definition file.
        if (file_exists(CONFIGPATH.'hooks.php')) {
            include(CONFIGPATH.'hooks.php');
        }

        if (file_exists(CONFIGPATH . ENVIRONMENT.'/hooks.php')) {
            include(CONFIGPATH . ENVIRONMENT.'/hooks.php');
        }

        // If there are no hooks, we're done.
        if ( ! isset($hook) || ! is_array($hook)) {
            return;
        }

        $this->hooks =& $hook;
        $this->enabled = true;
    }

}
