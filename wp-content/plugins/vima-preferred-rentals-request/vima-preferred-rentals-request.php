<?php
/*
    Plugin Name: VIMA - Preferred Rentals Request
    Description: Para generar la funcionalidad de PRR
    Version: 1.0 SEP24
    Author: Oscar G
*/
add_filter('wp_mail_from','ajustarAClientCareEmailPRR');
    function ajustarAClientCareEmailPRR($content_type) {
    return 'clientcare@vacationintervalsmanagement.com';
    }
function agregarColunmaTagAdminView($columns) {
    $columns['custom_tags_column'] = 'Tags';
    return $columns;
}
add_filter('manage_preferred-rentals_posts_columns', 'agregarColunmaTagAdminView');

function cargarEstilosPRR(){

    $url_actual = home_url( add_query_arg( null, null ) );
    $path = parse_url($url_actual, PHP_URL_PATH);
    $ultima_parte = basename(rtrim($path, '/'));

    if($ultima_parte === "preferred-rental-requests"){

        $version = filemtime( plugin_dir_path( __FILE__ ) . 'assets/css/estilo_prr.css' );
        
        wp_enqueue_style( 'estilo_prr', plugin_dir_url( __FILE__ ) . 'assets/css/estilo_prr.css', array(), $version );
        wp_enqueue_script('prr', plugin_dir_url( __FILE__ ) . 'assets/js/prr.js', array(), $version, true );
    }
}

add_action( 'wp_enqueue_scripts', 'cargarEstilosPRR' );

require_once 'partes/formulario.php';

function custom_tags_column_content($column, $post_id) {

    if ($column == 'custom_tags_column') {
        $tags = get_the_terms($post_id, 'prr-selected-units');

        if ($tags && !is_wp_error($tags)) {
            $tags_list = array();
            foreach ($tags as $tag) {
                $tags_list[] = $tag->name;
            }
            echo implode(', ', $tags_list);
        } else {
            echo 'No tags';
        }
    }
}
add_action('manage_preferred-rentals_posts_custom_column', 'custom_tags_column_content', 10, 2);

function make_tags_column_sortable($columns) {
    $columns['custom_tags_column'] = 'custom_tags';
    return $columns;
}
add_filter('manage_edit-preferred-rentals_sortable_columns', 'make_tags_column_sortable');


function cargarPRRActuales($currentUser){

	$query = array(
		"author" => $currentUser->ID,
		"post_type" => "preferred-rentals",
		"posts_per_page" => 1,
		"order" => 'DESC',
        "date_query"    => array(
            "column"  => 'post_date',
            "after"   => '2024-09-15'
        )
	);
	$postPRRThisPartner = new WP_Query($query);
    //echo "<pre>".print_r($postPRRThisPartner, true). "</pre>";
    if(!$postPRRThisPartner->have_posts()){
        return [];
    }
	$prrData = get_post_meta($postPRRThisPartner->posts[0]->ID);

	return array(
		"email" => $prrData['email'][0],
		"name" => $prrData['name'][0],
		"initials" => $prrData['initials'][0],
		"unit" => maybe_unserialize($prrData['unit'][0]),
		"number-of-weeks-available" => $prrData['number-of-weeks-available'][0],
        "alreadypost" => $postPRRThisPartner->posts[0]->ID,
	);
}

function armarArrayPRR($fieldUnit){

	$arrArmado = [];

	foreach ($fieldUnit as $unit){
		$arrArmado[$unit] = 'true';
	}

	return $arrArmado;

}
function updatePostPRR($currentUser, $arrPRR){

    $post_id = $arrPRR['ID'];
    $prrPostData = array(
        'ID' => $post_id, 
        'post_title' => 'PRR_R' . $currentUser->display_name . '_' . $currentUser->user_email,
    );
    
    try{
        wp_update_post($prrPostData);
        wp_set_object_terms($post_id, $arrPRR['terms'], 'prr-selected-units', false);
        update_post_meta($post_id, 'unit', $arrPRR['unit']);
        update_post_meta($post_id, 'number-of-weeks-available', $arrPRR['number-of-weeks-available']);
        update_post_meta($post_id, 'initials', $arrPRR['initials']);
        
        echo "<script type='text/javascript'>window.location.assign(document.URL);</script>";
    
    }catch(Error $e){
        echo "<pre>".print_r($e, true). "</pre>";
    }
}

