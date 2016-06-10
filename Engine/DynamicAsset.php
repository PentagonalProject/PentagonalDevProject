<?php
final class DynamicAsset
{
    /**
     * @var array
     */
    protected static $list_integrity = array();
    /**
     * AssetPath
     */
    const ASSET_PATH = ASSETPATH;

    const INTEGRITY_NAME = 'asset_integrity.json';

    const DIRECTORY_SEP = '~';

    private static $instance;

    private static $integrity_cached = null;

    private static $cache_ready = null;

    private static $cache_dir;

    protected $integrity;

    protected $integrity_aliases;

    private function __construct($integrity = null)
    {
        $this->internalCheckCache();
        $this->integrity = $integrity;
    }

    /**
     * @return DynamicAsset
     */
    public static function getInstance()
    {
        !self::$instance && self::$instance = new self();

        return self::$instance;
    }

    private function internalCheckCache()
    {
        if (self::$cache_ready === null) {
            $cache           = config_item('cache_dir');
            self::$cache_dir = empty($cache) || ! is_string($cache) || trim($cache) != '' || ! is_dir($cache)
                ? TEMPPATH . 'cache' . DS
                : $cache;
            if (self::$cache_dir == TEMPPATH . 'cache' . DS && ! is_dir(self::$cache_dir)) {
                if (!is_dir(self::$cache_dir . 'integrity' .DS)) {
                    if (! @mkdir(self::$cache_dir . 'integrity' . DS, 755, true)) {
                        self::$cache_ready = false;
                        self::$cache_dir   = null;

                        return;
                    }
                    @file_put_contents(self::$cache_dir . 'integrity' . DS .'index.html', '');
                }
                if (!is_writable(self::$cache_dir . 'integrity' . DS)) {
                    self::$cache_ready = false;
                    self::$cache_dir   = null;
                    return;
                }

                self::$cache_ready = true;
                self::$cache_dir = self::$cache_dir . 'integrity'. DS;
                return;
            }
            self::$cache_dir = str_replace(array('/', '\\'), DS, self::$cache_dir);
            self::$cache_dir = rtrim(self::$cache_dir, DS) . DS;
            if (! is_dir(self::$cache_dir)) {
                self::$cache_dir   = null;
                self::$cache_ready = false;
                return;
            }
            self::$cache_dir = realpath(self::$cache_dir). DS;
            if (!is_dir(self::$cache_dir . 'integrity' . DS)) {
                if (!@mkdir(self::$cache_dir . 'integrity' . DS)) {
                    self::$cache_dir   = null;
                    self::$cache_ready = false;
                    return;
                }
                @file_put_contents(self::$cache_dir . 'integrity' . DS .'index.html', '');
            }
            self::$cache_dir = self::$cache_dir . 'integrity' . DS;
            self::$cache_ready = true;
        }
        if (!is_array(self::$integrity_cached)) {
            self::$integrity_cached = array();
            if (self::$cache_ready) {
                $file = self::$cache_dir . DS . self::INTEGRITY_NAME;
                if (!file_exists($file)) {
                    @file_put_contents($file, json_encode(self::$integrity_cached));
                } elseif (is_readable($file)) {
                    $str = file_get_contents($file);
                    $json = json_decode($str, true);
                    if (!is_array($json)) {
                        @file_put_contents($file, json_encode(array()));
                    } else {
                        self::$integrity_cached = $json;
                    }
                }
            }
        }
    }

    private function saveIntegrity($list)
    {
        if (!self::$cache_ready) {
            return false;
        }
        self::$integrity_cached = array_merge(self::$integrity_cached, $list);
        $file = self::$cache_dir . DS . self::INTEGRITY_NAME;
        return (bool) @file_put_contents($file, json_encode(self::$integrity_cached, JSON_PRETTY_PRINT));
    }

    public static function getData($integrity)
    {
        /**
         * @var \DynamicAsset
         */
        $instance = self::getInstance();
        /** @noinspection PhpUndefinedVariableInspection */
        return isset($instance::$integrity_cached[$integrity])
            ? $instance::$integrity_cached[$integrity]
            : null;
    }

