<?php

class PentagonalRouter extends CI_Router
{
    protected $admin_path_route;
    protected $dynamic_path_route;

    /**
     * is on admin
     *
     * @var bool
     */
    protected $is_admin = false;

    protected $is_dynamic = false;

    /**
     * Add direct route controller 404 override
     *
     * @var string
     */
    public $override_404 = 'Controller404';

    /**
     * Class constructor
     *
     * Runs the route mapping function.
     *
     * @param	array	$routing
     * @return	void
     */
    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct($routing = null)
    {
        $this->extendedRouting = $routing;

        $this->config =& load_class('Config', 'core');
        $this->uri =& load_class('URI', 'core');

        $this->enable_query_strings = ( ! is_cli() && $this->config->item('enable_query_strings') === TRUE);

        // If a directory override is configured, it has to be set before any dynamic routing logic
        is_array($routing) && isset($routing['directory']) && $this->set_directory($routing['directory']);
    }

    protected function setRoutingAddition()
    {

        // Set any routing overrides that may exist in the main index file
        if (!empty($this->extendedRouting) && is_array($this->extendedRouting)) {
            empty($this->extendedRouting['controller'])
                || $this->set_class($this->extendedRouting['controller']);
            empty($this->extendedRouting['function'])
                || $this->set_method($this->extendedRouting['function']);
        }

        unset($this->extendedRouting);
        log_message('info', 'Router Class Initialized');
    }

    public function setRouting()
    {
        static $hasCall = 0;
        if ($hasCall < 2) {
            $hasCall++;
            $this->_set_routing();
        }
    }

    /**
     * Set route mapping
     *
     * Determines what should be served based on the URI request,
     * as well as any "routes" that have been set in the routing config file.
     *
     * @return	void
     */
    protected function _set_routing()
    {
        $this->admin_path_route = '(?i)'.preg_quote(ADMINPATH, '/').'(\/+(.*))?';
        $this->dynamic_path_route = preg_quote(DYNAMICPATH, '/').'\/(js|css)\/([a-z0-9A-Z\_\-\.\,\(\)\~\@]+)\-hash\[[0-9a-f]{40}\]\.\\1';
        $this->default_controller = 'DefaultController';
        $this->translate_uri_dashes = false;
        $this->override_404 = 'Controller404';
        $this->routes = array(
             $this->admin_path_route => 'AdminController',
             $this->dynamic_path_route => 'AssetController',
            '(.*)?' => 'DefaultController',
        );

        // Are query strings enabled in the config file? Normally CI doesn't utilize query strings
        // since URI segments are more search-engine friendly, but they can optionally be used.
        // If this feature is enabled, we will gather the directory/class/method a little differently
        if ($this->enable_query_strings) {
            // If the directory is set at this time, it means an override exists, so skip the checks
            if (! isset($this->directory)) {
                $_d = $this->config->item('directory_trigger');
                $_d = isset($_GET[$_d]) ? trim($_GET[$_d], " \t\n\r\0\x0B/") : '';

                if ($_d !== '') {
                    $this->uri->filter_uri($_d);
                    $this->set_directory($_d);
                }
            }

            $_c = trim($this->config->item('controller_trigger'));
            if (! empty($_GET[$_c])) {
                $this->uri->filter_uri($_GET[$_c]);
                $this->set_class($_GET[$_c]);

                $_f = trim($this->config->item('function_trigger'));
                if (! empty($_GET[$_f])) {
                    $this->uri->filter_uri($_GET[$_f]);
                    $this->set_method($_GET[$_f]);
                }

                $this->uri->rsegments = array(
                    1 => $this->class,
                    2 => $this->method
                );
            } else {
                $this->_set_default_controller();
            }

            // Routing rules don't apply to query strings and we don't need to detect
            // directories, so we're done here
            return;
        }

        /**
         * Run Hooks
         */
        Hook::apply('system_before_root_parsed', $this);

        // Is there anything to parse?
        if ($this->uri->uri_string !== '') {
            $this->_parse_routes();
        } else {
            $this->_set_default_controller();
        }

        $this->setRoutingAddition();
    }

