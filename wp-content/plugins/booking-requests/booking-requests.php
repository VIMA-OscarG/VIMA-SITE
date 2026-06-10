<?php 
/**
 * Plugin Name:       VIMA - API External Booking Requests handler
 * Plugin URI:        https://plugin-uri/
 * Description:       Para conectar VIMA con la API externa y consumir Booking Requests
 * Version:           beta 0.01
 * Author:            Dev OG
 * Author URI:        og.lopar711@gmail.com
 * License:           GPL v2 or later
 */

// Crear el post-type booking requests

// Register the Custom Post Type
function register_custom_post_type_booking_requests() {

    if ( post_type_exists( 'booking-requests' ) ) {
        return;
    }


    $labels = array(
        'name'               => _x( 'Booking Requests', 'General name of the post type', 'text-domain' ),
        'singular_name'      => _x( 'Booking Request', 'Singular name of the post type', 'text-domain' ),
        'menu_name'          => _x( 'Booking Requests', 'Name in the menu', 'text-domain' ),
        'name_admin_bar'     => _x( 'Booking Request', 'Name in the admin bar', 'text-domain' ),
        'add_new'            => _x( 'Add New', 'Adding new item', 'text-domain' ),
        'add_new_item'       => __( 'Add New Booking Request', 'text-domain' ),
        'new_item'           => __( 'New Booking Request', 'text-domain' ),
        'edit_item'          => __( 'Edit Booking Request', 'text-domain' ),
        'view_item'          => __( 'View Booking Request', 'text-domain' ),
        'all_items'          => __( 'All Booking Requests', 'text-domain' ),
        'search_items'       => __( 'Search Booking Requests', 'text-domain' ),
        'parent_item_colon'  => __( 'Parent Booking Request:', 'text-domain' ),
        'not_found'          => __( 'No booking requests found.', 'text-domain' ),
        'not_found_in_trash' => __( 'No booking requests found in trash.', 'text-domain' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'booking-requests' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array( 'title', 'editor' ),
        'register_meta_box_cb' => 'add_custom_fields_booking_requests' // Add custom fields
    );

    register_post_type( 'booking-requests', $args );
}
add_action( 'init', 'register_custom_post_type_booking_requests' );

// Add custom fields
function add_custom_fields_booking_requests() {
    add_meta_box(
        'booking_requests_meta_box',
        __( 'Booking Request Details', 'text-domain' ),
        'show_custom_fields_booking_requests',
        'booking-requests',
        'normal',
        'high'
    );
}

// Show custom fields
function show_custom_fields_booking_requests( $post ) {
    // Get metafield 'client-data', 'week-details', and 'webhook'
    $client_data = get_post_meta( $post->ID, 'client-data', true );
    $week_details = get_post_meta( $post->ID, 'week-details', true );
    $webhook = get_post_meta( $post->ID, 'webhook', true );

    // Display metafields for 'client-data'
    echo '<h3>Client Data</h3>';
    echo '<ul>';
    foreach ($client_data as $key => $value) {
        if (is_array($value)) {
            echo '<li><strong>' . $key . ':</strong>';
            echo '<ul>';
            foreach ($value as $subkey => $subvalue) {
                echo '<li><strong>' . $subkey . ':</strong> ' . $subvalue . '</li>';
            }
            echo '</ul>';
            echo '</li>';
        } else {
            echo '<li><strong>' . $key . ':</strong> ' . $value . '</li>';
        }
    }
    echo '</ul>';

    // Display metafields for 'week-details'
    echo '<h3>Week Details</h3>';
    echo '<ul>';
    foreach ($week_details as $key => $value) {
        echo '<li><strong>' . $key . ':</strong> ' . $value . '</li>';
    }
    echo '</ul>';

    // Display metafields for 'webhook'
    echo '<h3>Webhook</h3>';
    echo '<ul>';
    foreach ($webhook as $key => $value) {
        echo '<li><strong>' . $key . ':</strong> ' . $value . '</li>';
    }
    echo '</ul>';
}

// Save metafields
function save_metafields_booking_requests( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

    // Check permissions
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // Save metafields 'client-data', 'week-details', and 'webhook'
    if ( isset( $_POST['client-data'] ) ) {
        update_post_meta( $post_id, 'client-data', $_POST['client-data'] );
    }

    if ( isset( $_POST['week-details'] ) ) {
        update_post_meta( $post_id, 'week-details', $_POST['week-details'] );
    }

    if ( isset( $_POST['webhook'] ) ) {
        update_post_meta( $post_id, 'webhook', $_POST['webhook'] );
    }
}
add_action( 'save_post', 'save_metafields_booking_requests' );



//FIN crear post type booking request


 ?>