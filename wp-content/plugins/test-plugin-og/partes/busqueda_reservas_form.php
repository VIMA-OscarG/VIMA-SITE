<?php

    function template_chooser($template)   
    {    
    global $wp_query; 
    $plugindir = dirname(__FILE__);  
    $post_type = get_query_var('post_type');   
    if( $wp_query->is_search && $post_type == 'rental-submissions' )   
    {
        return  $plugindir.'/templates/resultado_reservas_page.php';  //  redirect to resultado_reservas_page.php
    }   
    return $template;   
    }
   // add_filter('template_include', 'template_chooser');
    
     
   

        add_action('admin_menu', 'newAdminPage');
    function newAdminPage() {
        remove_all_actions('admin_notices');
        add_menu_page( 'Buscador rental submissions', 'Buscador rental submissions', 'manage_options', 'search-submissions-page', 'mostrarBusquedaPage');
    }

    function mostrarBusquedaPage () {

        $url = "https://www.vacationintervalsmanagement.com/wp-admin/tools.php?page=advanced-admin-search";
       
        wp_safe_redirect( $url );
        exit;
    
       

    }

    function estiloBusqueda() {
        $plugindir = dirname(__FILE__); 
        wp_register_style( 'css_busqueda', 'https://vacationintervalsmanagement.com/wp-content/plugins/test-plugin-og/templates/estiloBusqueda.css', false, '8.0.6');
        wp_enqueue_style( 'css_busqueda' );
        //wp_enqueue_style('css_busqueda', plugins_url('/templates/estiloBusqueda.css', __FILE__) );
    }
    add_action( 'admin_enqueue_scripts', 'estiloBusqueda' );
    

?>