<?php
/*
    Plugin Name: VIMA - Rental Submissions
    Description: Este plugin se ancarga de la sección "My Reservations Deposited" y la página de edición de la misma.
    Version: 1.0 ENE25
    Author: Oscar G
*/
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
add_action('wp_ajax_get_nombre_partner', 'getNombrePartner');
add_action('wp_ajax_nopriv_get_nombre_partner', 'getNombrePartner');


function getNombrePartner(){
    $user = wp_get_current_user();
    $nombre = $user->first_name." ".$user->last_name;
    echo json_encode(["name" => $nombre, "initials" => getInitialsPartner($user)]);
    wp_die();
}

function getInitialsPartner($user){
    
    $nombre = $user->first_name." ".$user->last_name;
    $nombre = explode(" ", $nombre);
    $iniciales = strtoupper($nombre[0][0].$nombre[1][0].$nombre[1][1]);
    
    return $iniciales;
}


function get_rental_submissions($atts) {
    $atts = shortcode_atts( array(
        'user_id' => get_current_user_id(),
    ), $atts, 'get_rental_submissions' );

    $args = array(
        'post_type' => 'rental-submissions',
        'post_status' => 'publish',
        'author' => $atts['user_id'],
        'posts_per_page' => -1,
    );

    $query = new WP_Query($args);

    foreach($query->posts as $post) {

       pintarItem(get_post_meta($post->ID), $post->ID);
    }

    wp_reset_postdata();
    
    
}

//funcion para eliminar una rental-submission
add_action('wp_ajax_eliminar_rental_submission', 'eliminar_rental_submission');
add_action('wp_ajax_nopriv_eliminar_rental_submission', 'eliminar_rental_submission');

function eliminar_rental_submission(){
    $post_id = $_POST['post_id'];
    wp_trash_post($post_id);
    echo json_encode(["status" => "ok", "rental-submission-deleted" => $post_id]);
    wp_die();
}

//funcion para pintar el item encontrado con get_rental_submissions()

function pintarItem($postMeta, $id){
    //echo "<pre>".print_r($postMeta, true)."</pre>";
    $url = esc_url("https://www.vacationintervalsmanagement.com/member-area/edit-your-submission?rs=".$id);
    $creacionDate = get_the_date('Y-m-d', $id);
    //echo "<pre>".print_r($creacionDate, true)."</pre>";
        $html = '';
        $html .= '<a href="'.$url.'" class="item" id="'.$id.'">';
        $html .= '<div>';
        $html .= '<h6>'.$postMeta['unit-for-rent'][0].'</h6>';
        $html .= '<p>&nbsp;&nbsp;&nbsp;&nbsp;Week: &nbsp;'.date("M/d", $postMeta['date-of-start'][0]).' </p>';
        $html .= '<p>&nbsp; - &nbsp;'.date("M/d/Y", $postMeta['date-of-end'][0]).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </p>';
        $html .= '<p>Created: &nbsp;&nbsp;'.$creacionDate.'</p>';
        $html .= '</div></a>';
        $html .= '<button class="eliminar" data-id="'.$id.'">
        
        <svg height="20px" width="20px" version="1.1" id="_x32_" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" 
	 viewBox="0 0 512 512"  xml:space="preserve">
<style type="text/css">
	.st0{fill:#FFFFFF;}
</style>
<g>
	<path class="st0" d="M88.594,464.731C90.958,491.486,113.368,512,140.234,512h231.523c26.858,0,49.276-20.514,51.641-47.269
		l25.642-335.928H62.952L88.594,464.731z M420.847,154.93l-23.474,307.496c-1.182,13.37-12.195,23.448-25.616,23.448H140.234
		c-13.42,0-24.434-10.078-25.591-23.132L91.145,154.93H420.847z"/>
	<path class="st0" d="M182.954,435.339c5.877-0.349,10.35-5.4,9.992-11.269l-10.137-202.234c-0.358-5.876-5.401-10.349-11.278-9.992
		c-5.877,0.357-10.35,5.409-9.993,11.277l10.137,202.234C172.033,431.231,177.085,435.696,182.954,435.339z"/>
	<path class="st0" d="M256,435.364c5.885,0,10.656-4.763,10.656-10.648V222.474c0-5.885-4.771-10.648-10.656-10.648
		c-5.885,0-10.657,4.763-10.657,10.648v202.242C245.344,430.601,250.115,435.364,256,435.364z"/>
	<path class="st0" d="M329.046,435.339c5.878,0.357,10.921-4.108,11.278-9.984l10.129-202.234c0.348-5.868-4.116-10.92-9.993-11.277
		c-5.877-0.357-10.92,4.116-11.277,9.992L319.054,424.07C318.697,429.938,323.17,434.99,329.046,435.339z"/>
	<path class="st0" d="M439.115,64.517c0,0-34.078-5.664-43.34-8.479c-8.301-2.526-80.795-13.566-80.795-13.566l-2.722-19.297
		C310.388,9.857,299.484,0,286.642,0h-30.651H225.34c-12.825,0-23.728,9.857-25.616,23.175l-2.721,19.297
		c0,0-72.469,11.039-80.778,13.566c-9.261,2.815-43.357,8.479-43.357,8.479C62.544,67.365,55.332,77.172,55.332,88.38v21.926h200.66
		h200.676V88.38C456.668,77.172,449.456,67.365,439.115,64.517z M276.318,38.824h-40.636c-3.606,0-6.532-2.925-6.532-6.532
		s2.926-6.532,6.532-6.532h40.636c3.606,0,6.532,2.925,6.532,6.532S279.924,38.824,276.318,38.824z"/>
</g>
</svg>

        </button>';

    echo $html;
}

//hacer que este plugin solo afecte a la página /member-area/my-reservations-deposited y soolo cuando carga el template elementor_library=reservations-deposited-jan25
add_action('template_redirect', function() {
    $url_actual = home_url( add_query_arg( null, null ) );
    $path = parse_url($url_actual, PHP_URL_PATH);
    $ultima_parte = basename(rtrim($path, '/'));

    if($ultima_parte === "my-reservations-deposited"){
        add_shortcode('get_rental_submissions', 'get_rental_submissions');
        //hacer el enqueue del estilo en plugins/vima-rental-submissions/assets/css/estilo.css
        wp_enqueue_style('vima-rental-submissions', plugin_dir_url(__FILE__).'assets/css/estilo.css');

        //hacer el enqueue del script en plugins/vima-rental-submissions/assets/js/script.js
        wp_enqueue_script('vima-rental-submissions', plugin_dir_url(__FILE__).'assets/js/script.js', array('jquery'), 104, true);
    }
    
});

//add_shortcode('get_rental_submissions', 'get_rental_submissions');