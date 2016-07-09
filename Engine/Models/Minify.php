<?php
use Pentagonal\StaticHelper\PathHelper;

/**
 * Class Minify
 */
/** @noinspection PhpUndefinedClassInspection */
class Minify extends CI_Model
{
    const HASH_METHOD = 'md5';

    const HASH_FILE   = 'sha1';

    // 10 hours
    const EXPIRE_TIME = 36000;

    /**
     * Determine as cache ready to uses
     * @var null|boolean
     */
    private static $cache_ready = null;

    /**
     * cache directory
     * @var string
     */
    protected $cache_dir;

    /**
     * Minify constructor.
     */
    public function __construct()
    {
        if (self::$cache_ready === null) {
            /** @noinspection PhpUndefinedClassInspection */
            parent::__construct();
            $cache           = config_item('cache_dir');
            $this->cache_dir = empty($cache) || ! is_string($cache) || trim($cache) != '' || ! is_dir($cache)
                ? TEMPPATH . 'cache' . DS
                : $cache;
            if ($this->cache_dir == TEMPPATH . 'cache' . DS && ! is_dir($this->cache_dir)) {
                if (!is_dir($this->cache_dir . 'asset' .DS)) {
                    if (! @mkdir($this->cache_dir . 'asset' . DS, 755, true)) {
                        self::$cache_ready = false;
                        $this->cache_dir   = null;

                        return;
                    }
                    @file_put_contents($this->cache_dir . 'asset' .DS .'index.html', '');
                }
                if (!is_writable($this->cache_dir . 'asset' . DS)) {
                    self::$cache_ready = false;
                    $this->cache_dir   = null;
                    return;
                }

                self::$cache_ready = true;
                $this->cache_dir = $this->cache_dir . 'asset'. DS;
                $this->cacheCheck();
                return;
            }
            $this->cache_dir = str_replace(array('/', '\\'), DS, $this->cache_dir);
            $this->cache_dir = rtrim($this->cache_dir, DS) . DS;
            if (! is_dir($this->cache_dir)) {
                $this->cache_dir   = null;
                self::$cache_ready = false;
                return;
            }
            $this->cache_dir = realpath($this->cache_dir). DS;
            if (!is_dir($this->cache_dir . 'asset' . DS)) {
                if (!@mkdir($this->cache_dir . 'asset' . DS)) {
                    $this->cache_dir   = null;
                    self::$cache_ready = false;
                    return;
                }

                @file_put_contents($this->cache_dir . 'asset' .DS .'index.html', '');
            }
            $this->cache_dir = $this->cache_dir . 'asset' . DS;
            self::$cache_ready = true;
            $this->cacheCheck();
        }
    }

    /**
     * Cache Check
     *
     * @return void
     */
    private function cacheCheck()
    {
        if (!self::$cache_ready) {
            return;
        }
        $expired = (@time() - self::EXPIRE_TIME);
        foreach ((array) PathHelper::readDirList($this->cache_dir, 1) as $v) {
            $file = $this->cache_dir . $v;
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (!is_file($this->cache_dir . $v) || $ext != 'css' && $ext != 'js') {
                continue;
            }
            // remove invalid time
            if (filemtime($file) < $expired) {
                @unlink($file);
            }
        }
    }

    /**
     * Put data into cache
     *
     * @param  string  $hash      hash key
     * @param  string  $text      data
     * @param  string  $type      js|css
     * @param  boolean $overwrite overwrite if exist
     * @return boolean
     */
    private function cachePut($hash, $text, $type, $overwrite = false)
    {
        if (!self::$cache_ready) {
            return false;
        }
        // invalid
        if (!is_string($type) || !is_string($text) || !is_string($hash)
            || !in_array(trim(strtolower($type)), array('js', 'css'))
        ) {
            return false;
        }
        $type = trim(strtolower($type));
        $file_name = $hash . '.' . $type;
        $file = $this->cache_dir . $file_name;
        $text = trim($text);
        // empty no need to check
        if ($text == '' && !$overwrite) {
            return true;
        }
        if (!file_exists($file) || !is_writable($file)) {
            if (@$fp = fopen($file, FOPEN_WRITE_CREATE_DESTRUCTIVE)) {
                $text = str_split($text, 2048);
                for ($i=0; count($text) > $i; $i++) {
                    $fwrite = fputs($fp, $text[$i]);
                    if ($fwrite === false) {
                        break;
                    }
                }
                unset($text);
                fclose($fp);
                return true;
            }
        }

        return false;
    }

