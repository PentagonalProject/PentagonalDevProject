<?php

/** @noinspection PhpUndefinedClassInspection */
class Auth extends CI_Model
{
    public function __construct()
    {
        /** @noinspection PhpUndefinedClassInspection */
        parent::__construct();
        $this->load->model('User', MODEL_NAME_USER);
        $this->load->library('session');
    }
}
