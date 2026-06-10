<?php
/*
 * Plugin Name:       VIMA - Delete Account
 * Plugin URI:        mailto: og.lopar711@gmail.com
 * Description:       Este plugin genera el botón "Delete Account".
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Oscar García
 * Author URI:        og.lopar711@gmail.com
 * License:           GPL v2 or later
 */

 function delete_account_handler() {
  // Verificar el nonce
  if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'delete_account_nonce')) {
      error_log('Nonce verification failed.');
      wp_send_json_error('Nonce verification failed.');
  }

  $user_id = intval($_POST['user_id']);
  $user = get_userdata($user_id);

  if (!$user) {
      error_log('User not found.');
      wp_send_json_error('Error: Invalid user.');
  }

  if (current_user_can('manage_options')) {
      error_log('Attempt to delete an administrator account.');
      wp_send_json_error('Error: Unable to delete administrator account.');
  }

  // Eliminar el usuario
  $result = wp_delete_user($user_id);

  if ($result) {
      error_log('User deleted successfully.');
      wp_send_json_success('Account deleted successfully.');
  } else {
      error_log('Failed to delete user.');
      wp_send_json_error('Failed to delete account.');
  }
}

add_action('wp_ajax_delete_account', 'delete_account_handler');
add_action('wp_ajax_nopriv_delete_account', 'delete_account_handler');

function delete_account_button_shortcode() {

  $current_user = wp_get_current_user();
  $user_id = $current_user->ID;

  if ( !is_user_logged_in() ) {
    return '<p>Please log in to delete your account.</p>';
  }

  if ( current_user_can('manage_options') ) {
    return '<p><strong>Admin, no se puede eliminar.</strong></p>';
  }

  ob_start();

  //echo "<pre>".print_r(admin_url('admin-ajax.php'), true)."</pre>";
?>
  <form id="delete-account-form" method="post" action="<?php echo admin_url('admin-ajax.php'); ?>">
      <input type="hidden" name="action" value="delete_account">
      <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
      <?php wp_nonce_field('delete_account_nonce', '_wpnonce'); ?>
      <button type="submit" style='font-family: "Cormorant Garamond", Sans-serif; font-size: 17px; font-weight: 700; color: #FFFFFF !important; background-color: #414242; padding: 0px 10px 0px 10px; margin-top: 20px; margin-left: 16px;'>Delete Account</button>
  </form>
  <!-- Dialog de Confirmacion -->
<dialog id="confirmation-dialog">
    <form method="dialog">
        <p>Are you sure you want to delete your account? This action cannot be undone.</p>
        <button type="button" id="confirm-delete">Confirm</button>
        <button type="button" id="cancel-delete">Cancel</button>
    </form>
</dialog>

<!-- Dialog de Mensaje -->
<dialog id="message-dialog">
    <form method="dialog">
        <p id="message-text">Message will go here.</p>
        <button type="button" id="close-message">Close</button>
    </form>
</dialog>


<?php
  $form = ob_get_contents();
  ob_end_clean();

  return $form;
}

add_shortcode('delete_account_button', 'delete_account_button_shortcode');

function enqueue_delete_account_script() {
  wp_enqueue_script('delete-account-script', plugins_url('delete-account-script.js', __FILE__), array('jquery'));
  wp_enqueue_style( 'delete-account-style',  plugins_url('delete-account-script.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'enqueue_delete_account_script');