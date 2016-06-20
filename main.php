<?php
/**
 * Openshift Development Only
 * Remove this if use openshift
 * or change values on environment
 */
if (!getenv('OPENSHIFT_MYSQL_DB_HOST')) {
   putenv('OPENSHIFT_MYSQL_DB_HOST=localhost');
}
if (!getenv('OPENSHIFT_MYSQL_DB_PORT')) {
    putenv('OPENSHIFT_MYSQL_DB_PORT=3306');
}
if (!getenv('OPENSHIFT_MYSQL_DB_USERNAME')) {
    putenv('OPENSHIFT_MYSQL_DB_USERNAME=root');
}
if (!getenv('OPENSHIFT_MYSQL_DB_PASSWORD')) {
    putenv('OPENSHIFT_MYSQL_DB_PASSWORD=mysql');
}
if (!getenv('OPENSHIFT_APP_NAME')) {
    putenv('OPENSHIFT_APP_NAME=codeigniter');
}

define('ROOT', __FILE__);
require_once __DIR__ . '/Engine/Loader.php';
