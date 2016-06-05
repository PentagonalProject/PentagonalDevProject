<?php
if ( ! function_exists('byte_format'))
{
    /**
     * Formats a numbers as bytes, based on size, and adds the appropriate suffix
     *
     * @param	mixed	will be cast as int
     * @param	int
     * @return	string
     */
    function byte_format($num, $precision = 1)
    {
        $CI =& get_instance();
        if ($num >= 1000000000000)
        {
            $num = round($num / 1099511627776, $precision);
            $unit = $CI->lang->line('TB');
        } elseif ($num >= 1000000000) {
            $num = round($num / 1073741824, $precision);
            $unit = $CI->lang->line('GB');
        } elseif ($num >= 1000000) {
            $num = round($num / 1048576, $precision);
            $unit = $CI->lang->line('MB');
        } elseif ($num >= 1000) {
            $num = round($num / 1024, $precision);
            $unit = $CI->lang->line('KB');
        } else {
            $unit = $CI->lang->line('Bytes');
            return number_format($num).' '.$unit;
        }

        return number_format($num, $precision).' '.$unit;
    }
}
