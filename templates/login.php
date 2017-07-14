<div class="crumble-error hidden">
  <?php esc_html_e('Invalid e-mail address or password.', 'crumble' ); ?>
</div>

<form method="post" class="crumble-contact-login">
  <?php wp_nonce_field( 'contact_login' ); ?>
  <input type="hidden" name="action" value="crumble_login">
  <p>
    <input type="text" name="crumble_email" autocomplete="off" placeholder="<?php _e('E-mail address', 'crumble'); ?>">
  </p>
  <p>
    <input type="password" name="crumble_password" autocomplete="off" placeholder="<?php _e('Password', 'crumble'); ?>">
  </p>
  <input type="submit" value="<?php _e('Login', 'crumble'); ?>">
</form>
<br>