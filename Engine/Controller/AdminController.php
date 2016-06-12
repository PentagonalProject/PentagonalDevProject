<?php

/** @noinspection PhpUndefinedClassInspection */
class AdminController extends CI_Controller
{
    /**
     * @var string
     */
    protected $segment_1;

    protected $segment_2;

    /**
     * for prefix admin url default set into unique name
     */
    const URL_SEPARATOR_PREFIX = ADMIN_URL_SEPARATOR_PREFIX;

    const ADMIN_NAMESPACE = 'Admin';

    const LOGIN_PATH = 'login';

    protected $class;

    public function index()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $benchmark_class = $this->benchmark;
        /** @noinspection PhpUndefinedFieldInspection */
        $this->output->enable_profiler(true);
        /** @noinspection PhpUndefinedFieldInspection */
        $this->segment_1 = $this->uri->segment(1);
        /** @noinspection PhpUndefinedFieldInspection */
        $this->segment_2 = $this->uri->segment(2);
        $this->load->helper('form');
        if (!$this->segment_2) {
            if (!is_login()) {
                redirect(admin_url(self::URL_SEPARATOR_PREFIX . self::LOGIN_PATH));
            }
            if (file_exists(__DIR__ . DS . 'Admin'. DS . 'Dashboard.php')) {
                /** @noinspection PhpIncludeInspection */
                require_once __DIR__ . DS . 'Admin' . DS . 'Dashboard.php';
                $this->class = self::ADMIN_NAMESPACE . '\\Dashboard';
                if (class_exists($this->class) && is_subclass_of($this->class, 'CI_Controller')) {
                    $class = new $this->class();
                    /** @noinspection PhpUndefinedMethodInspection */
                    Hook::add('body_class', function($arr) {
                        $arr[] = 'admin-dashboard';
                        return $arr;
                    });
                    if (method_exists($class, 'index')) {
                        ob_start();
                        /** @noinspection PhpUndefinedMethodInspection */
                        $benchmark_class->mark('admin:'.$this->class.'|controller_start');
                        // call index
                        $class->index();
                        /** @noinspection PhpUndefinedMethodInspection */
                        $benchmark_class->mark('admin:'.$this->class.'|controller_end');
                        $content = ob_get_clean();
                        // load header
                        $this->load->view('header');
                        /** @noinspection PhpUndefinedFieldInspection */
                        $this->output->append_output($content);
                        $this->load->view('footer');
                        return;
                    }
                }
            }
        } elseif (strlen($this->segment_2) > 2) { // segment must be longer than 2
            /** @noinspection PhpUndefinedFieldInspection */
            if ($this->input->server('REQUEST_METHOD') != 'POST') {
                if ($this->segment_2 != strtolower(trim($this->segment_2))) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    // clean segment
                    $segment    = $this->uri->segments;
                    $segment[1] = strtolower(trim($segment[1]));
                    $segment[2] = strtolower(trim($segment[2]));
                    $segment    = implode('/', $segment);
                    /** @noinspection PhpUndefinedFieldInspection */
                    $query = $this->input->server('QUERY_STRING');
                    $url   = site_url($segment);
                    $url .= $query ? '?' . $query : '';
                    redirect($url);

                    return;
                }
            }
            if (preg_match('/^'.preg_quote(self::URL_SEPARATOR_PREFIX, '/').'([a-z]{3,})/i', $this->segment_2, $match)) {
                $class = ucwords(strtolower($match[1]));
                if (!is_login() && strtolower($class) != strtolower(self::LOGIN_PATH)) {
                    redirect(admin_url(self::URL_SEPARATOR_PREFIX . self::LOGIN_PATH));
                }
                if (strtolower($class) == 'dashboard') {
                    redirect(site_url(strtolower($this->segment_1)));
                    return;
                }
                if (file_exists(__DIR__ . DS . 'Admin' . DS . $class . '.php')) {
                    /** @noinspection PhpIncludeInspection */
                    require_once __DIR__ . DS . 'Admin' . DS . $class . '.php';
                    $this->class = self::ADMIN_NAMESPACE . '\\' . $class;
                    if (class_exists($this->class) && is_subclass_of($this->class, 'CI_Controller')) {
                        $class = new $this->class();
                        if (method_exists($class, 'index')) {
                            ob_start();
                            /** @noinspection PhpUndefinedMethodInspection */
                            $benchmark_class->mark('admin:'.$this->class.'|controller_start');
                            // call index
                            $class->index();
                            /** @noinspection PhpUndefinedMethodInspection */
                            $benchmark_class->mark('admin:'.$this->class.'|controller_end');
                            $content = ob_get_clean();
                            $this->load->view('header');
                            /** @noinspection PhpUndefinedFieldInspection */
                            $this->output->append_output($content);
                            $this->load->view('footer');
                            return;
                        }
                    }
                }
            }
            /**
             * IF not found do next check
             */
            if (preg_match('/^([a-z]+[a-z0-9\_]+[a-z0-9])/i', $this->segment_2, $match) && !empty($match[1])) {
                if (isset($this->moduleloader) && $this->moduleloader instanceof CI_ModuleLoader) {
                    $module = $this->moduleloader->getModule($match[1]);
                    /** @noinspection PhpUndefinedMethodInspection */
                    if ($module && ! empty($module['detail']['file']) && !empty($module['module'])
                        && $module['module'] instanceof CI_Module
                        // && $module['module']->allowedAccess() // check if is allowed
                    ) {
                        $directory = dirname($module['detail']['file']);
                        $st = ucwords(strtolower($match[1]));
                        if (!file_exists($file = $directory . DS . $st.'Controller.php')) {
                            if (!file_exists($file = $directory . DS . $st.'controller.php')) {
                                if (!file_exists($file = $directory . DS . strtolower($st).'controller.php')) {
                                    show_404(
                                        __('Module %s Controller Not Found.'),
                                        $this->segment_2
                                    );

                                    return;
                                }
                            }
                        }
                        if ($file) {
                            $container = file_get_contents($file, null, null, 0, 3028);
                            $className = $module['detail']['classname'] . 'Controller';
                            $class = preg_quote($st . 'Controller', '/');
                            if (preg_match(
                                    '/class\s\s*('.$class.')\s\s*extends\s\s*((?:\\\+)?CI_Controller)/i',
                                    $container,
                                    $match
                                )
                                && ! empty($match[1])
                            ) {
                                if (strpos($match[2], '\\') === false && !preg_match('/use\s\s*CI_Controller/i', $container)) {
                                    unset($container);
                                    show_404(
                                        sprintf(
                                            __('Module %s Controller Not Found.'),
                                            $this->segment_2
                                        )
                                    );
                                    return;
                                }
                                unset($container);
                                if (class_exists($className)) {
                                    show_error(
                                        array(
                                            __('There was an error.'),
                                            sprintf(
                                                __('The class %s must be not loaded before controller call.'),
                                                $className
                                            )
                                        ),
                                        500
                                    );
                                }
                                if (!is_login()) {
                                    redirect(admin_url(self::URL_SEPARATOR_PREFIX . self::LOGIN_PATH));
                                }
                                // isolate
                                array_map(function($file) {
                                    require_once $file;
                                }, array($file));
                                $class = new $className();
                                if (method_exists($class, 'index')) {
                                    $this->class = $className;
                                    $module_name = trim(strtolower($this->segment_2));
                                    /** @noinspection PhpUndefinedMethodInspection */
                                    Hook::add('body_class', function($arr) use ($module_name) {
                                        $arr[] = 'admin-module-'.$module_name;
                                        return $arr;
                                    });
                                    ob_start();
                                    /** @noinspection PhpUndefinedMethodInspection */
                                    $benchmark_class->mark('admin_module:'.$this->class.'|controller_start');
                                    // call index
                                    $class->index();
                                    /** @noinspection PhpUndefinedMethodInspection */
                                    $benchmark_class->mark('admin_module:'.$this->class.'|controller_end');
                                    $content = ob_get_clean();
                                    $this->load->view('header');
                                    /** @noinspection PhpUndefinedFieldInspection */
                                    $this->output->append_output($content);
                                    $this->load->view('footer');

                                    return;
                                }
                            }
                        }
                    }
                }
            }
        }

        show_404();
    }
}
