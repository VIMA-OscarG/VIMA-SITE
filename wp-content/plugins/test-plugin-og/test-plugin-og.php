<?php

/**
 * Plugin Name:       VIMA Plugin - Tarea 1 y Actividaes Extras 1, 2 ,3
 * Plugin URI:        https://plugin-uri/
 * Description:       Este plugin contiene la solución de automatización para la tabla  Availability  Calendar y también contiene la programación necesaria para enviar un link en los correos electrónicos para visualizar el archivo de rental submission y hacer notificaciónes de expiración de 97 y 37 días. El detalle de las caracteristicas que se desarrollan usando el plugin se describe en el "cronograma" (Tarea 1, activides extra de la 1 a la 3).
 * Version:           prod.1.0
 * Author:            Dev OG
 * Author URI:        og.lopar711@gmail.com
 * License:           GPL v2 or later
 */

 ///para cargar el script de idiomas js
function enqueue_member_area_script() {
    $current_url = home_url( add_query_arg( null, null ) );
    if ( strpos( $current_url, 'member-area' ) !== false ) {
        wp_enqueue_script( 'custom-member-area-script', plugin_dir_url( __FILE__ ) . 'scriptTraducciones.js', array(), '1.0.0', true );
    }
}
add_action( 'wp_enqueue_scripts', 'enqueue_member_area_script' );
////termina para cargar el script de idiomas js

require_once('VimaIdiomasClass.php');

require_once('partes/tabla_reservas_unidad.php');



////////para crear vista del archivo y colocar link en el correo que recibe el admin el enviar una "rental-submission"


require_once('partes/reserva_link_email_admin.php');




///crear schedules para envio de email
require_once('partes/rental_expiration_notificaion_email.php');


///para crear buscador de rental-submissions
require_once('partes/busqueda_reservas_form.php');

///para crear el nuevo calendario de reservas
require_once('partes/nuevo-calendario-reservas.php');
?>