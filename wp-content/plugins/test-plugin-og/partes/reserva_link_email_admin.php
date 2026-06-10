<?php
 

function my_plugin_templates( $template ) {
    $post_types = array( 'rental-submissions' );
   // var_dump(plugin_dir_path(__FILE__)) ;
   /// var_dump(is_singular( $post_types ));

    if ( is_singular( $post_types ) && file_exists( plugin_dir_path(__FILE__) . '../templates/post-rental-submission.php' ) ){
        $template = plugin_dir_path(__FILE__) . '../templates/post-rental-submission.php';
    }


    return $template;
}
add_filter( 'template_include', 'my_plugin_templates' );




function wpd_do_stuff_on_404(){
    if( is_404() ){

        $rentalNumeroPermalink = array();
        $url = $_SERVER['REQUEST_URI'];
        preg_match('/-([0-9]{4})/', $url, $rentalNumeroPermalink);

        $numeropostrentalsubmission = $rentalNumeroPermalink[1];

        $post = get_post($numeropostrentalsubmission);
        $fileLocation = site_url()."/agreements/".$post->post_name;
        //var_dump();
        header("Location: ".$fileLocation);
        
    }
}
add_action( 'template_redirect', 'wpd_do_stuff_on_404' );

?>
