<?php
class Controller404 extends CI_Controller
{
    /**
     * Index 404
     */
    public function index()
    {
        show_404();
    }

    public function _remap()
    {
        $adminExist = false;
        $ci = get_instance();
        if ($ci->load->router->class == 'AdminController') {
            $ci->load->model('AdminTemplateModel', 'model.template.admin');
            $template = $ci
                ->load
                ->get('model.template.admin')
                ->init()
                ->getActiveTemplateDirectory();
            if ($template)) {
                $ci->load->setActiveTheme($template);
                $adminExist = true;
            }
        }

        if (!$adminExist) {
            $ci->load->model('AdminTemplateModel', 'model.template.user');
            $ci->load->setActiveTheme(
                $ci->load->get('model.template.user')->getActiveTemplateDirectory()
            );
        }

        // call the index
        $this->index();
    }
}
