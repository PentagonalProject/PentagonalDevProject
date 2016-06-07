<?php
$this->vars(
    'title',
    'Error Encountered'
);
get_header();
?>
    <div id="container">
        <h1>Welcome to CodeIgniter RHMVC!</h1>
        <div id="body">
            <p>Error Page &amp; The corresponding controller for this page is found at:</p>
            <code><?php echo $this->getActiveTemplate(); echo basename(__FILE__);?></code>
            <code><?php echo $this->get_var('message');?></code>
        </div>
        <p class="footer">Page rendered in <strong>{elapsed_time}</strong> <?php _e('seconds');?>. <?php echo  (ENVIRONMENT === 'development') ?  'CodeIgniter Version <strong>' . CI_VERSION . '</strong>' : '' ?></p>
    </div>
<?php
get_footer();
