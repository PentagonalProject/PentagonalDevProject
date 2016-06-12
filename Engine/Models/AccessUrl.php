<?php

/**
 * Class Access
 */
class AccessUrl extends CI_Model
{
    protected $user_level;

    public function __construct()
    {
        parent::__construct();
    }

    public function buildRegex($url, $caseSensistive = false)
    {
        $reg = '/^\/?';
        $reg .= preg_quote(trim($url, '/'), '/');
        $reg .= '(\/+)?$/'.($caseSensistive ? '' : 'i');
        return $reg;
    }

    /**
     * @param string       $url
     * @param string|array $level
     * @param bool         $caseSensistive
     *
     * @return bool
     */
    public function addAccess($url, $level, $caseSensistive = false)
    {
        $url =  trim($url, '/');
        if (!$caseSensistive) {
            $url = strtolower($url);
        }

        $data = $this->getData('access', array());
        if ($url === '') {
            $url = '%';
        }
        if (!isset($data[$url])) {
            if (is_array($level)) {
                foreach ($level as $key => $value) {
                    if (!is_string($value)) {
                        continue;
                    }
                    $value = strtolower(trim($value));
                    if ($value == '') {
                        continue;
                    }
                    $level[$key] = $value;
                }
            } else {
                if (!is_string($level)) {
                    return false;
                }
                $level = strtolower(trim($level));
                if ($level == '') {
                    return false;
                }
            }

            $data[$url] = $level;
            $this->setData('access', $data);
            return $data[$url];
        }

        return false;
    }

    public function isAllowed($uri = null)
    {
        if ($uri === null) {
            $uri = ltrim($this->uri->uri_string(), '/');
            $uri = explode('/', $uri);
            array_shift($uri);
            $uri = '/'.implode('/', $uri);
        }
        if ($uri == '/' || empty($uri) || !is_string($uri)||! trim($uri) || $this->user_level === 'administrator') {
            return true;
        }

        static $isAllowed = array();
        if (isset($isAllowed[$uri])) {
            return $isAllowed[$uri];
        }

        $data = $this->getData('access', array());
        foreach ($data as $key => $value) {
            // fix
            $key = $key === '%' ? '' : $key;
            $regex = $this->buildRegex($key);
            if (preg_match($regex, $uri)) {
                if (!is_array($value)) {
                    $isAllowed[$uri] = false;
                    return false;
                }
                if (!empty($value[$uri]) && $value[$uri] == $this->user_level) {
                    return ($isAllowed[$uri] =  true);
                } else {
                    return ($isAllowed[$uri] =  false);
                }
            }
        }

        $isAllowed[$uri] = false;
        return false;
    }
}
