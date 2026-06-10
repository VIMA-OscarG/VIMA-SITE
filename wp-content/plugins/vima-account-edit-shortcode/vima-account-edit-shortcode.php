<?php
/**
 * Plugin Name: VIMA - Edit Account (solo formulario)
 * Description: Modificaciones a la sección del formulario de account details que está incrustado en el template de adit account details.
 * Version: 1.0.0
 */


// Mostrar campos extra en My Account > Edit Account
add_action( 'woocommerce_edit_account_form', function() {
    $user_id = get_current_user_id();
    $phone = get_user_meta( $user_id, 'billing_phone', true );
    $billing_address = get_user_meta( $user_id, 'billing_address_1', true );
    ?>
    <p class="form-row form-row-wide">
        <label for="billing_phone"><?php _e( 'Phone', 'woocommerce' ); ?></label>
        <input type="text" name="billing_phone" id="billing_phone"
            value="<?php echo esc_attr( $phone ); ?>" />
    </p>

    <p class="form-row form-row-wide">
        <label for="billing_address_1"><?php _e( 'Billing Address', 'woocommerce' ); ?></label>
        <input type="text" name="billing_address_1" id="billing_address_1"
            value="<?php echo esc_attr( $billing_address ); ?>" />
    </p>
    <?php
});

add_action( 'woocommerce_save_account_details', function( $user_id ) {
    if ( isset( $_POST['billing_phone'] ) ) {
        update_user_meta( $user_id, 'billing_phone', sanitize_text_field( $_POST['billing_phone'] ) );
    }
    if ( isset( $_POST['billing_address_1'] ) ) {
        update_user_meta( $user_id, 'billing_address_1', sanitize_text_field( $_POST['billing_address_1'] ) );
    }
});

function vima_get_account_details_url() {
    // Intenta resolver por path (mejor si hay cambios de dominio/ambiente)
    $page = get_page_by_path( 'member-area/account-details' );
    if ( $page ) {
        return get_permalink( $page->ID );
    }
    // Fallback por si el slug cambia: ajusta este path si fuera necesario.
    return home_url( '/member-area/account-details/' );
}

/**
 * 1) Inyecta nuestros hidden + nonce EN EL MISMO FORM del widget Account Details,
 *    y fija un return_to al URL correcto (Elementor).
 */
add_action( 'woocommerce_edit_account_form_end', function () {
    if ( ! is_user_logged_in() ) return;

    echo '<input type="hidden" name="vima_acc_save" value="1">';
    wp_nonce_field( 'vima_acc_save_nonce', 'vima_acc_save_nonce' );

    // return_to fijo a la página de Elementor
    $return_to = vima_get_account_details_url();
    echo '<input type="hidden" name="vima_return_to" value="' . esc_url( $return_to ) . '">';
}, 99 );

/**
 * 2) Captura el POST del widget y guarda metas billing_*,
 *    redirigiendo SIEMPRE a la página de Elementor.
 */
add_action( 'init', function () {
    if ( 'POST' !== ($_SERVER['REQUEST_METHOD'] ?? '') ) return;
    if ( empty($_POST['vima_acc_save']) ) return;
    if ( ! is_user_logged_in() ) return;

    if ( empty($_POST['vima_acc_save_nonce']) || ! wp_verify_nonce( $_POST['vima_acc_save_nonce'], 'vima_acc_save_nonce' ) ) {
        return;
    }

    $user_id = get_current_user_id();

    // Guarda PHONE
    if ( isset($_POST['billing_phone']) ) {
        $phone = sanitize_text_field( wp_unslash($_POST['billing_phone']) );
        update_user_meta( $user_id, 'billing_phone', $phone );
    }

    // Guarda BILLING ADDRESS
    if ( isset($_POST['billing_address_1']) ) {
        $addr1 = sanitize_text_field( wp_unslash($_POST['billing_address_1']) );
        update_user_meta( $user_id, 'billing_address_1', $addr1 );
    }

    // Sincroniza nombre/apellido/email si vienen del widget
    if ( isset($_POST['account_first_name']) ) {
        $first = sanitize_text_field( wp_unslash($_POST['account_first_name']) );
        update_user_meta( $user_id, 'first_name', $first );
        update_user_meta( $user_id, 'billing_first_name', $first );
    }
    if ( isset($_POST['account_last_name']) ) {
        $last = sanitize_text_field( wp_unslash($_POST['account_last_name']) );
        update_user_meta( $user_id, 'last_name', $last );
        update_user_meta( $user_id, 'billing_last_name', $last );
    }
    if ( isset($_POST['account_email']) && is_email($_POST['account_email']) ) {
        $email = sanitize_email( wp_unslash($_POST['account_email']) );
        wp_update_user( [ 'ID' => $user_id, 'user_email' => $email ] );
    }

    if ( function_exists('wc_add_notice') ) {
        wc_add_notice( __( 'Datos actualizados correctamente.', 'woocommerce' ), 'success' );
    }

    // === Redirección PRG SIEMPRE a la página de Elementor ===

	$target = add_query_arg( 'updated', 'true', vima_get_account_details_url() );
	wp_safe_redirect( $target );
    exit;
}, 0 );

/**
 * (Opcional pero recomendado)
 * Imprime notices de WooCommerce en tu página de Elementor,
 * por si tu plantilla no los muestra automáticamente.
 */
add_action( 'wp', function () {
    if ( is_page() ) {
        $page = get_queried_object();
        if ( $page && is_a( $page, 'WP_Post' ) && $page->post_name === 'account-details' ) {
            add_action( 'wp_footer', function () {
                if ( function_exists('wc_print_notices') ) {
                    echo '<div class="woocommerce-notices-wrapper" style="margin:1rem 0">';
                    wc_print_notices();
                    echo '</div>';
                }
            }, 1 );
        }
    }
});

add_action( 'woocommerce_edit_account_form_end', function () {
    if ( isset( $_GET['updated'] ) && $_GET['updated'] === 'true' ) {
        echo '<div class="woocommerce-message" role="alert" style="margin:1rem 0; color:gray;">Your account details have been updated successfully.</div>';
    }
});

// Eliminar campos del formulario de editar cuenta
add_filter( 'woocommerce_edit_account_form_fields', function( $fields ) {

    // Eliminar Display Name
    if ( isset( $fields['account_display_name'] ) ) {
        unset( $fields['account_display_name'] );
    }

    // Eliminar Newsletter si existe con ese key
    if ( isset( $fields['mailchimp_woocommerce_is_subscribed_radio'] ) ) {
        unset( $fields['mailchimp_woocommerce_is_subscribed_radio'] );
    }

    return $fields;
});