    /**
     * Set default controller
     *
     * @override
     * @return	void
     */
    protected function _set_default_controller()
    {
        if (empty($this->default_controller)) {
            show_error('Unable to determine what should be displayed. A default route has not been specified in the routing file.');
        }

        $method = null;
        // Is the method being specified?
        if (sscanf($this->default_controller, '%[^/]/%s', $class, $method) !== 2) {
            $method = 'index';
        }

        if (! file_exists(CONTROLLERPATH . $this->directory.ucfirst($class).'.php')) {
            // This will trigger 404 later
            return;
        }

        $this->set_class($class);
        $this->set_method($method);

        // Assign routed segments, index starting from 1
        $this->uri->rsegments = array(
            1 => $class,
            2 => $method
        );

        log_message('debug', 'No URI present. Default controller set.');
    }

    /**
     * Validate request
     *
     * Attempts validate the URI request and determine the controller path.
     *
     * @used-by	CI_Router::_set_request()
     * @param	array	$segments	URI segments
     * @return	mixed	URI segments
     */
    protected function _validate_request($segments)
    {
        $c = count($segments);
        $directory_override = isset($this->directory);

        // Loop through our segments and return as soon as a controller
        // is found or when such a directory doesn't exist
        while ($c-- > 0) {
            $test = $this->directory
                .ucfirst($this->translate_uri_dashes === true ? str_replace('-', '_', $segments[0]) : $segments[0]);

            if (! file_exists(CONTROLLERPATH . $test.'.php')
                && $directory_override === false
                && is_dir(CONTROLLERPATH . $this->directory.$segments[0])
            ) {
                $this->set_directory(array_shift($segments), true);
                continue;
            }

            return $segments;
        }

        // This means that all segments were actually directories
        return $segments;
    }
    /**
     * Parse Routes
     *
     * Matches any routes that may exist in the config/routes.php file
     * against the URI to determine if the class/method need to be remapped.
     *
     * @return	void
     */
    protected function _parse_routes()
    {
        // Turn the segment array into a URI string
        $uri = implode('/', $this->uri->segments);
        // Get HTTP verb
        $http_verb = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'cli';

        // Loop through the route array looking for wildcards
        foreach ($this->routes as $key => $val) {
            // Check if route format is using HTTP verbs
            if (is_array($val)) {
                $val = array_change_key_case($val, CASE_LOWER);
                if (isset($val[$http_verb])) {
                    $val = $val[$http_verb];
                } else {
                    continue;
                }
            }

            // Convert wildcards to RegEx
            $key = str_replace(array(':any', ':num'), array('[^/]+', '[0-9]+'), $key);

            // Does the RegEx match?
            if (preg_match('#^'.$key.'$#', $uri, $matches)) {
                //set is admin
                $this->is_admin = ($key === $this->admin_path_route);
                ! $this->is_admin && $this->is_dynamic = ($key === $this->dynamic_path_route);
                // Are we using callbacks to process back-references?
                if (! is_string($val) && is_callable($val)) {
                    // Remove the original string from the matches array.
                    array_shift($matches);

                    // Execute the callback using the values in matches as its parameters.
                    $val = call_user_func_array($val, $matches);
                } elseif (strpos($val, '$') !== false && strpos($key, '(') !== false) {
                    // Are we using the default routing method for back-references?
                    $val = preg_replace('#^'.$key.'$#', $val, $uri);
                }

                $this->_set_request(explode('/', $val));
                return;
            }
        }

        /**
         * Comment in this toprevent direct access to the controller URI
         * // If we got this far it means we didn't encounter a
         * // matching route so we'll set the site default route
         * $this->_set_request(array_values($this->uri->segments));
         */
        return null;
    }

    /**
     * @return bool if is on admin route
     */
    public function isAdminRoute()
    {
        return $this->is_admin;
    }
    /**
     * @return bool if is on admin route
     */
    public function isAssetRoute()
    {
        return $this->is_dynamic;
    }
}
