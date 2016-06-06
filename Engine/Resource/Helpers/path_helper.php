<?php
if (! function_exists('set_realpath')) {
    /**
     * Set Realpath
     *
     * @param	string $path
     * @param	bool   $check_existance checks to see if the path exists
     * @return	string
     */
    function set_realpath($path, $check_existance = false)
    {
        $CI = get_instance();
        // Security check to make sure the path is NOT a URL. No remote file inclusion!
        if (preg_match('#^(http:\/\/|https:\/\/|www\.|ftp)#i', $path)
            || filter_var($path, FILTER_VALIDATE_IP) === $path
        ) {
            show_error(
                $CI->lang->translate('The path you submitted must be a local server path, not a URL')
            );
        }

        // Resolve the path
        if (realpath($path) !== false) {
            $path = realpath($path);
        } elseif ($check_existance && ! is_dir($path) && ! is_file($path)) {
            show_error(
                $CI->lang->translate('Not a valid path: ') . $path
            );
        }

        // Add a trailing slash, if this is a directory
        return is_dir($path) ? rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR : $path;
    }
}
