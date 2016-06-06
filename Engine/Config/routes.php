<?php
/**
 * Dont change anything here
 */
return array(
    # config
    'translate_uri_dashes' => false,
    'default_controller' => 'DefaultController',
    # controller
    'admin(\/+(.*))?'  => 'AdminController',
    // especially module
    'module(\/+(.*))?' => 'Module',
    '(.*)?' => 'DefaultController',
);
