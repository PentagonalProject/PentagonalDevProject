<?php
use Pentagonal\StaticHelper\PathHelper;

/**
 * Class AdminTemplateModel
 */

/** @noinspection PhpUndefinedClassInspection */
class AdminTemplateModel extends CI_Model
{
    /**
     * @var string
     */
    protected $active_template;

    /**
     * List must be exist template
     * @var array
     */
    protected $headers = array(
        'header.php',
        'footer.php',
    );

    /**
     * @var string
     */
    protected $detail_file = 'template.json';

    /**
     * @const string
     */
    const OPTION_NAME = 'system.admin_template.active';

    /**
     * @var array
     */
    protected $valid_template = array();

    /**
     * @var array
     */
    protected $corrupt_template = array();

    /**
     * AdminTemplateModel constructor.
     */
    public function __construct()
    {
        /** @noinspection PhpUndefinedClassInspection */
        parent::__construct();
        /** @noinspection PhpUndefinedFieldInspection */
        $this->load->model('DataModel', MODEL_NAME_OPTION);
        /** @noinspection PhpUndefinedFieldInspection */
        $this->load->helper('language');
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function isValidTemplate($name)
    {
        if (!is_string($name) || trim($name) == '' || !is_dir(ADMINTEMPLATEPATH . $name)) {
            return false;
        }
        if (isset($this->valid_template[$name])) {
            return true;
        }
        $name = preg_replace('/(\\\|\/)+/', DIRECTORY_SEPARATOR, $name);
        $name = trim($name, DIRECTORY_SEPARATOR);
        if (strpos('/', trim($name)) !== false || strpos('\\', trim($name)) !== false) {
            return false;
        }
        foreach ($this->headers as $item) {
            if (!file_exists(ADMINTEMPLATEPATH . $name . DIRECTORY_SEPARATOR . $item)) {
                $this->corrupt_template[$name] = $this->parseDetail(ADMINTEMPLATEPATH . $name);
                return false;
            }
        }

        if (empty($this->valid_template[$name])) {
            $this->valid_template[$name] = $this->parseDetail(ADMINTEMPLATEPATH . $name);
        }

        return true;
    }

    /**
     * Get active template directory
     *
     * @return null|string
     */
    public function getActiveTemplateDirectory()
    {
        if ($this->getActiveTemplate()) {
            return ADMINTEMPLATEPATH . $this->getActiveTemplate();
        }

        return null;
    }

    /**
     * Initialize
     *
     * @return void
     */
    public function readAndSet()
    {
        static $hasCall;
        if ($hasCall) {
            return;
        }
        $hasCall = true;
        foreach (PathHelper::readDirList(ADMINTEMPLATEPATH, 1) as $value) {
            if (!is_string($value)) {
                continue;
            }
            $dir = ADMINTEMPLATEPATH . $value . DIRECTORY_SEPARATOR;
            if (!is_dir($dir)) {
                continue;
            }

            $corrupt_list = array();
            $corrupt = false;
            foreach ($this->headers as $header) {
                if (!file_exists($dir . $header)) {
                    $corrupt_list[] = $header;
                    $corrupt = true;
                }
            }
            if ($corrupt) {
                $this->corrupt_template[$value]                     = $this->parseDetail($dir);
                $this->corrupt_template[$value]['corrupt_files']    = $corrupt_list;
                $this->corrupt_template[$value]['template_invalid'] = __('corrupt file');
            } else {
                $this->valid_template[$value] = $this->parseDetail($dir);
            }
        }

        if (!empty($this->valid_template)) {
            if (!$this->active_template || ! isset($this->valid_template[$this->active_template])) {
                $this->active_template = key($this->valid_template);
                /** @noinspection PhpUndefinedFieldInspection */
                $this->load->get(MODEL_NAME_OPTION)->set(self::OPTION_NAME, $this->active_template, true);
            } else {
                $this->active_template = null;
            }
        } else {
            $this->active_template = null;
        }

        return;
    }

    /**
     * Get Info from template
     * @param string $template
     *
     * @return null|array
     */
    public function getInfo($template)
    {
        if (!$this->isValidTemplate($template)) {
            return null;
        }
        $template = trim($template);
        $list = $this->getTemplateList();
        return isset($list[$template]) ? $list[$template] : null;
    }

    /**
     * get Ready templates
     *
     * @return array
     */
    public function getTemplateReady()
    {
        return $this->valid_template;
    }

    /**
     * Get valid templates
     *
     * @return array
     */
    public function getValidTemplate()
    {
        return $this->valid_template;
    }
    /**
     * @return array
     */
    public function getCorruptTemplate()
    {
        return $this->corrupt_template;
    }

    /**
     * @return string
     */
    public function getActiveTemplate()
    {
        return $this->active_template;
    }

    /**
     * @return array
     */
    public function getTemplateList()
    {
        return array_merge($this->getTemplateReady(), $this->getCorruptTemplate());
    }

    /**
     * @param string $template_dir
     *
     * @return array
     */
    private function parseDetail($template_dir)
    {
        $file = "{$template_dir}/{$this->detail_file}";
        $default = [
            'template_name' => basename($template_dir),
            'template_url' => null,
            'template_author' => null,
            'template_author_url' => null,
            'template_version' => null,
            'template_license' => null,
            'template_license_url' => null,
            'template_description' => null,
            'template_directory' => realpath(dirname($file)) . DIRECTORY_SEPARATOR,
            'template_invalid' => 'Invalid Template detail has not readable',
        ];
        if (is_readable($file)) {
            $json = $this->prepareJson(file_get_contents($file));
            $json = $json ? json_decode($json, true) : null;
            if (is_array($json)) {
                $default = array_merge($default, $json);
                $default['template_invalid'] = false;
            } else {
                $default['template_invalid'] = 'Invalid Json format';
            }
        }

        return $default;
    }

    /**
     * Fix Invalid Commas / comments to make sure proper valid json
     *
     * @param  string $json the json
     * @return string if maybe valid json or null if not a json
     */
    private function prepareJson($json)
    {
        if (strpos($json, '}') === false && strpos($json, '[') === false) {
            return null;
        }
        // remove template comments
        if (strpos($json, '/') !== false) {
            $json = preg_replace_callback('/"(.*?)"/s', function ($c) {
                $c[1] = str_replace('//', '\/\/', $c[1]);
                return '"'.str_replace("\n", '\n', preg_replace('/([^\\\\])(\/)/', '$1\\\\$2', $c[1])).'"';
            }, $json);
        }
        // remove comments
        // use /* ... */  or -> // ..
        if (strpos($json, '//') !== false || strpos($json, '*/') !== false) {
            $json = preg_replace('/\s*(?:(?>[^\":]))(\/\/.*|[^\\\\]\/\*.*\*\/)(?!")/s', '', $json);
        }
        // remove end of commas
        $json = preg_replace_callback('/\s*\,(\s*(?:\}|\]))(?!")/', function ($c) {
            return $c[1];
        }, $json);
        // replace json invalid end by commas
        $json = preg_replace('/\,\s*([\}\]])/', '$1', $json);
        return $json;
    }

    /**
     * initial
     *
     * @return $this
     */
    public function init()
    {
        static $hasCall;
        if ($hasCall) {
            return $this;
        }
        $hasCall = true;
        /** @noinspection PhpUndefinedFieldInspection */
        $data = $this->load->get(MODEL_NAME_OPTION)->getFull(self::OPTION_NAME, null);
        if (empty($data) || ! is_array($data) || isset($data['options_autoload']) && $data['options_autoload'] != 'yes') {
            $this->load->get(MODEL_NAME_OPTION)->set(self::OPTION_NAME, $data['options_value'], true);
        }
        $this->active_template = isset($data['options_value']) ? $data['options_value'] : null;
        if (!$this->isValidTemplate($this->active_template)) {
            // detect template
            $this->readAndSet();
            $template = $this->getActiveTemplate();
            /** @noinspection PhpUndefinedMethodInspection */
            Hook::add('admin_notice_warning', function ($args) use ($template) {
                if ($template) {
                    $args[] = __('Active admin template change to: ') .$template;
                } else {
                    $args[] = __('Could Not set admin active template. Nothing template being ready');
                }
                return $args;
            });
        }

        return $this;
    }
}