    /**
     * Getting cache data
     *
     * @param  string $hash hash key
     * @param  string $type js|css
     * @return string|boolean
     */
    private function cacheGet($hash, $type)
    {
        if (!self::$cache_ready) {
            return false;
        }

        // invalid
        if (!is_string($type) || !is_string($hash)
            || !in_array(trim(strtolower($type)), array('js', 'css'))
        ) {
            return false;
        }
        $type = trim(strtolower($type));
        $file_name = $hash . '.' . $type;
        $file = $this->cache_dir . $file_name;
        if (file_exists($file) && is_readable($file)) {
            if (@$fp = fopen($file, FOPEN_READ)) {
                $text = '';
                while (!feof($fp)) {
                    $text .= fgets($fp, 2048);
                }
                fclose($fp);
                return $text;
            }
        }
        return false;
    }

    /**
     * Minify CSS from file
     *
     * @param string $file
     * @param bool   $strict
     *
     * @return bool|string
     */
    public function cssFile($file, $strict = true)
    {
        if (is_string($file) && trim($file)) {
            // if on strict
            if ($strict) {
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                // if use strict and extension is not css
                if (strtolower($ext) != 'css') {
                    return false;
                }
            }
            // if contain url ftp|http(s)
            $use_http = preg_match('/(ht|f)tps?:\/\//i', $file);
            if (!$use_http && (is_file($file) && is_readable($file))
                || $use_http
            ) {
                if (!$use_http) {
                    $file = realpath($file);
                }
                /**
                 * Check from cache
                 */
                $hash = self::HASH_FILE;
                $hash = $hash($file) . '.dist';
                if (($cache = $this->cacheGet($hash, 'css')) !== false) {
                    // trim to make sure it same result
                    $cache = trim($cache);
                    return $this->css($cache, $file);
                }

                $retval = null;
                /**
                 * Check if use local
                 */
                if (!$use_http && function_exists('fopen')) {
                    if (!$file) {
                        return false;
                    }
                    if ($fp = @fopen($file, 'r+')) {
                        $retval = '';
                        while (($buffer = fgets($fp, 4096)) !== false) {
                            $retval .= $buffer;
                        }
                        fclose($fp);
                    }
                } else {
                    if ($use_http) {
                        $ctx = stream_context_create(
                            array(
                                'http'=> array(
                                    'timeout' => 10,  // 10 seconds has very very long time!!
                                )
                            )
                        );
                        $retval = @file_get_contents($file, false, $ctx);
                    } else {
                        if (!$file) {
                            return false;
                        }
                        $retval = @file_get_contents($file, false);
                    }
                }
                if (is_string($retval)) {
                    // trim to make sure it same result
                    $retval = trim($retval);
                    $this->cachePut($hash, $retval, 'css');
                    return $this->css($retval, $file);
                }
            }
        }

        return false;
    }

    /**
     * Minify CSS
     *
     * @param  string      $text         css text
     * @param  null|string $url_replacer url replacerfor handle fix source
     * @return string
     */
    public function css($text, $url_replacer = null)
    {
        if (!is_string($text)) {
            return false;
        }
        $hash = self::HASH_METHOD;
        if (!is_null($url_replacer) && !is_string($url_replacer)) {
            $url_replacer = null;
        }
        $hash = $hash($text . $url_replacer). '.min';
        if (($cache = $this->cacheGet($hash, 'css')) !== false) {
            return $cache;
        }
        // remove comments
        $text = preg_replace('/(^\s*|\s*$|\/\*(?:(?!\*\/)[\s\S])*\*\/|[\r\n\t]+)/', '', $text);
        $text = preg_replace(
            '/(#(?:([a-f]|[A-F]|[0-9]){1}(?:\\2)([a-f]|[A-F]|[0-9]){1}(?:\\3))([a-f]|[A-F]|[0-9]){1}(?:\\4))\b/',
            '#$2$3$4',
            $text
        );
        $regex = '(?six)
                  \s*+;\s*(})\s*
                | \s*([*$~^|]?=|[{};,>~+-]|\s+!important\b)\s*
                | ([[(:])\s+
                | \s+([\]\)])
                | \s+(:)\s+
                (?!
                    (?>
                        [^{}"\']++
                        | \"(?:[^"\\\\]++|\\\\.)*\"
                        | \'(?:[^\'\\\\]++|\\\\.)*\' 
                    )*
                    {
                )
                | ^\s+|\s+ \z
                | (\s)\s+
                | (\#((?:[a-f]|[A-F]|[0-9]){3}))(?:\\2)?\b # replace same value hex digit to 3 character eg : #ffffff to #fff
                ';

        /**
         * Fix css url('statmenturi/');
         */
        if (is_string($url_replacer) && trim($url_replacer)
            && (
                preg_match('/^(https?:)?\/\//', $url_replacer) === 0
                && strpos($url_replacer, FCPATH) === 0
            )
        ) {
            $text = $this->internalFixCSS($text, $url_replacer);
        }

        $text = preg_replace("%{$regex}%", '$1$2$3$4$5$6$7', $text);
        $this->cachePut($hash, $text, 'css');

        return $text;
    }