    public static function sanitize($string)
    {
        if (!is_string($string)) {
            return false;
        }
        $string = trim($string, self::DIRECTORY_SEP);
        $string = str_replace(
            array(
                self::DIRECTORY_SEP,
                '/',
                '\\',
            ),
            DIRECTORY_SEPARATOR,
            $string
        );

        return $string;
    }

    public static function generate(array $list, $type)
    {
        $instance = self::getInstance();
        if (!is_string($type) || empty($list)) {
            return null;
        }
        $type = trim(strtolower($type));
        $type = trim($type, '.');
        if (!$type || !in_array($type, array('css', 'js'))) {
            return null;
        }
        $list = array_values($list);
        foreach ($list as $key => $value) {
            $list[$key] = $instance->sanitize($value);
        }
        $hash = sha1(json_encode($list).ENGINE_SALT);
        if ($data = $instance->getData($hash)) {
            $obj = new self($hash);
            foreach ($data as $k => $v) {
                $obj->$k = $v;
            }
            return $obj;
        }

        $ext =  '.' . $type;
        if (is_array($list)) {
            foreach ($list as $k => $value) {
                $sanitized = self::sanitize($value);
                if (!$sanitized) {
                    unset($list[$k]);
                    continue;
                }
                $changed = false;
                if (substr($sanitized, -strlen($ext)) === $ext) {
                    $sanitized = substr($sanitized, 0, -strlen($ext));
                    $changed = true;
                }
                if (file_exists($file = self::ASSET_PATH . $sanitized . $ext)) {
                    $list[$k] = $sanitized.$ext;
                    continue;
                } elseif ($changed && file_exists($file = self::ASSET_PATH . $sanitized . $ext . $ext)) {
                    $list[$k] = $sanitized . $ext . $ext;
                    continue;
                }
                unset($list[$k]);
            }
            $list = array_values($list);
        }

        if (empty($list)) {
            return false;
        }

        $hash_2 = sha1(json_encode($list).ENGINE_SALT);
        if ($hash_2 != $hash) {
            $instance->saveIntegrity(
                array(
                    $hash_2 => array(
                        'type' => $type,
                        'data' => $list,
                        'integrity' => $hash_2,
                        'integrity_aliases' => $hash,
                    )
                )
            );
        }

        $data = array(
            $hash => array(
                'type' => $type,
                'data' => $list,
                'integrity' => $hash,
                'integrity_aliases' => $hash_2,
            )
        );

        $instance->saveIntegrity($data);

        $obj = new self($hash_2);
        foreach ($data[$hash] as $k => $v) {
            $obj->$k = $v;
        }
        $obj->integrity = $hash_2;
        $obj->integrity = $hash_2;
        return $obj;
    }

    public function getIntegrity()
    {
        return $this->integrity;
    }

    public function getIntegrityAliases()
    {
        return $this->integrity_aliases;
    }

    public function checkIntegrity($integrity)
    {
        return $integrity === $this->getIntegrity() || $integrity === $this->getIntegrityAliases();
    }

    public function toUrl()
    {
        $integrity = $this->integrity;
        $integrities = self::getData($integrity);
        if (!empty($integrities['data']) && is_array($integrities['data'])
            && !empty($integrities['type']) && in_array($integrities['type'], array('css', 'js'))
        ) {
            $type = $integrities['type'];
            $url = '';
            foreach ($integrities['data'] as $value) {
                $tmp = str_replace('/', self::DIRECTORY_SEP, $value);
                $url .= ','.substr($tmp, 0, -strlen($type)-1);
            }
            $dynamic = trim(str_replace(DIRECTORY_SEPARATOR, '/', DYNAMICPATH), '/');
            $url = trim($url, ',');
            $the_url = "{$dynamic}/{$type}/{$url}-hash[{$integrity}].{$type}";
            return base_url($the_url);
        }

        return null;
    }
}
