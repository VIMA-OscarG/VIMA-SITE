<?php

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



