<?php
$form = $this->load->get_var('form');
if (empty($form)) {
    show_error(
        array(
            __('There was an error.'),
            __('When try to load the login form.')
        )
    );
}
?>
<div class="container-fluid">
    <div id="login-form-box">
      <div class="row">
          <div class="col-md-4 col-md-offset-4">
<?php
$login_form = array();
echo form_open_multipart('', array('method' => 'post', 'id' => 'form-login'));
$login_form[] = form_input(
    $form['username']['name'],
    $form['username']['value'],
    array(
        'class' => 'form-control',
        'placeholder' => __('username ...')
    )
);
$login_form[] = form_password(
    $form['password']['name'],
    $form['password']['value'],
    array(
        'class' => 'form-control',
        'placeholder' => __('password ...')
    )
);
$login_form[] = form_input(
    array(
        'name' => $form['remember']['name'],
        'type' => 'checkbox',
        'class'=> 'checkbox',
    ),
    'yes',
     set_checkbox(
        $form['remember']['name'],
        'yes'
    )
);
$login_form[] = form_button(
    array(
        'content' => __('Login'),
        'type' => 'submit'
    ),
    '',
    array(
        'class' => 'btn btn-primary btn-block',
    )
);
$login_form = array_map('trim', $login_form);
echo "<p>".implode("</p>\n</p>", $login_form)."</p>\n";
echo form_close();
?>

          </div>
        </div>
    </div>
    </div>
