<?php

/** @noinspection PhpUndefinedClassInspection */
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
        if (is_admin_area()) {
            $ci->load->model('AdminTemplateModel', MODEL_NAME_TEMPLATE_ADMIN);
            $template = $ci
                ->load
                ->get(MODEL_NAME_TEMPLATE_ADMIN)
                ->init()
                ->getActiveTemplateDirectory();
            if ($template) {
                $ci->load->setActiveTemplate($template);
                $adminExist = true;
            }
        }

        if (!$adminExist) {
            $ci->load->model('AdminTemplateModel', MODEL_NAME_TEMPLATE_USER);
            $ci->load->setActiveTemplate(
                $ci->load->get(MODEL_NAME_TEMPLATE_USER)->getActiveTemplateDirectory()
            );
        }

        // call the index
        $this->index();
    }
}