function crearPostPRR($currentUser, $arrPRR){

    $prrPostData = array(
                'post_title' => 'PRR_' . $currentUser->display_name . '_' . $currentUser->user_email,
                'post_status' => 'publish',
                'post_author' => $currentUser->ID, 
                'post_type' => 'preferred-rentals',
            );

            try {

                $postCreado = wp_insert_post($prrPostData);
                wp_set_object_terms($postCreado, $arrPRR['terms'], 'prr-selected-units', false);
                update_post_meta($postCreado, 'unit', $arrPRR['unit']);
                update_post_meta($postCreado, 'initials', $arrPRR['initials']);
                update_post_meta($postCreado, 'number-of-weeks-available', $arrPRR['number-of-weeks-available']);
                echo "<script>window.location.assign(document.URL)</script>";

            }catch(Error $e){
                echo "<pre>".print_r($e, true). "</pre>";
            }
            
}


function updateTagsMailchimp($userEmail, $terms){
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $url    = 'https://www.vacationintervalsmanagement.com/vima-mailchimp/tags/mailchimp-tags.php';

    $data = [
        'update_tags_mailchimp' => '1',
        'user_email'            => $userEmail,
        'tags_list'             => implode(',', (array)$terms),
    ];

    $curl = curl_init($url);
    $headers = [
    'Content-Type: application/x-www-form-urlencoded',
    'Referer: https://www.vacationintervalsmanagement.com/',
    'Origin: https://www.vacationintervalsmanagement.com',
    ];

    curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_USERAGENT  => 'VIMA/1.0 (+https://www.vacationintervalsmanagement.com)',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($data),
        CURLOPT_RETURNTRANSFER => true
    ]);


    $response = curl_exec($curl);
    $errno = curl_errno($curl);
    $error = curl_error($curl);
    $info  = curl_getinfo($curl);

    curl_close($curl);

    return $response;
}

add_shortcode( 'generar_shortcode_prr', function() {
    if ( is_user_logged_in() ) {
        $userid = get_current_user_id();
        do_shortcode( '[nueva_seccion_prr userid = '.$userid.']');
        
    } 
});

