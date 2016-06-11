<?php

/**
 * Class AdminMenu
 */
class AdminMenu extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->hasData('list_menu_sidebar') ||
            $this->setData('list_menu_sidebar', array(), true);
        $this->hasData('list_menu_top_menu') ||
            $this->setData('list_menu_top_menu', array(), true);
    }

    public function initialize()
    {
        static $hascall;
        if ($hascall) {
            return $this;
        }
        $hascall = true;
        return $this;
    }
}
