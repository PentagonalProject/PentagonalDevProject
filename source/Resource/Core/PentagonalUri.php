<?php
class PentagonalUri extends CI_URI
{
    /**
     * Parse REQUEST_URI
     * use as rewrite
     *
     * Will parse REQUEST_URI and automatically detect the URI from it,
     * while fixing the query string if necessary.
     *
     * @return	string
     */
    protected function _parse_request_uri()
    {
        if ( ! isset($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME'])) {
            return '';
        }

        // parse_url() returns false if no host is present, but the path or query string
        // contains a colon followed by a number
        $uri = parse_url('http://dummy'.$_SERVER['REQUEST_URI']);
        $query = isset($uri['query']) ? $uri['query'] : '';
        $uri = isset($uri['path']) ? $uri['path'] : '';

        if (isset($_SERVER['SCRIPT_NAME'][0])) {
            $ft = false;
            // check containers of htaccess
            if (file_exists(FCPATH . '.htaccess')) {
                $fcontent = @file_get_contents(FCPATH . '.htaccess');
                if ($fcontent) {
                    // if on rewrite
                    if (preg_match('/RewriteCond\s*\%\{REQUEST_FILENAME\}\s*(\!\-d|\!\-f)/smi', $fcontent)) {
                        $ft = true;
                    }
                }
            }
            // if not detect as rewrite use script name
            if (!$ft && strpos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
                $uri = (string) substr($uri, strlen($_SERVER['SCRIPT_NAME']));
            } elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0) {
                $uri = (string) substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
            }
        }

        // This section ensures that even on servers that require the URI to be in the query string (Nginx) a correct
        // URI is found, and also fixes the QUERY_STRING server var and $_GET array.
        if (trim($uri, '/') === '' && strncmp($query, '/', 1) === 0) {
            $query = explode('?', $query, 2);
            $uri = $query[0];
            $_SERVER['QUERY_STRING'] = isset($query[1]) ? $query[1] : '';
        } else  {
            $_SERVER['QUERY_STRING'] = $query;
        }

        parse_str($_SERVER['QUERY_STRING'], $_GET);

        if ($uri === '/' || $uri === '') {
            return '/';
        }

        // Do some final cleaning of the URI and return it
        return $this->_remove_relative_directory($uri);
    }

}