add_shortcode( 'nueva_seccion_prr', function($atts) {
    
	$currentUser = wp_get_current_user();
    //var_dump(get_the_ID(  ));

    if(function_exists('cargarPRRActuales')){
        $prrActuales = cargarPRRActuales($currentUser);
    }

    if(!empty($prrActuales)){
        if(function_exists('mostrarCheckBoxes')){
            mostrarCheckboxes($currentUser, $prrActuales);
        }
    }

    if(empty($prrActuales)){
        echo "Please enter your initials and select the properties you want to include in your Preferred Rentals Requests settings. You must select at least one unit from the list below to proceed.";
        if(function_exists('mostrarCheckBoxes')){
            mostrarCheckboxes($currentUser, []);
        }
    }

    if(isset($_POST['field_unit'])){
            if(isset($_POST['todo'])){
                $currentUser = new stdClass();
                $currentUser->ID = $_POST['user'];
                $currentUser->user_email = $_POST['useremail'];
                $currentUser->display_name = $_POST['username'];
                $currentUser->initials = $_POST['field_initials'];

                $prrArray = array(
                    "email" => $currentUser->user_email,
                    "name" => $currentUser->display_name,
                    "initials" => $currentUser->initials,
                    "unit" => armarArrayPRR($_POST['field_unit']),
                    "number-of-weeks-available" => $_POST['number_of_weeks_available'],
                    "terms" => $_POST['field_unit'],
                );
                if($_POST['todo'] == 'create'){
                    crearPostPRR($currentUser, $prrArray);
                }

                if($_POST['todo'] == 'update'){
                    $prrArray['ID'] = $_POST['alreadypost'];
                    echo '<script type="text/javascript">mostrarLoaderGuardado();</script>';
                    //echo "<pre>".print_r($currentUser, true). "</pre>";
                        updateTagsMailchimp($currentUser->user_email, $prrArray['terms']);
                  // echo "<pre>".print_r(["user_email" => $currentUser->user_email, "array_prr_units" =>  $prrArray['terms'], "update_result" => updateTagsMailchimp($currentUser->user_email, $prrArray['terms'])], true). "</pre>";
                    updatePostPRR($currentUser, $prrArray);
                    
                   
                }
            }

            $to = $_POST['useremail'];
            $subject = 'VIMA Preferred Rental Requests';
            $unitsText = '';
            foreach ($_POST['field_unit'] as $unit) {
                $unitsText .= $unit . ', ';
            }
            $unitsText = rtrim($unitsText, ', ');
            $message = '<div style="background-color: #000000; color: #ffffff; font-family: \'Merriweather\', sans-serif; padding: 20px; max-width: 600px; margin: 0 auto; line-height: 1.6; font-size: 16px;">
  
  <!-- Header con logo y borde -->
  <div style="text-align: center; margin-bottom: 30px; border-bottom: 8px solid #DEBB27; padding-bottom: 20px;">
    <img src="https://www.vacationintervalsmanagement.com/wp-content/uploads/2023/09/full_trimmed_transparent_customcolor_auto_x2_toned_light_ai-1-1024x405.jpg" alt="VIMA Logo" style="max-width: 100%; height: auto;">
  </div>

  <!-- Contenido del mensaje -->
  <div>
    <p>Hi '.$currentUser->display_name.',</p>

    <p>We’re pleased to let you know that your <strong>Preferred Rentals Request</strong> has been successfully submitted to our system.</p>

    <p><strong>Here are the details of your submission:</strong></p>

    <ul style="list-style-type: disc; padding-left: 20px;">
      <li><strong>Preferred unit types: </strong> '.$unitsText.'</li>
      <li><strong>Number of weeks available: </strong>'.$_POST['number_of_weeks_available'].'</li>
    </ul>

    <p>You can review or update your preferences at any time by logging into your account and selecting the <strong>"My Preferred Rentals Requests"</strong> tab.</p>

    <p><a href="https://vacationintervalsmanagement.com/login/" style="color: #DEBB27; text-decoration: underline;">Log in to your account</a></p>

    <p>If you have any questions or need additional assistance, don’t hesitate to reach out. We’re here to help!</p>

    <p><strong>Warm regards,</strong><br>
    The VIMA Vacation Intervals Management Team</p>
  </div>

  <!-- Footer -->
  <div style="margin-top: 40px; border-top: 1px solid #444444; padding-top: 20px; text-align: center; font-size: 14px; color: #cccccc;">
    <p style="margin: 0;">VIMA Vacation Intervals Management</p>
    <p style="margin: 0;"><a href="https://vacationintervalsmanagement.com" style="color: #cccccc; text-decoration: underline;">www.vacationintervalsmanagement.com</a></p>
  </div>

</div>
';
            
            $headers = array('Content-Type: text/html; charset=UTF-8');

            wp_mail($to, $subject, $message, $headers);
    }
});



add_action("wp_ajax_save_unit_meta", "save_unit_meta");

function save_unit_meta() {
    if (!is_user_logged_in()) {
        wp_send_json_error("No auth");
    }

    $user_id = get_current_user_id();

    $unit       = sanitize_text_field($_POST["unit"]);
    $fee        = floatval($_POST["fee"]);
    $rental     = floatval($_POST["rental"]);
    $multiplier = floatval($_POST["multiplier"]);

    $data = get_user_meta($user_id, "user_units_data", true);
    if (!is_array($data)) {
        $data = [];
    }

    $data[$unit] = [
        "fee"        => $fee,
        "multiplier" => $multiplier,
        "rental"     => $rental
    ];
    
    //$data = [];

    update_user_meta($user_id, "user_units_data", $data);

    wp_send_json_success($data);
}






?>
