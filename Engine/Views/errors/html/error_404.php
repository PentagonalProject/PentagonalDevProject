<?php
$heading = (isset($heading) ? $heading : '404 Page Not Found');
$message = (isset($message) ? $message : 'The page you requested was not found.');
$title = '404 Page Not Found';
$lang = 'en';
if (function_exists('get_instance')) {
    $CI = get_instance();
    if (isset($CI->load)) {
        $title = $CI->load->get_var('title') !== null ? $CI->load->get_var('title') : $title;
    }
    if (isset($CI->lang)) {
        $heading = $CI->lang->line($heading);
        if (is_array($message)) {
            foreach ($message as $k => $v) {
                $message[$k] = $CI->lang->line($message);
            }
        } else {
            $message = $CI->lang->line($message);
        }
        $title = $CI->lang->line($title);
        $lang = $CI->lang->getCurrentLanguage();
    }
}
?><!DOCTYPE html>
<html lang="<?php echo $lang;?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $title;?></title>
  <style type="text/css">
      *,*:after,
      *:before {
          -webkit-box-sizing: border-box;
          -moz-box-sizing: border-box;
          box-sizing: border-box;
      }
      body {
          background-color: #f1f1f1;
          font-size: 14px;
          font-family: "Helvetica Neue", "Helvetica", Arial, sans-serif;
          color: #555;
          line-height: 1;
          padding: 0;
          margin: 0;
      }
      p {
          margin: 0 0 .7em;
          letter-spacing: 1px;
      }
      .wrap {
          text-align: center;
      }
      h1 {
          font-size: 13em;
          margin: .6em 0;
          text-shadow: 2px 0 0 #fff;
      }
      h2 {
          font-size: 1.2em;
      }
  </style>
</head>
<body>
  <div class="wrap">
    <h1>404</h1>
    <h2><?php echo $heading;?></h2>
    <p><?php echo is_array($message) ? implode('</p><p>', $message) : $message;?></p>
  </div>
</body>
</html>
