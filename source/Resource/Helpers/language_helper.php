<?php
/**
 * Lang
 *
 * Fetches a language variable and optionally outputs a form label
 *
 * @param	string	$line		The language line
 * @param	string	$for		The "for" value (id of the form element)
 * @param	array	$attributes	Any additional HTML attributes
 * @return	string
 */
function lang($line, $for = '', $attributes = array())
{
    $line = __($line);

    if ($for !== '') {
        $line = '<label for="'.$for.'"'._stringify_attributes($attributes).'>'.$line.'</label>';
    }

    return $line;
}

/**
 * @param string $line string of language
 * @param null $textdomain
 * @return mixed
 */
function __($line, $textdomain = null)
{
    if (!is_string($line)) {
        return $line;
    }
    /**
     * Get Mapped Object
     */
    $lang = get_instance()->getMapped('lang');
    if (!is_object($lang) || method_exists($lang, 'traslate')) {
        show_error('Language object does not exists!');
    }
    return $lang->translate($line, $textdomain);
}

/**
 * @param string $line string of language
 * @param null $textdomain
 * @return mixed
 */
function _e($line, $textdomain = null)
{
    echo __($line, $textdomain);
}
