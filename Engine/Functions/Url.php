<?php
/**
 * Get base url within defined Path
 *
 * @param string $rootpath
 *
 * @return bool|string
 */
function get_path_from_root($rootpath)
{
    if (is_string($rootpath) && ($rootpath = realpath($rootpath)) !== false) {
        static $fcpath;
        if (!isset($fcpath)) {
            $fcpath = realpath(FCPATH);
        }
        if (strpos($rootpath, $fcpath) === 0) {
            return '/'. ltrim(
                str_replace(
                    DIRECTORY_SEPARATOR,
                    '/',
                    substr($rootpath, strlen($fcpath)-1)
                ),
                '/'
            );
        }
    }

    return false;
}

/**
 * Get asset URL
 *
 * @param string $url_path
 *
 * @return string
 */
function asset_url($url_path = '')
{
    static $path;
    if (!isset($path)) {
        $path = get_path_from_root(ASSETPATH);
    }
    if (!is_string($url_path)) {
        settype($url_path, 'string');
    }
    if (trim($url_path, '/') == '') {
        $url_path = '/';
    } elseif (! strpos($url_path, '\\') !== false || strpos($url_path, '/') !== false) {
        $url_path = preg_replace('/(\\\|\/)+/', '/', $url_path);
        $url_path = '/'.ltrim($url_path, '/');
    }
    return base_url($path . $url_path);
}

/**
 * Get asset URL
 *
 * @param string $url_path
 *
 * @return string
 */
function templates_uri($url_path = '')
{
    static $path;
    if (!isset($path)) {
        $path = get_path_from_root(TEMPLATEPATH);
    }
    if (!is_string($url_path)) {
        settype($url_path, 'string');
    }
    if (trim($url_path, '/') == '') {
        $url_path = '/';
    } elseif (! strpos($url_path, '\\') !== false || strpos($url_path, '/') !== false) {
        $url_path = preg_replace('/(\\\|\/)+/', '/', $url_path);
        $url_path = '/'.ltrim($url_path, '/');
    }
    return base_url($path . $url_path);
}

/**
 * Get asset URL
 *
 * @param string $url_path
 *
 * @return string
 */
function template_uri($url_path = '')
{
    static $path;
    if (!isset($path)) {
        $CI =& get_instance();
        $CI->load->model('TemplateModel', MODEL_NAME_TEMPLATE_USER);
        $path = get_path_from_root($CI->load->get(MODEL_NAME_TEMPLATE_USER)->getActiveTemplateDirectory());
    }

    if (!is_string($url_path)) {
        settype($url_path, 'string');
    }
    if (trim($url_path, '/') == '') {
        $url_path = '/';
    } elseif (! strpos($url_path, '\\') !== false || strpos($url_path, '/') !== false) {
        $url_path = preg_replace('/(\\\|\/)+/', '/', $url_path);
        $url_path = '/'.ltrim($url_path, '/');
    }
    return base_url($path . $url_path);
}

/**
 * Get asset URL
 *
 * @param string $url_path
 *
 * @return string
 */
function admin_template_uri($url_path = '')
{
    static $path;
    if (!isset($path)) {
        $CI =& get_instance();
        $CI->load->model('TemplateModel', MODEL_NAME_TEMPLATE_ADMIN);
        $path = get_path_from_root($CI->load->get(MODEL_NAME_TEMPLATE_ADMIN)->getActiveTemplateDirectory());
    }

    if (!is_string($url_path)) {
        settype($url_path, 'string');
    }
    if (trim($url_path, '/') == '') {
        $url_path = '/';
    } elseif (! strpos($url_path, '\\') !== false || strpos($url_path, '/') !== false) {
        $url_path = preg_replace('/(\\\|\/)+/', '/', $url_path);
        $url_path = '/'.ltrim($url_path, '/');
    }
    return base_url($path . $url_path);
}

/**
 * Current URL WIth Query
 *
 * Returns the full URL (including segments) of the page where this
 * function is placed
 *
 * @return	string
 */
function current_really_url()
{
    $CI =& get_instance();
    $query = isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : '';
    return $CI->config->site_url($CI->uri->uri_string().$query);
}

/**
 * Getting Admin Url
 *
 * @param string $url_path
 *
 * @return string
 */
function admin_url($url_path = '')
{
    if (!is_string($url_path)) {
        settype($url_path, 'string');
    }

    if (trim($url_path, '/') == '') {
        $url_path = '/';
    } elseif (! strpos($url_path, '\\') !== false || strpos($url_path, '/') !== false) {
        $url_path = preg_replace('/(\\\|\/)+/', '/', $url_path);
        $url_path = '/'.ltrim($url_path, '/');
    }

    return base_url(ADMINPATH . $url_path);
}
