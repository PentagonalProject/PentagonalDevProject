<?php
get_header();
$message = $this->get_var('message');
if (is_array($message)) {
    $out = '';
    foreach ($message as $key => $value) {
        $out .= "<p>{$key} : {$value}</p>";
    }
    $message = $out;
}
?>
    <div id="container">
        <h1>Welcome to CodeIgniter RHMVC!</h1>
        <div id="body">
            <p>Error Page &amp; The corresponding controller for this page is found at:</p>
            <code><?php echo $this->getActiveTemplate(); echo basename(__FILE__);?></code>
            <code><?php echo $message;?></code>
        </div>
        <p class="footer">Page rendered in <strong><?php echo get_elapsed_time();?></strong> <?php _e('seconds');?>. <?php echo  (ENVIRONMENT === 'development') ?  'CodeIgniter Version <strong>' . CI_VERSION . '</strong>' : '' ?></p>
    </div>
<?php
get_footer();
