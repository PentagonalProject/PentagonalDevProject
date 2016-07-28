<?php
return array(
    'path' => array(
        'system' => 'System',
        'resource' => 'Resource',
        'view' => 'Views',
        'template' => 'Template',
    ),
    'app' => array(
        'environment' => 'development',
        'select_db' => 'default', // selected database (empty to default)
        'encryption_key' => '(*^@HkJKLHkHS875654Khglkg)*=-(%*jh&jgkk'
    ),
    'db' => array(
        'default' => array(
            'hostname' => getenv('OPENSHIFT_MYSQL_DB_HOST'),
            'username' => getenv('OPENSHIFT_MYSQL_DB_USERNAME'),
            'password' => getenv('OPENSHIFT_MYSQL_DB_PASSWORD'),
            'database' => getenv('OPENSHIFT_APP_NAME'),
            'dbdriver' => 'mysqli',
            'dbprefix' => '',
        )
    )
);