    /**
     * Fix css url path that enclosed with ../ to better uses
     *     This method for internal Use Only
     *
     * @access private
     * @internal
     * @param  string       $text      css text
     * @param  string       $full_path path to css file / full path
     * @return string sanitized css
     */
    private function internalFixCSS($text, $full_path)
    {
        if (!$text || !is_string($text) || !trim($text)) {
            return $text;
        }
        if (preg_match('/url\(.+?\)/i', $text)) {
            $text = preg_replace_callback('/url\((.+?)\)/ixm', function ($c) use ($full_path) {
                $detach = trim($c[1]);
                $detach = trim($detach, '"');
                $detach = trim($detach, '\'');
                if (!preg_match('/^(https?:)?\/\//i', $detach)) {
                    // if match on full path
                    if (strpos($full_path, FCPATH) === 0) {
                        $base_url   = base_url();
                        $full_path  = is_file($full_path) ? dirname($full_path) : $full_path;
                        $detach_dir = realpath($full_path . '/' . dirname($detach));
                        $FCPATH     = preg_replace('/(\/|\\\)+/', '/', FCPATH);
                        $detach_dir = str_replace(DS, '/', $detach_dir);
                        $detach_dir = (substr($detach_dir, strlen($FCPATH)));
                        $c[0]       = preg_replace(
                            '/^https?:\/\//i',
                            '',
                            // base URL
                            // take from base URL of CI
                            trim(rtrim($base_url, '/') . '/' . (trim($detach_dir, '/') . '/' . basename($detach)))
                        );
                        $c[0] = strpos($c[0], '\'') !== false ? json_encode("//{$c[0]}") : "'//{$c[0]}'";
                    }
                    $c[0] = "url({$c[0]})";
                } else {
                    // add dummy files
                    $full_path .= '_dummy';
                    // doing fix parent directory
                    do {
                        // seed of parent files
                        $full_path = dirname($full_path);
                        if (strpos('../', $c[0]) !== 0) {
                            break;
                        }
                        $c[0] = substr($c[0], 3);
                    } while (strpos('../', $c[0]) === 0);
                    $c[0] = rtrim($full_path, '/').'/'.ltrim($detach, '/');
                    $c[0] = strpos($c[0], '\'') !== false ? json_encode("{$c[0]}") : "'{$c[0]}'";
                    $c[0] = "url({$c[0]})";
                }
                return $c[0];
            }, $text);
        }

        return $text;
    }

    /**
     * Remove non confitional comment
     *
     * @access protected
     * @param string $js
     * @return mixed
     */
    protected function removeNonConditionalCommentsJS($js)
    {
        return preg_replace('/(?:(?:\/\*(?:[^*\!]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\'|\")\/\/.*))/', '$1', $js);
    }

    /**
     * Remove brackets on semi colon
     *
     * @access protected
     * @param  string $js javascript source text
     * @return string
     */
    protected function removeBracketsSemiColon($js)
    {
        // remove new line after semicolon
        $js = preg_replace(
            '/;\n(?!\s*\/\*)/',
            ';',
            $js
        );
        return $js;
    }

