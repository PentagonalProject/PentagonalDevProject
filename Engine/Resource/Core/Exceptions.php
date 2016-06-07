<?php
class CI_Exceptions
{
    /**
     * Nesting level of the output buffering mechanism
     *
     * @var	int
     */
    public $ob_level;

    /**
     * List of available error levels
     *
     * @var	array
     */
    public $levels = array(
        E_ERROR			=>	'Error',
        E_WARNING		=>	'Warning',
        E_PARSE			=>	'Parsing Error',
        E_NOTICE		=>	'Notice',
        E_CORE_ERROR		=>	'Core Error',
        E_CORE_WARNING		=>	'Core Warning',
        E_COMPILE_ERROR		=>	'Compile Error',
        E_COMPILE_WARNING	=>	'Compile Warning',
        E_USER_ERROR		=>	'User Error',
        E_USER_WARNING		=>	'User Warning',
        E_USER_NOTICE		=>	'User Notice',
        E_STRICT		=>	'Runtime Notice'
    );

    /**
     * Class constructor
     *
     * @return	void
     */
    public function __construct()
    {
        $this->ob_level = ob_get_level();
        // Note: Do not log messages from this constructor.
    }

    // --------------------------------------------------------------------

    /**
     * Exception Logger
     *
     * Logs PHP generated error messages
     *
     * @param	int	$severity	Log level
     * @param	string	$message	Error message
     * @param	string	$filepath	File path
     * @param	int	$line		Line number
     * @return	void
     */
    public function log_exception($severity, $message, $filepath, $line)
    {
        $severity = isset($this->levels[$severity]) ? $this->levels[$severity] : $severity;
        log_message('error', 'Severity: '.$severity.' --> '.$message.' '.$filepath.' '.$line);
    }

    // --------------------------------------------------------------------

    /**
     * 404 Error Handler
     *
     * @uses	CI_Exceptions::show_error()
     *
     * @param	string	$page		Page URI
     * @param 	bool	$log_error	Whether to log the error
     * @return	void
     */
    public function show_404($page = '', $log_error = TRUE)
    {
        if (is_cli()) {
            $heading = 'Not Found';
            $message = 'The controller/method pair you requested was not found.';
        } else {
            $heading = '404 Page Not Found';
            $message = 'The page you requested was not found.';
        }
        // By default we log this, but allow a dev to skip it
        if ($log_error) {
            log_message('error', $heading.': '.$page);
        }
        $retval = $this->show_error($heading, $message, 'error_404', 404);
        if (!is_cli() && function_exists('get_instance')) {
            $ci = get_instance();
            $ci->output->set_output($retval);
            $ci->output->_display();
        } else {
            echo $retval;
        }
        exit(4); // EXIT_UNKNOWN_FILE
    }

    // --------------------------------------------------------------------

    /**
     * General Error Page
     *
     * Takes an error message as input (either as a string or an array)
     * and displays it using the specified template.
     *
     * @param	string		$heading	Page heading
     * @param	string|string[]	$message	Error message
     * @param	string		$template	Template name
     * @param 	int		$status_code	(default: 500)
     *
     * @return	string	Error page output
     */
    public function show_error($heading, $message, $template = 'error_general', $status_code = 500)
    {
        $templates_path = config_item('error_views_path');
        if (empty($templates_path)) {
            $templates_path = VIEWPATH.'errors'.DIRECTORY_SEPARATOR;
        }
        $the_message = $message;
        if (is_cli()) {
            $message = "\t".(is_array($message) ? implode("\n\t", $message) : $message);
            $template = 'cli'.DIRECTORY_SEPARATOR.$template;
        } else {
            set_status_header($status_code);
            $message = '<p>'.(is_array($message) ? implode('</p><p>', $message) : $message).'</p>';
            $template = 'html'.DIRECTORY_SEPARATOR.$template;
        }
        $file = array(
            'html'.DIRECTORY_SEPARATOR.'error_general' => 'error.php',
            'html'.DIRECTORY_SEPARATOR.'error_db' => 'errordb.php',
            'html'.DIRECTORY_SEPARATOR.'error_exception'=> 'exception.php',
            'html'.DIRECTORY_SEPARATOR.'error_php' => 'errorphp.php',
            'html'.DIRECTORY_SEPARATOR.'error_404' => '404.php',
        );

        if (ob_get_level() > $this->ob_level + 1) {
            ob_end_flush();
        }
        ob_start();
        if (!is_cli() && isset($file[$template]) && function_exists('get_instance')) {
            $ci = get_instance();
            if (isset($ci->load) && $ci->load instanceof \PentagonalLoader && $ci->load->getActiveTemplate()) {
                if (file_exists($ci->load->getActiveTemplate() . DIRECTORY_SEPARATOR . $file[$template])) {
                    $ci->load->vars(
                        array(
                            'heading' => $heading,
                            'message' => $the_message,
                            'exception' => $this,
                            'status_code' => $status_code
                        )
                    );
                    $ci->load->view($file[$template]);
                    $exist = true;
                }
            }
        }

        if (empty($exist)) {
            unset($exist, $ci);
            /** @noinspection PhpIncludeInspection */
            include( $templates_path . $template . '.php' );
        }

        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }

