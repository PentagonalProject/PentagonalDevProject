<?php
namespace Admin;

/**
 * Class Login
 *
 * @package Admin
 */
class Login extends \CI_Controller
{
    public function index()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $title = \Hook::apply('login_title', __('Login To Admin Area'));

        /** @noinspection PhpUndefinedMethodInspection */
        // inject title hook
        \Hook::add('admin_title', function () use ($title) {
            return $title;
        });
        // addd variable
        $this->addVar();
        $this->load->view('login');
    }

    protected function addVar()
    {
        $form = array(
            'password' => array(
                'name' => 'password',
                'value' => ''
            ),
            'username' => array(
                'name' => 'username',
                'value' => (!is_string($this->input->post('username')) ? '' : trim($this->input->post('username')))
            ),
            'remember' => array(
                'name' => 'remember',
                'value' => boolval($this->input->post('remember'))
            )
        );
        $this->load->vars('form', $form);
    }
}
