<?php
/**
 * Template Header
 */
?>
<!DOCTYPE html>
<html <?php language_attributes();?>>
<head>
 <meta charset="<?php site_info('charset');?>">
 <title><?php echo get_the_title();?></title>
 <link rel="stylesheet" href="<?php
    echo generateDynamicAssetCss(
        array(
            'bootstrap/css/bootstrap',
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
