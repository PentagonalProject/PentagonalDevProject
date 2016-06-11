<?php

/**
 * Class UserMenu
 */
class UserMenu extends CI_Model
{
    public function initialize()
    {
        static $hascall;
        if ($hascall) {
            return;
        }
        $hascall = true;
    }
}
