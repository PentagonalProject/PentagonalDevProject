<?php
/**
 * Example Implementation
 */
/** @noinspection PhpUndefinedMethodInspection */
Hook::add('the_title', function () {
    return 'Welcome to Code igniter';
});
/** @noinspection PhpUndefinedMethodInspection */
Hook::add('body_class', function ($class) {
    $class[] = 'home-page';
    return $class;
});
/** @noinspection PhpUndefinedMethodInspection */
Hook::add('language_attributes', function ($attr) {
    return $attr. ' class="no-js"';
});
get_header();
?>
<div id="container">
    <h1>Welcome to CodeIgniter RHMVC!</h1>
    <div id="body">
        <p>The corresponding controller for this page is found at:</p>
        <code><?php echo $this->load->getActiveTemplate(); echo basename(__FILE__);?></code>
    </div>
    <p class="footer">Page rendered in <strong>{elapsed_time}</strong> <?php _e('seconds');?>. <?php echo  (ENVIRONMENT === 'development') ?  'CodeIgniter Version <strong>' . CI_VERSION . '</strong>' : '' ?></p>
</div>
<?php
get_footer();
