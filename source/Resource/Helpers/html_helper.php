<?php
/**
 * Heading
 *
 * Generates an HTML heading tag.
 *
 * @param	string     $data content
 * @param	int|string $h heading level
 * @param	string     $attributes
 * @return	string
 */
function heading($data = '', $h = '1', $attributes = '')
{
    return '<h'.$h._stringify_attributes($attributes).'>'.$data.'</h'.$h.'>';
}

/**
 * Unordered List
 *
 * Generates an HTML unordered list from an single or multi-dimensional array.
 *
 * @param	array
 * @param	mixed
 * @return	string
 */
function ul($list, $attributes = '')
{
    return _list('ul', $list, $attributes);
}

/**
 * Ordered List
 *
 * Generates an HTML ordered list from an single or multi-dimensional array.
 *
 * @param	array
 * @param	mixed
 * @return	string
 */
function ol($list, $attributes = '')
{
    return _list('ol', $list, $attributes);
}

/**
 * Generates the list
 *
 * Generates an HTML ordered list from an single or multi-dimensional array.
 *
 * @param	string
 * @param	mixed
 * @param	mixed
 * @param	int
 * @return	string
 */
function _list($type = 'ul', $list = array(), $attributes = '', $depth = 0)
{
    // If an array wasn't submitted there's nothing to do...
    if (! is_array($list)) {
        return $list;
    }

    // Set the indentation based on the depth
    $out = str_repeat(' ', $depth)
        // Write the opening list tag
        .'<'.$type._stringify_attributes($attributes).">\n";


    // Cycle through the list elements.  If an array is
    // encountered we will recursively call _list()
    foreach ($list as $key => $val) {
        $_last_list_item = $key;

        $out .= str_repeat(' ', $depth + 2).'<li>';

        if (! is_array($val)) {
            $out .= $val;
        } else {
            $out .= $_last_list_item."\n"._list($type, $val, '', $depth + 4).str_repeat(' ', $depth + 2);
        }

        $out .= "</li>\n";
    }

    // Set the indentation for the closing tag and apply it
    return $out.str_repeat(' ', $depth).'</'.$type.">\n";
}

/**
 * Image
 *
 * Generates an <img /> element
 *
 * @param	mixed
 * @param	bool
 * @param	mixed
 * @return	string
 */
function img($src = '', $index_page = false, $attributes = '')
{
    if (! is_array($src)) {
        $src = array('src' => $src);
    }

    // If there is no alt attribute defined, set it to an empty string
    if (! isset($src['alt'])) {
        $src['alt'] = '';
    }

    $img = '<img';

    foreach ($src as $k => $v) {
        if ($k === 'src' && ! preg_match('#^([a-z]+:)?//#i', $v)) {
            if ($index_page === true) {
                $img .= ' src="'.get_instance()->config->site_url($v).'"';
            } else {
                $img .= ' src="'.get_instance()->config->slash_item('base_url').$v.'"';
            }
        } else {
            $img .= ' '.$k.'="'.$v.'"';
        }
    }

    return $img._stringify_attributes($attributes).' />';
}


/**
 * Doctype
 *
 * Generates a page document type declaration
 *
 * Examples of valid options: html5, xhtml-11, xhtml-strict, xhtml-trans,
 * xhtml-frame, html4-strict, html4-trans, and html4-frame.
 * All values are saved in the doctypes config file.
 *
 * @param	string	type	The doctype to be generated
 * @return	string
 */
function doctype($type = 'xhtml1-strict')
{
    return Processor::config('doctypes', $type);
}

/**
 * Link
 *
 * Generates link to a CSS file
 *
 * @param	mixed	$href stylesheet hrefs or an array
 * @param	string	$rel rel
 * @param	string	$type type
 * @param	string	$title title
 * @param	string	$media media
 * @param	bool	$index_page should index_page be added to the css path
 * @return	string
 */
function link_tag($href = '', $rel = 'stylesheet', $type = 'text/css', $title = '', $media = '', $index_page = false)
{
    $CI =& get_instance();
    $CI->load->helper('url');

    $link = '<link ';

    if (is_array($href)) {
        foreach ($href as $k => $v) {
            if ($k === 'href' && ! preg_match('#^([a-z]+:)?//#i', $v)) {
                if ($index_page === true) {
                    $link .= 'href="'.site_url($v).'" ';
                } else {
                    $link .= 'href="'.$CI->config->slash_item('base_url').$v.'" ';
                }
            } else {
                $link .= $k.'="'.$v.'" ';
            }
        }
    } else {
        if (preg_match('#^([a-z]+:)?//#i', $href)) {
            $link .= 'href="'.$href.'" ';
        } elseif ($index_page === true) {
            $link .= 'href="'.site_url($href).'" ';
        } else {
            $link .= 'href="'.$CI->config->slash_item('base_url').$href.'" ';
        }

        $link .= 'rel="'.$rel.'" type="'.$type.'" ';

        if ($media !== '') {
            $link .= 'media="'.$media.'" ';
        }

        if ($title !== '') {
            $link .= 'title="'.$title.'" ';
        }
    }

    return $link."/>\n";
}

/**
 * Generates meta tags from an array of key/values
 *
 * @param	array
 * @param	string
 * @param	string
 * @param	string
 * @return	string
 */
function meta($name = '', $content = '', $type = 'name', $newline = "\n")
{
    // Since we allow the data to be passes as a string, a simple array
    // or a multidimensional one, we need to do a little prepping.
    if (! is_array($name)) {
        $name = array(array('name' => $name, 'content' => $content, 'type' => $type, 'newline' => $newline));
    } elseif (isset($name['name'])) {
        // Turn single array into multidimensional
        $name = array($name);
    }

    $str = '';
    foreach ($name as $meta) {
        $type        = (isset($meta['type']) && $meta['type'] !== 'name')    ? 'http-equiv' : 'name';
        $name        = isset($meta['name'])                    ? $meta['name'] : '';
        $content    = isset($meta['content'])                ? $meta['content'] : '';
        $newline    = isset($meta['newline'])                ? $meta['newline'] : "\n";

        $str .= '<meta '.$type.'="'.$name.'" content="'.$content.'" />'.$newline;
    }

    return $str;
}

/**
 * Generates HTML BR tags based on number supplied
 *
 * @deprecated	3.0.0	Use str_repeat() instead
 * @param	int	$count	Number of times to repeat the tag
 * @return	string
 */
function br($count = 1)
{
    return str_repeat('<br />', $count);
}

/**
 * Generates non-breaking space entities based on number supplied
 *
 * @deprecated	3.0.0	Use str_repeat() instead
 * @param	int
 * @return	string
 */
function nbs($num = 1)
{
    return str_repeat('&nbsp;', $num);
}
