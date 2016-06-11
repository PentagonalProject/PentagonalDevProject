<!DOCTYPE html>
<html lang="<?php echo getSiteInfo('language');?>" class="no-js">
<head>
  <meta charset="<?php echo getSiteInfo('charset');?>">
  <title><?php admin_title();?></title>
  <link rel="stylesheet" href="<?php
    echo generateDynamicAssetCss(
        array(
            'bootstrap/css/bootstrap',
            'bootflat/css/bootflat',
        )
    );
    ?>">
  <script src="<?php
    echo generateDynamicAssetJs(
        array(
            'js/jquery.min',
            'js/jquery-migrate.min',
            'bootstrap/js/bootstrap.min',
            'bootflat/js/icheck.min',
            'bootflat/js/jquery.fs.selecter.min',
            'bootflat/js/jquery.fs.stepper.min',
        )
    );
    ?>"></script>
</head>
<body>