    /**
     * Minify Javascript file
     *
     * @param string $file
     * @param bool   $strict
     * @return bool|string
     */
    public function jsFile($file, $strict = true, $usefirst_comment = true, $comment_only = false)
    {
        if (is_string($file) && trim($file)) {
            // if on strict
            if ($strict) {
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                // if use strict and extension is not css
                if (strtolower($ext) != 'js') {
                    return false;
                }
            }
            // if contain url ftp|http(s)
            $use_http = preg_match('/(ht|f)tps?:\/\//i', $file);
            if (!$use_http && (is_file($file) && is_readable($file))
                || $use_http
            ) {
                if (!$use_http) {
                    $file = realpath($file);
                }
                /**
                 * Check from cache
                 */
                $hash = self::HASH_FILE;
                $hash = $hash($file) . '.dist';
                if (($cache = $this->cacheGet($hash, 'js')) !== false) {
                    // trim to make sure it same result
                    $cache = trim($cache);
                    if ($comment_only) {
                        return $this->removeCommentOnlyJs($cache, $usefirst_comment);
                    } else {
                        return $this->js($cache, $usefirst_comment);
                    }
                }
                $retval = null;
                /**
                 * Check if use local
                 */
                if (!$use_http && function_exists('fopen')) {
                    if (!$file) {
                        return false;
                    }
                    if ($fp = @fopen($file, 'r+')) {
                        $retval = '';
                        while (($buffer = fgets($fp, 4096)) !== false) {
                            $retval .= $buffer;
                        }
                        fclose($fp);
                    }
                } else {
                    if ($use_http) {
                        $ctx = stream_context_create(
                            array(
                                'http'=> array(
                                    'timeout' => 10,  // 10 seconds has very very long time!!
                                )
                            )
                        );
                        $retval = file_get_contents($file, false, $ctx);
                    } else {
                        if (!$file) {
                            return false;
                        }
                        $retval = file_get_contents($file, false);
                    }
                }
                if (is_string($retval)) {
                    // trim to make sure it same result
                    $retval = trim($retval);
                    $this->cachePut($hash, $retval, 'js');
                    if ($comment_only) {
                        return $this->removeCommentOnlyJs($retval, $usefirst_comment);
                    } else {
                        return $this->js($retval, $usefirst_comment);
                    }
                }
            }
        }

        return false;
    }

    /**
     * Minify javascript without fully optimized
     *
     * @param  string  $js                javascript source
     * @param  boolean $use_first_comment allow first comment
     * @return string
     */
    public function removeCommentOnlyJs($js, $use_first_comment = true)
    {
        $hash = self::HASH_METHOD;

        $use_first_comment = (bool) $use_first_comment;
        $hash = $hash($js . 'comment' . $use_first_comment). '.min';
        if (($cache = $this->cacheGet($hash, 'css')) !== false) {
            return $cache;
        }

        /**
         * Getting first comment if available and allow to print out
         * @var string
         */
        $first_comment = '';
        if ($use_first_comment && preg_match('%^\s*/\*(?:[^*]|[\r\n]|(?:\*+(?:[^*/]|[\r\n])))*\*+/%', $js, $match)
            && !empty($match[0])
        ) {
            $first_comment = trim($match[0])."\n";
            unset($match);
        }

        // remove comments
        $js = preg_replace('%\s*/\*(?:[^*]|[\r\n]|(?:\*+(?:[^*/]|[\r\n])))*\*+/%', '', $js);
        $js = preg_replace('/(?:(?:\/\*(?:[^*\!]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\'|\")\/\/.*))/', '$1', $js);
        $js = str_replace("\r", '', $js);
        $js = str_replace(array("\n\n\n", "\n\n"), "\n", $js);
        $js = str_replace("\n\n", "\n", $js);
        $js = $first_comment . trim($js);

        $this->cachePut($hash, $js, 'js');
        return $js;
    }

