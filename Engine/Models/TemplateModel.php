<?php
use Pentagonal\StaticHelper\PathHelper;

class TemplateModel extends CI_Model
{
    protected $active_template;

    protected $headers = array(
        'home.php',
        'single.php',
        'header.php',
        'footer.php',
    );

    protected $detail_file = 'template.json';

    const OPTIONNAME = 'system.template.active';

    protected $valid_template = array();

    protected $corrupt_template = array();

    public function __construct()
    {
        parent::__construct();
        $CI =& get_instance();
        $CI->load->model('DataModel', 'model.option');
        $CI->load->helper('language');
        $this->active_template = $CI->load->get('model.option')->get(self::OPTIONNAME, null);
        if (!$this->isValidTemplate($this->active_template)) {
            $template = $this->getActiveTemplate();
            Hook::add('admin_notice_info', function ($args) use ($template) {
                if ($template) {
                    $args['template'] = __('Active template change to: ') .$template;
                } else {
                    $args['template'] = __('Could Not set active template. Nothing template being ready');
                }
                return $args;
            });
            $this->readAndSet();
        }
    }

    public function isValidTemplate($name)
    {
        if (!is_string($name) || trim($name) == '' || !is_dir(TEMPLATEPATH . $name)) {
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
            if (!file_exists(TEMPLATEPATH . $name . DIRECTORY_SEPARATOR . $item)) {
                $this->corrupt_template[$name] = $this->parseDetail(TEMPLATEPATH . $name);
                return false;
            }
        }

        if (empty($this->valid_template[$name])) {
            $this->valid_template[$name] = $this->parseDetail(TEMPLATEPATH . $name);
        }

        return true;
    }

    public function getActiveTemplateDirectory()
    {
        if ($this->getActiveTemplate()) {
            return TEMPLATEPATH . $this->getActiveTemplate();
        }

        return null;
    }

    /**
     * @return void
     */
    public function readAndSet()
    {
        static $hasCall;
        if ($hasCall) {
            return;
        }
        $hasCall = true;
        foreach (PathHelper::readDirList(TEMPLATEPATH, 1) as $value) {
            if (!is_string($value)) {
                continue;
            }
            $dir = TEMPLATEPATH . $value . DIRECTORY_SEPARATOR;
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
                $this->load->get('model.option')->set(self::OPTIONNAME, $this->active_template, true);
            } else {
                $this->active_template = null;
            }
        } else {
            $this->active_template = null;
        }

        return;
    }

    public function getInfo($template)
    {
        if (!$this->isValidTemplate($template)) {
            return null;
        }
        $template = trim($template);
        $list = $this->getTemplateList();
        return isset($list[$template]) ? $list[$template] : null;
    }

    public function getTemplateReady()
    {
        return $this->valid_template;
    }

    public function getCorruptTemplate()
    {
        return $this->corrupt_template;
    }

    public function getActiveTemplate()
    {
        return $this->active_template;
    }

    public function getTemplateList()
    {
        return array_merge($this->getTemplateReady(), $this->getCorruptTemplate());
    }

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
}
