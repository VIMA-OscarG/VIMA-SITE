<?php 

   

function crearRecurrenciaMailNotifReservas( $schedules ) 
{

    // if(!isset($schedules['60dias']))
    // {
    //     $schedules['60dias'] = array(
    //     'display' => __( 'When 97 days have passed', 'twentyfifteen' ),
    //     'interval' => 60 * DAY_IN_SECONDS,
    //     );
    // }

    // if(!isset($schedules['10secs']))
    // {
    //     $schedules['30secs'] = array(
    //     'display' => __( 'Cada 30 secs -**for testing ONLY', 'twentyfifteen' ),
    //     'interval' => 30,
    //     );
    // }

     
    return $schedules;
}
//add_filter( 'cron_schedules', 'crearRecurrenciaMailNotifReservas' );
   

//add_action('wp', 'docron');
/*
function docron(){


    if (date('Hi') > 2000 && date('Hi') < 2400) {
        if ( ! wp_next_scheduled( 'dos711' ) ) {
          // wp_schedule_sinlge_event( time(), '60dias', 'dos711' );
          if(wp_schedule_single_event( time()+ 5, 'dos711' )){
              //var_dump(true);
          }
                
        }
       // notifacarRentalExpirationDate1();   
    }

}*/


//para el email de new members
// function sendEmailRole($user_id, $new_role, $old_roles){
//   return; //deshabilitar hasta reparación

// }

// add_action("set_user_role", "sendEmailRole", 10, 3);
// function sendEmailRole($user_id, $new_role, $old_roles){
//   return; //deshabilitar hasta reparación

//   error_log('Usuario ' . $user_id . ' cambió de rol ' . implode(', ', $old_roles) . ' a ' . $new_role);
//   $roles = ["member", "new_member", "vip_member", "existing_member"];
//   if(!in_array($new_role, $roles)){
//     return;
//   }
//   $partnerData = get_userdata($user_id);
//   $partnerName = $partnerData->first_name; 
//   $subject = 'Welcome to VIMA - Your Gateway to Effortless Vacation Property Rentals!';
//   $copyto = 'Bcc: webdevelopment@vacationintervalsmanagement.com';
//   $headers = array('Content-Type: text/html; charset=UTF-8', $copyto );
//   //$body = '<p><pre>'.array($role_new, $user, $role_old). '<pre></p>';
//   $body = '
//   <div style="background-color: #000000; color: #ffffff; font-family: \'Merriweather\', sans-serif; padding: 20px; max-width: 600px; margin: 0 auto; line-height: 1.6; font-size: 16px;">
  
//   <!-- Header con logo y borde -->
//   <div style="text-align: center; margin-bottom: 30px; border-bottom: 8px solid #DEBB27; padding-bottom: 20px;">
//     <img src="https://www.vacationintervalsmanagement.com/wp-content/uploads/2023/09/full_trimmed_transparent_customcolor_auto_x2_toned_light_ai-1-1024x405.jpg" alt="VIMA Logo" style="max-width: 100%; height: auto;">
//   </div>

//   <!-- Contenido del correo -->
//   <div>
//     <p>Dear Owner,</p>

//     <p><strong>Welcome to VIMA Vacation Intervals Management!</strong> We’re thrilled to have you on board. Your enrollment is now complete, and you’re ready to embark on a seamless experience for renting your vacation property.</p>

    

//     <p><strong>Here’s what you can do next in your account:</strong></p>

//     <p><strong>1. Set Up Your Preferred Rentals Request:</strong><br>
//     Your preferences matter to us! In the “My Preferred Rental Request” section, you can select the types of properties you\'re most interested in renting out. As we receive rental requests from our partners, we’ll forward to you the ones that best match your selections—ensuring that our services are tailored to your needs.</p>

//     <p><strong>2. Submit Your Reservations:</strong><br>
//     You can now submit your existing reservation(s). We’ll immediately begin promoting them through our network of exclusive partners to maximize exposure. The process is quick and easy—just log in to your account, go to “Deposit my week,” fill in the form, and submit.</p>

//     <p><strong>3. Request Marketing Campaigns:</strong><br>
//     Have specific weeks you’d like to promote? You can request paid marketing campaigns, and our team will handle the design and promotion to boost your visibility.</p>