    /**
     * Minify Javascript
     *
     * @param  string  $js                javascript source
     * @param  boolean $use_first_comment allow first comment
     * @return string
     */
    public function js($js, $use_first_comment = true)
    {
        if (!is_string($js) || !trim($js)) {
            return false;
        }

        $hash = self::HASH_METHOD;
        $use_first_comment = (bool) $use_first_comment;
        $hash = $hash($js . 'full' . $use_first_comment). '.min';
        if (($cache = $this->cacheGet($hash, 'css')) !== false) {
            return $cache;
        }

        /**
         * Getting first comment if available and allow to print out
         * @var string
         */
        $first_comment = '';
        if ($use_first_comment && preg_match('%^\s*/\*(?:[^*]|[\r\n]|(?:\*+(?:[^*/]|[\r\n])))*\*+/%', $js, $match)
            && !empty($match[0])
        ) {
            $first_comment = trim($match[0])."\n";
            unset($match);
        }

        // remove comments
        $js = preg_replace('%\s*/\*(?:[^*]|[\r\n]|(?:\*+(?:[^*/]|[\r\n])))*\*+/%', '', $js);
        $js = preg_replace('/(?:(?:\/\*(?:[^*\!]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\'|\")\/\/.*))/', '$1', $js);
        $js = str_replace("\r", '', $js);
        // nested spaces
        $js = str_replace('  ', ' ', str_replace(array('   ','  ', '  '), ' ', $js));
        $js = str_replace(') {', '){', $js);
        $jsr = explode("\n", $js);
        $js  = '';
        $last = null;
        foreach ($jsr as $key => $v) {
            if (trim($v) == '' || substr(trim($v), 0, 2) == '//') {
                continue;
            }
            $next = isset($jsr[$key+1]) ? $jsr[$key+1] : null;
            if ($next !== null && trim($next) == '') {
                $next =  isset($jsr[$key+2]) ? $jsr[$key+2] : null;
            }
            $v = trim($v);
            if ($next != '' && $next[0] === '.' || $v[0] == '}'
                && (stripos($last, 'return') === 0 || stripos($last, 'if (') === 0)
            ) {
                $js .= "{$v}";
            } else {
                $js .= "\n{$v}";
            }
            $last = $v;
        }

        unset($jsr); // freed
        $js = preg_replace('/(?>(?!\'|\"|\/))\s*(&&|\|\|)\s*/', '$1', $js);
        $js = preg_replace('/(?>(?!\'|\"|\/))([\)\}])\s*{/', '$1{', $js);
        $js = preg_replace('/(?>(?!\'|\"|\/))([a-z0-9\)])\n\./i', '$1.', $js);
        $js = preg_replace('/(?>(?!\'|\"|\/))([,:])\n([a-z\$\_])/i', '$1$2', $js);
        $js = preg_replace('/(?>(?!\'|\"|\/)),[ \n]([\"\'a-z\$\_])/i', ',$1', $js);
        $js = preg_replace('/(?>(?!\'|\"|\/))(?:[ ])?:(?:\s*)?(\{)?/i', ':$1', $js);
        $js = preg_replace("/(function)\s*\(((?>[\(]).+)?\)\s*\{(?:\s*([a-zA-Z]+))/", "\$1(\$2){\$3", $js);
        $js = preg_replace('/(?>(?!\'|\" | \/))(function) \(\)/', "\$1()", $js);
        $js = preg_replace('/var\s*((?:.+[^\s*]|[0-9a-zA-Z]+|[0-9a-zA-Z\$]+))\s*=\s*/', "var \$1=", $js);
        $js = preg_replace('/(?:[ ]+|\n[ ])([\=\&\:\$\.\}\{]|if \()/', " $1", $js);
        $js = preg_replace('/(\n+)[\t ]+|((\n|\n\r|([\t ]+)\n)+)/i', "\n", $js);
        $js = preg_replace('/(?>(?!\'|\"|\/)) ((?:\!|\=)?==?|\:|\&\&|\<|\>|\<\>|<\=|\?|\|\||\-) ?([\{\(\'\"\$a-z0-9]|false|null)/i', '$1$2', $js);
        $js = preg_replace('/(?>(?!\'|\"|\/))([a-z])(=|\:) ([\{\(\'\"\$a-z0-9]|false|null)/i', '$1$2$3', $js);
        $js = preg_replace('/(?>(?!\'|\"|\/));\n(?!\s*\/\*)/', ';', $js);
        $js = preg_replace('/(?>(?!\'|\"|\/))(;|)\s*}/', "}", $js);
        $js = str_replace(array("}\n}", "}\n}", "} }"), "}}", $js);
        $js = str_replace(array(")\n}", ")\n}", ") }"), ")}", $js);
        $js = $first_comment.trim($js);

        $this->cachePut($hash, $js, 'js');
        return $js;
    }
}
