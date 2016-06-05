<?php

class CI_Unit_test {

    /**
     * Active flag
     *
     * @var	bool
     */
    public $active = true;

    /**
     * Test results
     *
     * @var	array
     */
    public $results = array();

    /**
     * Strict comparison flag
     *
     * Whether to use === or == when comparing
     *
     * @var	bool
     */
    public $strict = false;

    /**
     * Template
     *
     * @var	string
     */
    protected $_template = null;

    /**
     * Template rows
     *
     * @var	string
     */
    protected $_template_rows = null;

    /**
     * List of visible test items
     *
     * @var	array
     */
    protected $_test_items_visible	= array(
        'test_name',
        'test_datatype',
        'res_datatype',
        'result',
        'file',
        'line',
        'notes'
    );

    // --------------------------------------------------------------------

    /**
     * Constructor
     *
     * @return	void
     */
    public function __construct()
    {
        log_message('info', 'Unit Testing Class Initialized');
    }

    // --------------------------------------------------------------------

    /**
     * Run the tests
     *
     * Runs the supplied tests
     *
     * @param	array	$items
     * @return	void
     */
    public function set_test_items($items)
    {
        if ( ! empty($items) && is_array($items))
        {
            $this->_test_items_visible = $items;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Run the tests
     *
     * Runs the supplied tests
     *
     * @param	mixed	$test
     * @param	mixed	$expected
     * @param	string	$test_name
     * @param	string	$notes
     * @return	string
     */
    public function run($test, $expected = true, $test_name = 'undefined', $notes = '')
    {
        if ($this->active === false)
        {
            return false;
        }

        if (in_array(
                $expected,
                array('is_object', 'is_string', 'is_bool', 'is_true', 'is_false', 'is_int', 'is_numeric', 'is_float', 'is_double', 'is_array', 'is_null', 'is_resource'),
                true
            )
        ) {
            $expected = str_replace('is_double', 'is_float', $expected);
            $result = $expected($test);
            $extype = str_replace(array('true', 'false'), 'bool', str_replace('is_', '', $expected));
        } else {
            $result = ($this->strict === true) ? ($test === $expected) : ($test == $expected);
            $extype = gettype($expected);
        }

        $back = $this->_backtrace();

        $report = array (
            'test_name'     => $test_name,
            'test_datatype' => gettype($test),
            'res_datatype'  => $extype,
            'result'        => ($result === true) ? 'passed' : 'failed',
            'file'          => $back['file'],
            'line'          => $back['line'],
            'notes'         => $notes
        );

        $this->results[] = $report;

        return $this->report($this->result(array($report)));
    }

    // --------------------------------------------------------------------

    /**
     * Generate a report
     *
     * Displays a table with the test data
     *
     * @param	array	 $result
     * @return	string
     */
    public function report($result = array())
    {
        if (count($result) === 0)
        {
            $result = $this->result();
        }

        $CI =& get_instance();
        $CI->load->language('en', 'system');

        $lang['ut_test_name'] = 'Test Name';
        $lang['ut_test_datatype'] = 'Test Datatype';
        $lang['ut_res_datatype'] = 'Expected Datatype';
        $lang['ut_result'] = 'Result';
        $lang['ut_undefined'] = 'Undefined Test Name';
        $lang['ut_file'] = 'File Name';
        $lang['ut_line'] = 'Line Number';
        $lang['ut_passed'] = 'Passed';
        $lang['ut_failed'] = 'Failed';
        $lang['ut_boolean'] = 'Boolean';
        $lang['ut_integer'] = 'Integer';
        $lang['ut_float'] = 'Float';
        $lang['ut_double'] = 'Float'; // can be the same as float
        $lang['ut_string'] = 'String';
        $lang['ut_array'] = 'Array';
        $lang['ut_object'] = 'Object';
        $lang['ut_resource'] = 'Resource';
        $lang['ut_null'] = 'Null';
        $lang['ut_notes'] = 'Notes';

        $this->_parse_template();

        $r = '';
        foreach ($result as $res)
        {
            $table = '';

            foreach ($res as $key => $val) {
                if ($key === 'ut_result') {
                    if ($val === 'ut_passed') {
                        $val = '<span style="color: #0C0;">'.$CI->lang->line($lang[$val]).'</span>';
                    } elseif ($val === 'ut_failed') {
                        $val = '<span style="color: #C00;">'.$CI->lang->line($lang[$val]).'</span>';
                    }
                }

                $table .= str_replace(
                    array('{item}', '{result}'),
                    array($CI->lang->line($lang[$key]), $CI->lang->line($lang[$val])),
                    $this->_template_rows
                );
            }

            $r .= str_replace('{rows}', $table, $this->_template);
        }

        return $r;
    }

    // --------------------------------------------------------------------

    /**
     * Use strict comparison
     *
     * Causes the evaluation to use === rather than ==
     *
     * @param	bool	$state
     * @return	void
     */
    public function use_strict($state = true)
    {
        $this->strict = (bool) $state;
    }

    // --------------------------------------------------------------------

    /**
     * Make Unit testing active
     *
     * Enables/disables unit testing
     *
     * @param	bool
     * @return	void
     */
    public function active($state = true)
    {
        $this->active = (bool) $state;
    }

    // --------------------------------------------------------------------

    /**
     * Result Array
     *
     * Returns the raw result data
     *
     * @param	array	$results
     * @return	array
     */
    public function result($results = array())
    {
        if (count($results) === 0)
        {
            $results = $this->results;
        }

        $retval = array();
        foreach ($results as $result)
        {
            $temp = array();
            foreach ($result as $key => $val) {
                if ( ! in_array($key, $this->_test_items_visible)) {
                    continue;
                } elseif (in_array($key, array('test_name', 'test_datatype', 'test_res_datatype', 'result'), true)) {
                    $val = 'ut_'.$val;
                }

                $temp['ut_'.$key] = $val;
            }

            $retval[] = $temp;
        }

        return $retval;
    }

    // --------------------------------------------------------------------

    /**
     * Set the template
     *
     * This lets us set the template to be used to display results
     *
     * @param	string
     * @return	void
     */
    public function set_template($template)
    {
        $this->_template = $template;
    }

    // --------------------------------------------------------------------

    /**
     * Generate a backtrace
     *
     * This lets us show file names and line numbers
     *
     * @return	array
     */
    protected function _backtrace()
    {
        $back = debug_backtrace();
        return array(
            'file' => (isset($back[1]['file']) ? $back[1]['file'] : ''),
            'line' => (isset($back[1]['line']) ? $back[1]['line'] : '')
        );
    }

    // --------------------------------------------------------------------

    /**
     * Get Default Template
     *
     * @return	string
     */
    protected function _default_template()
    {
        $this->_template = "\n".'<table style="width:100%; font-size:small; margin:10px 0; border-collapse:collapse; border:1px solid #CCC;">{rows}'."\n</table>";

        $this->_template_rows = "\n\t<tr>\n\t\t".'<th style="text-align: left; border-bottom:1px solid #CCC;">{item}</th>'
            ."\n\t\t".'<td style="border-bottom:1px solid #CCC;">{result}</td>'."\n\t</tr>";
    }

    // --------------------------------------------------------------------

    /**
     * Parse Template
     *
     * Harvests the data within the template {pseudo-variables}
     *
     * @return	void
     */
    protected function _parse_template()
    {
        if ($this->_template_rows !== null)
        {
            return;
        }

        if ($this->_template === null OR ! preg_match('/\{rows\}(.*?)\{\/rows\}/si', $this->_template, $match))
        {
            $this->_default_template();
            return;
        }

        $this->_template_rows = $match[1];
        $this->_template = str_replace($match[0], '{rows}', $this->_template);
    }

}

/**
 * Helper function to test boolean true
 *
 * @param	mixed	$test
 * @return	bool
 */
function is_true($test)
{
    return ($test === true);
}

/**
 * Helper function to test boolean false
 *
 * @param	mixed	$test
 * @return	bool
 */
function is_false($test)
{
    return ($test === false);
}
