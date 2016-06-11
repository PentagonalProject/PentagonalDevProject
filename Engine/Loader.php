<?php
/**
 * Must be Defined Root
 */
if (!defined('ROOT')) {
    return;
}

/*
 * --------------------------------------------------------------------
 * LOAD THE BOOTSTRAP FILE
 * --------------------------------------------------------------------
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Determinator.php'; // include fisrt
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Functions' . DIRECTORY_SEPARATOR . 'Core.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Functions' . DIRECTORY_SEPARATOR . 'Url.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Functions' . DIRECTORY_SEPARATOR . 'Alternate.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Functions' . DIRECTORY_SEPARATOR . 'User.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Processor.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'DataCollection.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Hook.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'DynamicAsset.php';

// run
return Processor::run();
