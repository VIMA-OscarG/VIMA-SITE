<?php
/*
Plugin Name: VIMA Invitation Validator
Description: Valida invitaciones para el registro de usuarios en WordPress.
Version: 1.0
Author: Oscar Garcia webdevelopment@vacationintervalsmanagement.com
*/


// añadir logica del shortcode invitation_validator para la sección sign in

add_action('init', 'register_invitation_validator_shortcode');
function register_invitation_validator_shortcode() {
    add_shortcode('invitation_validator', 'invitation_validator_shortcode_handler');
}
function invitation_validator_shortcode_handler($atts) {

$arrCodes = [
    "VIMAVIP2026",
    "ZOETRYVIP2026"
];
    ob_start();
    // echo "<pre>".print_r($_POST, true)."</pre>";
    // var_dump(in_array($_POST['invitation_code'], $arrCodes));
    ?>
    <div class="invitation-validator" style="display: <?php echo isset($_POST['validate_invitation']) && (in_array($_POST['invitation_code'], $arrCodes)) ? 'none' : 'block'; ?>; text-align: center; margin-top: 20px;">
        <h2>Type in your code</h2>
        <form method="post" action="">
        <input type="text" id="invitation_code" name="invitation_code" required>
        <br>
        <button type="submit" name="validate_invitation" value="1">Validate</button>
    </form>
    </div>
    
    <?php
 //como solo existe un código válido VIMAVIP2026 y este shortcode está justo arriba del formulario de registro, si el código es válido mostramos el formulario de registro
    if (isset($_POST['validate_invitation'])) {
        $invitation_code = sanitize_text_field($_POST['invitation_code']);
        if ($invitation_code === 'VIMAVIP2026' || $invitation_code === 'ZOETRYVIP2026') {
            echo '<p style="color:green; margin-top:20px; display:block; text-align:center;">Invitation Valid. Please complete the registration form below.</p>';
        } else {
            echo '<p style="color:red; margin-top:20px; display:block; text-align:center;">Invalid Invitation Code. Please try again.</p>';
        }
    }
    return ob_get_clean();
}