    // --------------------------------------------------------------------

    /**
     * @param $exception
     */
    public function show_exception($exception)
    {
        $templates_path = config_item('error_views_path');
        if (empty($templates_path)) {
            $templates_path = VIEWPATH.'errors'.DIRECTORY_SEPARATOR;
        }
        $message = $exception->getMessage();
        if (empty($message)) {
            $message = '(null)';
        }

        if (is_cli()) {
            $templates_path .= 'cli'.DIRECTORY_SEPARATOR;
        } else {
            set_status_header(500);
            $templates_path .= 'html'.DIRECTORY_SEPARATOR;
        }

        if (ob_get_level() > $this->ob_level + 1) {
            ob_end_flush();
        }
        if (!is_cli() && function_exists('get_instance')) {
            $ci = get_instance();
            if (isset($ci->load) && $ci->load instanceof \PentagonalLoader && $ci->load->getActiveTemplate()) {
                if (file_exists($ci->load->getActiveTemplate() . DIRECTORY_SEPARATOR . 'exception.php')) {
                    $ci->load->vars(
                        array(
                            'message' => $message,
                            'exception' => $this,
                        )
                    );
                    $ci->load->view('exception.php');
                    $exist = true;
                }
            }
        }
        ob_start();
        if (empty($exist)) {
            unset($ci, $exist);
            /** @noinspection PhpIncludeInspection */
            include($templates_path . 'error_exception.php');
        }
        $buffer = ob_get_contents();
        ob_end_clean();
        echo $buffer;
    }

    // --------------------------------------------------------------------

    /**
     * Native PHP error handler
     *
     * @param	int	$severity	Error level
     * @param	string	$message	Error message
     * @param	string	$filepath	File path
     * @param	int	$line		Line number
     * @return	string	Error page output
     */
    public function show_php_error($severity, $message, $filepath, $line)
    {
        $templates_path = config_item('error_views_path');
        if (empty($templates_path))
        {
            $templates_path = VIEWPATH.'errors'.DIRECTORY_SEPARATOR;
        }

        $severity = isset($this->levels[$severity]) ? $this->levels[$severity] : $severity;

        // For safety reasons we don't show the full file path in non-CLI requests
        if (! is_cli()) {
            $filepath = str_replace('\\', '/', $filepath);
            if (FALSE !== strpos($filepath, '/')) {
                $x = explode('/', $filepath);
                $filepath = $x[count($x)-2].'/'.end($x);
            }

            $template = 'html'.DIRECTORY_SEPARATOR.'error_php';
        } else {
            $template = 'cli'.DIRECTORY_SEPARATOR.'error_php';
        }

        if (ob_get_level() > $this->ob_level + 1) {
            ob_end_flush();
        }

        ob_start();
        if (!is_cli() && function_exists('get_instance')) {
            $ci = get_instance();
            if (isset($ci->load) && $ci->load instanceof \PentagonalLoader && $ci->load->getActiveTemplate()) {
                if (file_exists($ci->load->getActiveTemplate() . DIRECTORY_SEPARATOR . 'errorphp.php')) {
                    $ci->load->vars(
                        array(
                            'message' => $message,
                            'severity' => $severity,
                            'exception' => $this
                        )
                    );
                    $ci->load->view('errorphp.php');
                    $exist = true;
                }
            }
        }

        if (empty($exist)) {
            unset($exist, $ci);
            include($templates_path . $template . '.php');
        }
        $buffer = ob_get_contents();
        ob_end_clean();
        echo $buffer;
    }
}
