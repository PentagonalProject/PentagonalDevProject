<?php

/** @noinspection PhpUndefinedClassInspection */
class DefaultController extends CI_Controller
{
    protected $segmentsMethod = array(
        'member' => 'member',
    );

    public function index()
    {
        $this->output->enable_profiler(true);
        $segment = $this->uri->segment(1);
        if (!$segment) {
            $this->load->view('home');
            return;
        } else {
            if (isset($this->segmentsMethod[$segment]) && method_exists($this, $segment)) {
                $this->{$this->segmentsMethod[$segment]}();
                return;
            }
        }
        // default 404
        show_404();
    }

    public function member()
    {
        $this->output->set_output('dummy output');
    }
}
