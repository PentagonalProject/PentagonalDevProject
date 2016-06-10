<?php
/**
 * Template Header
 */
?>
<!DOCTYPE html>
<html <?php language_attributes();?>>
<head>
 <meta charset="utf-8">
 <title><?php echo get_the_title();?></title>
 <link rel="stylesheet" href="<?php
    echo generateDynamicAssetCss(
        array(
            'bootstrap/css/bootstrap.css',
        )
    );
?>">
 <script src="<?php
    echo generateDynamicAssetJs(
        array(
            'js/jquery.min',
            'js/jquery-migrate.min',
            'bootstrap/js/bootstrap',
        )
    );
?>"></script>
</head>
<body<?php body_class();?>>
