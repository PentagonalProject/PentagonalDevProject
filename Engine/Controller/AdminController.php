<?php

/** @noinspection PhpUndefinedClassInspection */
class AdminController extends CI_Controller
{
    protected $segment_1;

    protected $segment_2;

    /**
     * for prefix admin url default set into unique name
     */
    const URL_SEPARATOR_PREFIX = ADMIN_URL_SEPARATOR_PREFIX;

    const ADMIN_NAMESPACE = 'Admin';

    protected $class;

    public function index()
    {
        $benchmark_class = $this->benchmark;
        $this->output->enable_profiler(true);
        $this->segment_1 = $this->uri->segment(1);
        $this->segment_2 = $this->uri->segment(2);

        if (!$this->segment_2) {
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
                        $this->load->view('header');
                        $benchmark_class->mark('admin:'.$this->class.'|controller_start');
                        // call index
                        $class->index();
                        $benchmark_class->mark('admin:'.$this->class.'|controller_end');
                        $this->load->view('footer');
                        return;
                    }
                }
            }
        } elseif (strlen($this->segment_2) > 2) { // segment must be longer than 2
            if ($this->input->server('REQUEST_METHOD') != 'POST') {
                if ($this->segment_2 != strtolower(trim($this->segment_2))) {
                    // clean segment
                    $segment    = $this->uri->segments;
                    $segment[1] = strtolower(trim($segment[1]));
                    $segment[2] = strtolower(trim($segment[2]));
                    $segment    = implode('/', $segment);
                    $query = $this->input->server('QUERY_STRING');
                    $url   = site_url($segment);
                    $url .= $query ? '?' . $query : '';
                    redirect($url);

                    return;
                }
            }
            if (preg_match('/^'.preg_quote(self::URL_SEPARATOR_PREFIX, '/').'([a-z]{3,})/i', $this->segment_2, $match)) {
                $class = ucwords(strtolower($match[1]));
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
                            $this->load->view('header');
                            $benchmark_class->mark('admin:'.$this->class.'|controller_start');
                            // call index
                            $class->index();
                            $benchmark_class->mark('admin:'.$this->class.'|controller_end');
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
                    if ($module && ! empty($module['detail']['file'])) {
                        $directory = dirname($module['detail']['file']);
                        $st = ucwords(strtolower($match[1]));
                        if (!file_exists($file = $directory . DS . $st.'Controller.php')) {
                            if (!file_exists($file = $directory . DS . $st.'controller.php')) {
                                if (!file_exists($file = $directory . DS . strtolower($st).'controller.php')) {
                                    show_404(
                                        __('Module %s Controller Not Found'),
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
                                    '/class\s*('.$class.')\s*extends\s*((?:\\\+)?CI_Controller)/i',
                                    $container,
                                    $match
                                )
                                && ! empty($match[1])
                            ) {
                                if (strpos($match[2], '\\') === false && !preg_match('/use\s*CI_Module/i', $container)) {
                                    unset($container);
                                    show_404(
                                        sprintf(
                                            __('Module %s Controller Not Found'),
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
                                // immunated
                                array_map(function($file) {
                                    require_once $file;
                                }, array($file));
                                $class = new $className();
                                if (method_exists($class, 'index')) {
                                    $this->class = $className;
                                    $this->load->view('header');
                                    $module_name = trim(strtolower($this->segment_2));
                                    /** @noinspection PhpUndefinedMethodInspection */
                                    Hook::add('body_class', function($arr) use ($module_name) {
                                        $arr[] = 'admin-module-'.$module_name;
                                        return $arr;
                                    });
                                    $benchmark_class->mark('admin_module:'.$this->class.'|controller_start');
                                    // call index
                                    $class->index();
                                    $this->load->view('footer');
                                    $benchmark_class->mark('admin_module:'.$this->class.'|controller_end');
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