//     <p><strong>Even better, enjoy all these features on the go with the VIMA App!</strong><br>
//     Manage rentals, update preferences, and submit reservations from anywhere.
//     <p style="margin-top: 30px;"><strong>Download the VIMA App:</strong></p>
//   <table style="margin-top: 10px;">
//   <tr>
//     <td style="padding-right: 10px;">
//       <a href="https://apps.apple.com/" target="_blank">
//         <img src="https://developer.apple.com/assets/elements/badges/download-on-the-app-store.svg" alt="Download on the App Store" style="height: 42px; display: block;">
//       </a>
//     </td>
//     <td>
//       <a href="https://play.google.com/store" target="_blank">
//         <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" alt="Get it on Google Play" style="height: 42px; display: block;">
//       </a>
//     </td>
//   </tr>
//   </table>

//     <p>We’re here to ensure your experience with VIMA is smooth, efficient, and rewarding. If you have any questions or need support, please don’t hesitate to reach out.</p>

//     <p>Thank you for choosing VIMA.<br>
//     <strong>Warm regards,</strong><br>
//     <strong>The VIMA Vacation Intervals Management Team</strong></p>
//   </div>

//   <!-- Footer -->
//   <div style="margin-top: 40px; border-top: 1px solid #444444; padding-top: 20px; text-align: center; font-size: 14px; color: #cccccc;">
//     <p style="margin: 0;">VIMA Vacation Intervals Management</p>
//     <p style="margin: 0;"><a href="https://vacationintervalsmanagement.com" style="color: #cccccc; text-decoration: underline;">www.vacationintervalsmanagement.com</a></p>
//   </div>

//   </div>
//   ';

//   wp_mail('og.lopar711@gmail.com', $subject, $body, $headers );

// }
// //fin para email de new members


     
function wpse27856_set_content_type(){
    return "text/html";
}
add_filter( 'wp_mail_content_type','wpse27856_set_content_type' );
  
