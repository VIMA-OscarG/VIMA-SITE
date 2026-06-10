<?php
/**
 * The template for displaying all single posts and attachments
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */ 
get_header();
wp_head(); 
if(current_user_can('administrator')){
    show_admin_bar( true );
}
// echo "<pre>".print_r($_GET, true). "</pre>";
// echo "<pre>".print_r($_POST, true). "</pre>";
// echo isset($_GET['s']) ? "template de busqueda":"no hay template de resultados";

// if(isset($_GET['s'])){
//     include('resultados_reservas_page.php');
// }

?>
    <style>
    div.gallery {
    margin: 5px;
    border: 1px solid #ccc;
    float: left;
    width: 180px;
    }

    div.gallery:hover {
    border: 1px solid #777;
    }

    div.gallery img {
    width: 100%;
    height: auto;
    }

    div.desc {
    padding: 10px;
    text-align: center;
    font-family: 'Montserrat';
    font-size: 11px;
    color: #000;
    }
    </style>
  
    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">

        <?php if(current_user_can('administrator')) {  

             // Start the loop.
        while ( have_posts() ) : the_post();
  
        /*
         * Include the post format-specific template for the content. If you want to
         * use this in a child theme, then include a file called called content-___.php
         * (where ___ is the post format) and that will be used instead.
         */
        get_template_part( 'content', get_post_format() );

      //echo "<pre>". print_r(implode(', ',maybe_unserialize( get_post_meta(get_the_ID(), 'privileges')[0])), true ). "</pre>";

            

        ?>

            <div>
                <h6>Reserva : <strong> <?php echo get_the_title(); ?></strong></h6> 
                <h6>Nombre del cliente :<strong> <?php echo get_post_meta(get_the_ID(), 'nombre')[0];  ?></strong></h6>
                <h6>Iniciales :<strong> <?php echo get_post_meta(get_the_ID(), 'initials')[0];  ?></strong></h6>
                <h6>Unidad rentada :<strong> <?php echo get_post_meta(get_the_ID(), 'unit-for-rent')[0];  ?></strong></h6>
                <h6>Fecha de llegada :<strong> <?php echo date("m/d/Y",get_post_meta(get_the_ID(), 'date-of-start')[0]);  ?></strong></h6>
                <h6>Fecha de salida :<strong> <?php echo date("m/d/Y",get_post_meta(get_the_ID(), 'date-of-end')[0]);  ?></strong></h6>
                <h6>Privilegios :<strong> <br /> <?php echo implode(', <br />',maybe_unserialize( get_post_meta(get_the_ID(), 'privileges')[0]));  ?></strong></h6>

                
                <br />
               <h5> <strong>Confirmation of Reservation -im&aacute;genes</strong> </h5>
            </div>
            

        <?php



        $reservatioonFIle = get_post_meta( get_post_field('confirmation-of-reservation'), '_wp_attached_file', 1 );
        $arrReservationFiles = get_post_meta( get_the_ID( ),'confirmation-of-reservation', 1);
        $arrReservationFiles = explode(",", $arrReservationFiles);

        $confictaimg = 0;
        foreach($arrReservationFiles as $reservationFile):
            $confictaimg ++;
            $reservatioonFIle = get_post_meta( $reservationFile, '_wp_attached_file', 1 );

         //   echo "<pre>".print_r(wp_get_attachment_image_src($reservationFile, 'full', false ), true)."</pre>";
            $imagenMostrar =  site_url().'/wp-content/uploads/'.$reservatioonFIle;

        
        //echo '<img src="'.$imagenMostrar.'" width="1000" height="auto" />';
        echo   '<div class="gallery">
                    <a target="_blank" href="'.$imagenMostrar.'">
                    <img src="'.$imagenMostrar.'" width="600" height="auto">
                    </a>
                    <div class="desc">Confirmation of Reservation '.$confictaimg.' </div>
                </div>';
        endforeach;


        ?> 
            <div style="clear: both;">
                <br />
                <h5><strong>Promotional Amenity Privileges -im&aacute;genes </strong> </h5>
            </div>
           
        <?php

        $reservatioonFIle = get_post_meta( get_post_field('promotional-amenity-privileges'), '_wp_attached_file', 1 );
        $arrReservationFiles = get_post_meta( get_the_ID( ),'promotional-amenity-privileges', 1);
        $arrReservationFiles = explode(",", $arrReservationFiles);
        $promctaimg;
        foreach($arrReservationFiles as $reservationFile):
            $reservatioonFIle = get_post_meta( $reservationFile, '_wp_attached_file', 1 );
            $promctaimg ++;
         //   echo "<pre>".print_r(wp_get_attachment_image_src($reservationFile, 'full', false ), true)."</pre>";
            $imagenMostrar =  site_url().'/wp-content/uploads/'.$reservatioonFIle;

        
       // echo '<img src="'.$imagenMostrar.'" width="1000" height="auto" />';
        echo   '<div class="gallery">
                    <a target="_blank" href="'.$imagenMostrar.'">
                    <img src="'.$imagenMostrar.'" width="600" height="auto">
                    </a>
                    <div class="desc">Promotional Amenity Privileges '.$promctaimg.' </div>
                </div>';
        endforeach;


      

        //echo "<pre>". print_r(get_post_meta(get_the_ID()));
        
        
        
    endwhile;

         }else{

            echo '<div class="button elementor-item"><a class="button elementor-item" href="'.site_url().'/wp-login.php?action=logout&_wpnonce=458e6ab3ef">Acceder como Admin</a></div>';
            die ("Esta sección esta reservada para el administrador del sistema.");
        
        } ?>

        
  
        </main><!-- .site-main -->
    </div><!-- .content-area -->
    <?php wp_footer(); ?>
  
    