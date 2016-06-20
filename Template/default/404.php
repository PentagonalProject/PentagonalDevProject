<?php
get_header();
?>
    <div id="container">
        <h1>Welcome to CodeIgniter RHMVC!</h1>
        <div id="body">
            <p>404 Page &amp; The corresponding controller for this page is found at:</p>
            <code><?php echo get_instance()->load->getActiveTemplate(); echo basename(__FILE__);?></code>
        </div>
        <p class="footer">Page rendered in <strong><?php echo get_elapsed_time();?></strong> <?php _e('seconds');?>. <?php echo  (ENVIRONMENT === 'development') ?  'CodeIgniter Version <strong>' . CI_VERSION . '</strong>' : '' ?></p>
    </div>
<?php
get_footer();