add_action( 'email_notificacion_37_dias', 'notifacarRentalExpirationDate' );     
add_action( 'email_notificacion_97_dias', 'notifacarRentalExpirationDate' );
/* Email de notificación de 97 días restantes  */
  function notifacarRentalExpirationDate($dias = 97) 
  {

    $diasMenosNotificacion = $dias;

    
   
      $args = array(
        'numberposts' => -1,
        'post_type'   => 'rental-submissions',
        'order'       => 'ASC',
        'orderby'     => 'title'
      );
      $rentalSubmits = get_posts( $args );


      foreach($rentalSubmits as $rentalSubmit):
        

       $fechaInicio = get_post_meta($rentalSubmit->ID, 'date-of-start');
       $fechaInicio = strtotime(date("Y-m-d",$fechaInicio[0]));
       $fechaMas97dias = strtotime(date("Y-m-d", $fechaInicio).'-'.$diasMenosNotificacion.' day');
       $fechaHoy = strtotime(date("Y-m-d"));
       $fechaMenos97dias = $fechaMas97dias;

        if(intval($fechaInicio) > intval($fechaMas97dias)){
            
           // var_dump($fechaHoy);
           // var_dump($fechaMenos97dias);
           $tomail = get_post_meta($rentalSubmit->ID, 'email')[0];
            if($fechaHoy == $fechaMenos97dias ){
               
                 $to = $tomail;
               // $to = "asesor.ingles.f@gmail.com"; 
                //$to = "og.lopar711@gmail.com";  
               
               
                $subject = 'Reservation about to expire - '.$diasMenosNotificacion.' days left';
               // var_dump(get_post_meta($rentalSubmit->ID, 'unit-for-rent')[0]);
                //$unitObjReserved = obtenerUnitType(get_post_meta($rentalSubmit->ID, 'unit-for-rent')[0]);
                $unitObjReserved = get_post_meta($rentalSubmit->ID, 'unit-for-rent')[0];
                $arrUnit = explode(" ", $unitObjReserved);
                $unitReserved = $arrUnit[0];
                $nombreToMeta = get_post_meta($rentalSubmit->ID, 'nombre')[0];
                $nombretoArr = explode(" ", $nombreToMeta);
                $sizeArrUnit = sizeof($arrUnit);
                $sobrantes = $sizeArrUnit -2;
                

                $locationUnit = "";

                for($x = 0; $x < $sobrantes; $x++):

                  $locationUnit .= $arrUnit[$x]." ";

                endfor;
              $body = '<body style="font-family: Arial, sans-serif; color: ghostwhite;letter-spacing: .5px; line-height: 1.6; margin: 0; padding: 0;">
                  <div style="width: 100%; max-width: 1024px; margin: 0 auto; padding: 40px; text-align: justify; font-size: 22px; border: 1px solid #ddd; border-radius: 8px; background-color: black !important; font-family: Cormorant Garamond;">
                      
                      <div style="text-align: center; padding-bottom: 20px;">
                        <div style="border-style: solid;border-width: 0px 0px 8px 0px;border-color: #DEBB27; padding-bottom: 20px;">
                          <img src="https://www.vacationintervalsmanagement.com/wp-content/uploads/2023/09/full_trimmed_transparent_customcolor_auto_x2_toned_light_ai-1-1024x405.jpg" width="65%">
                        </div>  
                        
                          
                          <br />
                          <p style="margin: 0;">Hi '.$nombretoArr[0].', hope this message finds you well</p>
                          <p style="margin: 0;">This is VIMA Vacation Intervals Management Team.<br /></p>
                      </div>
                      
                      
                      
                      <p>This email has been sent to remind you that the following week(s) hasn\'t been rented yet and that the time to cancel or change your reservation with Vidanta is close to expiring:</p>
                      
                      <p>Week(s) reserved: <strong>'.date("l M/d/Y",get_post_meta($rentalSubmit->ID, 'date-of-start')[0]).' - '.date("l M/d/Y",get_post_meta($rentalSubmit->ID, 'date-of-end')[0]).' </strong><br />
Unit reserved: <strong>'.$locationUnit.'</strong> <br />
Location:<strong> '.$arrUnit[$sizeArrUnit -2].' '.$arrUnit[$sizeArrUnit -1].'</strong><br /><br /></p>

                      <p>Please log in to your account on our website and check the Availability Calendar tab where you\'ll be able to see all the units and the timeframes available.<a href="https://vacationintervalsmanagement.com/login">Click here to log in to our website</a></strong></p>

                      
                      <p> If you have any questions, concerns, or if there\'s anything else we can assist you with, don\'t hesitate to reach out.</p>
                      <br />
                      <p>Thank you for choosing VIMA!</p>
                      
                      <p>Best regards,<br>
                      VIMA Vacation Intervals Management Team</p>
                  </div>
              </body>';    
//       $bodyOld = "
// Hi ".$nombretoArr[0].", hope this message finds you well.<br /><br />

// This is VIMA Vacation Intervals Management Team.<br /><br />

// TThis email has been sent to remind you that the following week(s) hasn't been rented yet and that the time to cancel or change your reservation with Vidanta is close to expiring:<br /><br />

// Week(s) reserved: <strong>".date("l M/d/Y",get_post_meta($rentalSubmit->ID, 'date-of-start')[0])." - ".date("l M/d/Y",get_post_meta($rentalSubmit->ID, 'date-of-end')[0])." </strong><br />
// Unit reserved: <strong>".$locationUnit."</strong> <br />
// Location:<strong> ".$arrUnit[$sizeArrUnit -2]." ".$arrUnit[$sizeArrUnit -1]."</strong><br /><br />

// Please log in to your account on our website and check the Availability Calendar tab where you'll be able to see all the units and the timeframes available.<br /><br />

// <strong><a href='https://vacationintervalsmanagement.com/login'>Click here to log in to our website</a></strong><br /><br />

// If you have any questions, please don't hesitate to ask and we'll be glad to assist you! 
// <br /><br />

// <p>Best regards<br>
//         VIMA Vacation Intervals Management Team</p>
// ";
                $copyto = 'Bcc: manager@vacationintervalsmanagement.com';
                $headers = array('Content-Type: text/html; charset=UTF-8', $copyto );
                wp_mail( $to, $subject, $body, $headers );
            
            }
            
        }
      endforeach;
   }

   add_filter('wp_mail_from','yoursite_wp_mail_from');
    function yoursite_wp_mail_from($content_type) {
    return 'clientcare@vacationintervalsmanagement.com';
    }

    add_filter('wp_mail_from_name','yoursite_wp_mail_from_name');
    function yoursite_wp_mail_from_name($name) {
    return 'VIMA Vacation Intervals Management';
    }
