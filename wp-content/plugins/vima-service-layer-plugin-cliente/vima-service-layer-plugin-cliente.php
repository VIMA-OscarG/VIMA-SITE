<?php

/*
Plugin Name: VIMA - Service Layer Plugin Client
Description: Se usa para consumir de la API que proporciona la Service Layer.
Version: 1.0
Author: Oscar G
*/

class VIMAServiceLayerClass{


    public function uploadImagenConfirmation($img, $id_admin_rental_request){

        $api_url = 'https://www.vacationintervalsmanagement.com/service-layer/api/v1/vima/admin/upload-confirmation-image/'.$id_admin_rental_request;

        $post_data = array(
            'img' => $img
        );
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: "Content-Type: multipart/form-data;',
            'Content-Length: ' . strlen($json_data),
            'Authorization: Basic '. base64_encode("anton@vacationintervalsmanagement.com:VimaServiceLayer##2023-2024##!?")
        ));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error en la solicitud cURL: ' . curl_error($ch);
        }

        curl_close($ch);

       return $response;
    }


    public function getUnitType($unidad){
        return $unidad;
    }


    public function getSalesChannelInfo(){
        $api_url = 'https://www.vacationintervalsmanagement.com/service-layer/api/v1/vima/sale-channels';
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            
            'Authorization: Basic '. base64_encode("anton@vacationintervalsmanagement.com:VimaServiceLayer##2023-2024##!?")
        ));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error en la solicitud cURL: ' . curl_error($ch);
        }

        curl_close($ch);
       return $response;
    }
    public function getUserNotifications($useremail){
        

        $api_url = 'https://www.vacationintervalsmanagement.com/service-layer/api/v1/vima/user-notifications';

        $post_data = array(
            'partneremail' => base64_encode($useremail)
        );

        $json_data = json_encode($post_data);

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json_data),
            'Authorization: Basic '. base64_encode("anton@vacationintervalsmanagement.com:VimaServiceLayer##2023-2024##!?")
        ));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error en la solicitud cURL: ' . curl_error($ch);
        }

        curl_close($ch);

       return $response;
    }


    public function getRequestAdminMessages($useremail, $related_request_id){
        $api_url = 'https://www.vacationintervalsmanagement.com/service-layer/api/v1/vima/get-request-admin-messages';

        $post_data = array(
            'partneremail' => base64_encode($useremail),
            'related_request_id'=> intval($related_request_id)
        );

        $json_data = json_encode($post_data);

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json_data),
            'Authorization: Basic '. base64_encode("anton@vacationintervalsmanagement.com:VimaServiceLayer##2023-2024##!?")
        ));


        $response = curl_exec($ch);


        if (curl_errno($ch)) {
            echo 'Error en la solicitud cURL: ' . curl_error($ch);
        }

        curl_close($ch);

       return $response;
    }


    public function getNoRRRequestAdminMessages($useremail){
        $api_url = 'https://www.vacationintervalsmanagement.com/service-layer/api/v1/vima/get-request-admin-messages';

        $post_data = array(
            'partneremail' => base64_encode($useremail)
        );


        $json_data = json_encode($post_data);


        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json_data),
            'Authorization: Basic '. base64_encode("anton@vacationintervalsmanagement.com:VimaServiceLayer##2023-2024##!?")
        ));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error en la solicitud cURL: ' . curl_error($ch);
        }

        curl_close($ch);


       return $response;
    }

    public function filtrarNotificacionesParaEsteUser($notificaciones, $userEmail){
       // echo "<pre>".print_r($userEmail, true). "</pre>";
        $notifsUser = [];

        foreach ($notificaciones as $notif){

          //  echo "<pre>".print_r($notif['related_admin_rental_request']['status'], true). "</pre>";
          //  echo "<pre>".print_r($notif['related_admin_rental_request']['attending_by'], true). "</pre>";
           
            if($notif['related_admin_rental_request']['status'] != "verified"){
                array_push($notifsUser, $notif);
            }
            if($notif['related_admin_rental_request']['status'] === "verified"){

                if($notif['related_admin_rental_request']['attending_by'] == $userEmail){
                    array_push($notifsUser, $notif);
                }
                
            }
            
        }

                


        return $notifsUser;
    }

    public function encriptar($data, $clave) {
        $method = 'AES-256-CBC'; 
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method)); 
        $encriptado = openssl_encrypt($data, $method, $clave, 0, $iv);
        return base64_encode($iv . $encriptado); 
    }
    
    public function desencriptar($data, $clave) {
        $method = 'AES-256-CBC';
        $data = base64_decode($data); 
        $iv_length = openssl_cipher_iv_length($method);
        $iv = substr($data, 0, $iv_length); 
        $encriptado = substr($data, $iv_length);
        return openssl_decrypt($encriptado, $method, $clave, 0, $iv);
    }


}

add_shortcode( 'user-notifications-list', function(){
    $current_user = wp_get_current_user(); 
  //  echo "<pre>".print_r($current_user->user_email, true). "</pre>";

    $vimaServiceLayer = new VIMAServiceLayerClass();
    $resNotificationsUser = $vimaServiceLayer->getUserNotifications($current_user->user_email);

   // echo "<pre>".print_r($resNotificationsUser, true). "</pre>";

    $notificacionesUser = json_decode($resNotificationsUser, true);
  
    
    
    $notificatoins = $notificacionesUser['data']['notifications'];

   
    //echo "<pre>".print_r($notificatoins, true). "</pre>";
    $notifsUser = $vimaServiceLayer->filtrarNotificacionesParaEsteUser($notificatoins, $current_user->user_email);
    //echo "<pre>".print_r($notifsUser, true). "</pre>";
    //echo "<pre>".print_r($notificatoins, true). "</pre>";
    $notificatoins = $notifsUser;
    
    if(is_array($notificatoins)){


        ///resaltar mensajes nuevos

        //$mensajes = $notificatoins;

        function guardar_cantidad_notifications_previa($user_id, $cantidad_notifications_previa) {
            update_user_meta($user_id, 'cantidad_notifications_previa', $cantidad_notifications_previa);
        }

      
        function obtener_cantidad_notifications_previa($user_id) {
            return get_user_meta($user_id, 'cantidad_notifications_previa', true);
        }

       
        function obtener_cantidad_notifications_nuevos($mensajes_actuales, $user_id) {
            $cantidad_notifications_previa = obtener_cantidad_notifications_previa($user_id);
            
            //var_dump("Cantidad de mensajes previos: " . $cantidad_notifications_previa);
            //var_dump("Cantidad de mensajes actuales: " . $mensajes_actuales);

            if($cantidad_notifications_previa !== false && $mensajes_actuales > $cantidad_notifications_previa) {
               
                $cantidad_notifications_nuevos = $mensajes_actuales - $cantidad_notifications_previa;
                return $cantidad_notifications_nuevos;
            } else {
                // No hay nuevos mensajes
                return 0;
            }
        }

        $cantidad_notifications_actuales = sizeof($notificatoins);

        $user_id = get_current_user_id();
        //var_dump("ID de usuario actual: " . $user_id);

        $cantidad_notifications_nuevos = obtener_cantidad_notifications_nuevos($cantidad_notifications_actuales, $user_id);

        //echo "Cantidad de mensajes nuevos: " . $cantidad_notifications_nuevos;

        guardar_cantidad_notifications_previa($user_id, $cantidad_notifications_actuales);

        /////termina resaltar mensajes nuevos

        echo '<div x-data="{ active: null }" class="mx-auto max-w-3xl w-full min-h-[16rem] space-y-4">';
        $countItems = 1;
        $numeroDeMensaje = 0;
        foreach ($notificatoins as $userNotif){
         //  echo "<pre>".print_r($userNotif['related_admin_rental_request'], true)."</pre>";
            $statusRentalRequest = $userNotif['related_admin_rental_request']['status'];
            $guestRentalRequest = $userNotif['related_admin_rental_request']['guest'];
            $unitType = $vimaServiceLayer->getUnitType($userNotif['related_admin_rental_request']['unidad']);
            $requestIdFromNotif = $userNotif['related_admin_rental_request']['id_admin_rental_request'];
            // echo "<br>status:".$userNotif['status'];

            // echo "<br>updated at:". $userNotif['updated_at'];
            // echo gettype($userNotif['related_admin_rental_request']);
            // echo "<br>turn :". $userNotif['turno'];
            // echo "<br><br> ";
             $idRequest = $userNotif['related_admin_rental_request']['id_admin_rental_request'];
             
             $requestTurnoActual = $userNotif['related_admin_rental_request']['actual_turn'];

             $notifString = gettype($userNotif['related_admin_rental_request']) === 'array' 
                                    ? implode(",", $userNotif['related_admin_rental_request']) 
                                    : "";
            $userNotif['related_admin_rental_request'] = $notifString ;
            
            $cantidadDeNuevos = $cantidad_notifications_nuevos;
                                   //$esNuevo = "color: white;";

                                   if($numeroDeMensaje < $cantidadDeNuevos){
                                    $newEtiqueta = '<span style="
                                                                margin: 0 auto;
                                                                /* line-height: 0px; */
                                                                vertical-align: bottom;
                                                                margin-bottom: -45px;
                                                                padding-left: 6px;
                                                                padding-right: 6px;
                                                                display: block;
                                                                text-transform: uppercase;
                                                                font-weight: 800;
                                                                color: dimgrey;
                                                                background-color: white;
                                                                position: relative;
                                                                width: fit-content;
                                                                float: left;
                                                                border-radius: 3px 0px 3px 0px;
                                                                border-top: 1px solid #DEBB27;
                                                                border-left: 1px solid #DEBB27;
                                                                
                                                            ">New</span>';
                                    $esNuevo = "background-color: #DEBB27 !important; color: black !important;";
                                   }else{
                                    $newEtiqueta = "";
                                    $esNuevo = "background-color: #414242; color: white !important;";
                                   }

                                  // var_dump($numeroDeMensaje);

                                    $numeroDeMensaje++;
            
            if($statusRentalRequest == 'responded'){

                //  $paramsEnLaUrl= implode(',',$userNotif);
                //  $paramsEnLaUrl = str_replace("&","%26",$paramsEnLaUrl);

                $userNotif['request_turno'] = $requestTurnoActual;
                
                 $serializedData = serialize($userNotif);
                 //echo "<pre>".print_r( $serializedData, true). "</pre>";
                 $encodedData = base64_encode($serializedData);
                 //echo "<pre>".print_r( $encodedData, true). "</pre>";
                 $paramsEnLaUrlEncoded =  urlencode($encodedData);
                 //echo "<pre>".print_r( $paramsEnLaUrlEncoded, true). "</pre>";
                 //$unba = base64_decode($paramsEnLaUrlEncoded);
                 //$unse = unserialize($unba);
                 $encData = str_ireplace("+", "%2B", $vimaServiceLayer->encriptar($paramsEnLaUrlEncoded, 'iwsue6749!!"#'));

                // echo "<pre>".print_r( str_ireplace("+", "%2B", $encData), true). "</pre>";
                 
               

                        echo $newEtiqueta. '<a href="../../vima-service-page?ed='.$encData.'" x-on:click="$refs.textItem.innerHTML = \'Redirecting, wait a moment please... <img style=width:20px;height:20px;object-fit:fill;display:inline;margin-left:10px; src=https://vacationintervalsmanagement.com/wp-content/plugins/vima-service-layer-plugin-cliente/loader-circle.gif /> \' " data-request-id="'.$idRequest.'" style="display:block;"><div class="class="rounded-lg bg-neutral-700 request_responded" style="'.$esNuevo.';
                        border-radius: 8px;"><h2 class="text-white">
                        <button
                            class="flex w-full items-center justify-between px-3 py-2 text-base font-bold"
                            
                            
                        >
                            <span class="text-base" x-ref="textItem" style="text-align: center; width:-webkit-fill-available; font-size: small !important; font-weight: 500;">&nbsp;'.$unitType.'-'.$requestIdFromNotif.' &nbsp;&nbsp;'. strtoupper($statusRentalRequest).'&nbsp;&nbsp;<span style="font-size: smaller;">Upd. '.$userNotif['updated_at'].'</span>'.'</span>
                           
                        </button>
                    </h2></div></a>';
            }

            if($statusRentalRequest == 'declined'){
               

                        echo '<a href="#" style="display:block;"><div class="class="rounded-lg bg-neutral-700 request_declined" style="'.$esNuevo.';
                        border-radius: 8px; opacity: 0.5;"><h2 class="text-white">
                        <button disabled
                            class="flex w-full items-center justify-between px-3 py-2 text-base font-bold"
                            
                            
                        >
                            <span class="text-base" style="text-align: center; width:-webkit-fill-available; font-size: small !important; font-weight: 500;">&nbsp;'.$unitType.'-'.$requestIdFromNotif.' &nbsp;&nbsp;'. strtoupper($statusRentalRequest).'&nbsp;&nbsp;<span style="font-size: smaller;">Upd. '.$userNotif['updated_at'].'</span>'.'</span>
                           
                        </button>
                    </h2></div></a>';
            }

            if($statusRentalRequest == 'fulfilled'){
            

                    ?>

                    
                    
                        <div x-data="{
                            id: <?php echo $countItems; ?>,
                            get expanded() {
                                return this.active === this.id
                            },
                            set expanded(value) {
                                this.active = value ? this.id : null
                            },
                            }" role="region" class="rounded-lg bg-neutral-700 request_fulfilled">
                            <h2 class="text-white">
                                <button
                                    x-on:click="expanded = !expanded"
                                    :aria-expanded="expanded"
                                    class="flex w-full items-center justify-between px-3 py-2 text-base font-bold"
                                >
                                    <span class="text-base" style="text-align: center; width:-webkit-fill-available; font-size: small !important; font-weight: 500;">&nbsp;<?php echo $unitType ."-".$requestIdFromNotif."&nbsp;&nbsp;". strtoupper($statusRentalRequest)."&nbsp;&nbsp;<span style='font-size: smaller;'>Upd. ".$userNotif['updated_at']."</span>" ?></span>
                                    <span x-show="expanded" aria-hidden="true" class="ml-4">&minus;</span>
                                    <span x-show="!expanded" aria-hidden="true" class="ml-4">&plus;</span>
                                </button>
                            </h2>

                            <div x-show="expanded" x-collapse style="display:none">
                                <div class="px-6 pb-4 mt-4 bg-white text-base text-black border-b-1 pt-4 rounded-b-lg border-solid border-2 border-neutral-700 " style="text-align: center; width:-webkit-fill-available; font-size: small !important; font-weight: 500;">Waiting for admin verification</div>
                            </div>
                        </div>

                    

                    <?php
            }
            if($statusRentalRequest == 'verified'){
            

                ?>

                
                
                    <div x-data="{
                        id: <?php echo $countItems; ?>,
                        get expanded() {
                            return this.active === this.id
                        },
                        set expanded(value) {
                            this.active = value ? this.id : null
                        },
                        }" role="region" class="rounded-lg bg-neutral-700 request_verified" style="background-color: darkolivegreen;">
                        <h2 class="text-white">
                            <button
                                x-on:click="expanded = !expanded"
                                :aria-expanded="expanded"
                                class="flex w-full items-center justify-between px-3 py-2 text-base font-bold" 
                            >
                                <span class="text-base" style="text-align: center; width:-webkit-fill-available; font-size: small !important; font-weight: 500;">&nbsp;<?php echo $unitType ."-".$requestIdFromNotif." &nbsp;&nbsp;". strtoupper($statusRentalRequest)."&nbsp;<i class='fas fa-check'></i>&nbsp;&nbsp;&nbsp;&nbsp;<span style='font-size: smaller;'>Upd. ".$userNotif['updated_at']."</span>" ?></span>
                                <span x-show="expanded" aria-hidden="true" class="ml-4">&minus;</span>
                                <span x-show="!expanded" aria-hidden="true" class="ml-4">&plus;</span>
                            </button>
                        </h2>

                        <div x-show="expanded" x-collapse style="display:none">
                            <div class="px-6 pb-4 mt-4 bg-white text-base text-black border-b-1 pt-4 rounded-b-lg border-solid border-2 border-neutral-700 " style="text-align: center; width:-webkit-fill-available; font-size: small !important; font-weight: 500;">The confirmation of the reservation under the guest name has been verified. To see payout date and the cancelleation deadline please visit the rental request page by clicking on the button below.<br><br><button class="chere" style="  padding: 10px 5px;
                padding: 5px 15px;
                background-color: gray;
                color: whitesmoke;
                border: 1px solid #666;
                border-radius: 5px;
        ">Click here</button></div>
                        </div>
                    </div>

                

                <?php
        }
        $countItems++;
        }
        echo '</div>';
    }

   
       
   

    
});

// SERVICE PAGE CONTENT


add_shortcode('service-page-content', function(){


    $arrTurnos = [
        "en_turno" => 1,
        "proximo_turno" => 2,
        "turno3" => 3,
        "turno4" => 4,
        "turno5" => 5,
        "turno6" => 6,
        "turno7" => 7,
        "turno8" => 8
    ];


    $dataGet = $_GET;
    $arrDataRR = array();
    $vimaServiceLayer = new VIMAServiceLayerClass();
    $salesChannelsArr =  json_decode($vimaServiceLayer->getSalesChannelInfo(), true)['data']['sale_channels'];

    
    // foreach ($dataGet as $key => $data){
    //     $arrDataRR = explode(",", $key);
    // }

    
    $unc = $vimaServiceLayer->desencriptar($dataGet['ed'], 'iwsue6749!!"#');
    //echo "<pre>".print_r($vimaServiceLayer->desencriptar($dataGet['ed'], 'iwsue6749!!"#'), true). "</pre>";
    
    $unba = base64_decode($unc);
    $unse = unserialize($unba);
    
    $requestInfo = $unse['related_admin_rental_request'];

    $arrDataRR = explode(",", $requestInfo);

    unset($unse['related_admin_rental_request']);
    $userInfo = $unse;

    array_unshift($arrDataRR, $userInfo['user_email']);
    $arrDataRR[1] = str_replace(" ", "_", $arrDataRR[1]);
    $arrDataRR[17] = $userInfo['turno'];
    $arrDataRR[18] = $userInfo['turno_no'];
    //echo "<pre>". print_r($requestInfo, true). "</pre>";
    //echo "<pre>". print_r($arrDataRR, true). "</pre>";
    //echo "<pre>".print_r($userInfo, true). "</pre>";

//    
    $logoChannel = "";
   // echo "<pre>". print_r($arrDataRR, true). "</pre>";
    $channelFprThis = str_replace("%26", htmlspecialchars_decode('%26'),  $arrDataRR[4]);
    $channelFprThis = str_replace("_", " ",  $arrDataRR[4]);
    

    

    //$buscarCharacteresEspciales = preg_match_all('/[A-Za-z0-9áéíóúÁÉÍÓÚüÜñÑ]/', $arrDataRR[13], $matches);

    //  echo "<pre>". print_r($matches, true). "</pre>";

    $guestData = str_replace("_", " ",  $arrDataRR[13]);

   


      //echo "<pre>". print_r($arrDataRR[13], true). "</pre>";
    $providerData = $arrDataRR[0];
   // var_dump($arrDataRR[4]);
    if($arrDataRR[4] == "Booking_com"){
        $channelFprThis = str_replace("_", ".",  $arrDataRR[4]);
    }
     
    if (array_key_exists($channelFprThis, $salesChannelsArr)) {
      //  echo "Channel found in the array";
        $logoChannel = $salesChannelsArr[$channelFprThis]['logo_img'];
        $PayPolicy = $salesChannelsArr[$channelFprThis]['Payment Policy'];
        $CancelPolicy = $salesChannelsArr[$channelFprThis]['Cancellation Policy'];
    }
    //echo "<pre>". print_r($logoChannel, true). "</pre>";
   // echo "<pre>". print_r($channelFprThis, true). "</pre>";

    $unidadArr = explode("_",$arrDataRR[1]);
   
    $numPal = count($unidadArr);
   // echo "<pre>". print_r( ($numPal -2), true). "</pre>";
    $nombreUnidad = implode(" ", array_slice($unidadArr, 0, ($numPal -2)));
    $destinoUnidad = implode(" ", array_splice($unidadArr, ($numPal -2)));

    if(intval($arrDataRR[2] > 9999999999)){
        $fechas = date("d/M/Y", (intval($arrDataRR[2]) / 1000))." - ".date("d/M/Y", (intval($arrDataRR[3]) / 1000));
    }else{
        $fechas = date("d/M/Y", (intval($arrDataRR[2])))." - ".date("d/M/Y", (intval($arrDataRR[3])));
    }

    $mensajesrequest = $vimaServiceLayer->getRequestAdminMessages(str_replace("_", ".", $arrDataRR[0]), $arrDataRR[12] );
    $arrMensajesRequest = json_decode($mensajesrequest, true);
    $partnerEmail = str_replace("_", ".", $arrDataRR[0]);
    //echo "<pre>". print_r(base64_encode($partnerEmail) , true). "</pre>";
    //echo "<pre>". print_r($arrMensajesRequest, true). "</pre>";

    ?>

    <style type="text/css">

    .service-page-main-content{
        width: 100%;
        background-color: black;
        text-align: center;
        /* cursor: pointer; */
        height: auto;
    }

    .tercio{
        width: 28%;
        margin:.5%;
        /* background-color: #666; */
        
        /* cursor: pointer; */
        height: 450px;
        display: inline-table;
        padding-top: 5px;
        text-align:center;
        
        
    }
    .tercio h4 {
        font-size: 15px !important;
        color:#f0f0f0;
    }

    .tercio p {
        font-size: 13px !important;
        color:#f0f0f0;
        margin-bottom:15px;
      
    }
    .tercio p img{
        margin: auto;
    }

    .container label{
        cursor: pointer;
    }
    .container input{
        cursor: pointer;
    }

    .container:has(input[type="button"]:disabled) {
        
        opacity: .6;
    }

    .mx-auto{
        
        color: #fff;
        cursor: pointer;
        /* margin-top:30px; */
    }

    #modalConfirmacion{
        color: #000;
    }
    #modalConfirmacion p{
        color: #000;
    }

    #modal_confirmation  *{
        text-align: center;
    }
    #modal_confirmation p img{
        text-align: center;
        margin: auto;
    }
    #modal_confirmation_h2 p img{
        margin: auto;
    }

    button:focus {
    
    outline: none;
    }

    #numero_msj{
        position: relative;
        top: -2px;
        left: -2px;
        vertical-align: text-top;
        font-size: small;
        font-family: monospace;
    }
    #numero_msj_1{
        position: relative;
        top: -2px;
        left: -2px;
        vertical-align: text-top;
        font-size: small;
        font-family: monospace;
    }

    .at{
        font-style: italic;
        
    }

    .boton_regresar{

        display: flex;
        margin-left: 10px;
        padding-top: 10px;
        cursor: pointer;
        }

    .request_declined{
        opacity: 0.4;
    }
    .request_verified{
        background-color: darkolivegreen;
    }

   

    @media only screen and (max-width: 899px) {
        .tercio{
        
            width: 100%;
            height: auto;
            border-bottom: 1px solid #DCBE42;
            margin: 10px 0px 10px 0px;
            padding-bottom: 20px;
        
        }  
        .service-page-main-content{
           
            height: auto;
        }

        .tercio p {
            padding: 0px 15px 0px 15px;
      
        }
    

        
    }

    </style>

        <script type="text/javascript">

            const enviarImagenConfirmation = (frmData, id_rr) =>{
               
            }



            const sendImageConfirmation = () => {
            
               
            }

            const acceptRequest = (requestID, partneremail) => {

                               
                const data = { requestid: requestID, partneremail: partneremail, action: "accepted" };

                fetch('../wp-content/plugins/vima-service-layer-plugin-cliente/jsapihandler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(responseData => {
                    console.log('Respuesta del servidor:', responseData);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }

            const declineRequest = (requestID, partneremail) => {
                               
                               const data = { requestid: requestID, partneremail: partneremail, action: "declined" };
               
                               fetch('../wp-content/plugins/vima-service-layer-plugin-cliente/rejectRequest.php', {
                                   method: 'POST',
                                   headers: {
                                       'Content-Type': 'application/json'
                                   },
                                   body: JSON.stringify(data)
                               })
                               .then(response => response.json())
                               .then(responseData => {
                                   console.log('Respuesta del servidor:', responseData);
                               })
                               .catch(error => {
                                   console.error('Error:', error);
                               });
                           }
            

        </script>
       
            

        
        <div class="service-page-main-content">
            

        <div class="boton_regresar">
            <a href="https://www.vacationintervalsmanagement.com/member-area/rental-requests-responded/">
        <span class="jet-elements-icon jet-more-icon" style="font-size:x-large;"><i aria-hidden="true" class="fas fa-arrow-left"></i></span>
        </a>
        </div>

        <?php 
        
            //echo "<pre>".print_r(empty($arrMensajesRequest['data']['messages']), true). "</pre>";
            //if(sizeof($arrMensajesRequest['data']['messages']) > 0){
            if(!empty($arrMensajesRequest['data']['messages'])) {
            
        ?>
        
        <div class="snap-center	 justify-center w-full bg-black" style="margin:auto;">
            
            <div x-data="{ active: 1 }" class="mx-auto max-w-3xl w-full">
                <div x-data="{
                    id: 1,
                    get expanded() {
                        return this.active === this.id
                    },
                    set expanded(value) {
                        this.active = value ? this.id : null
                    },
                }" role="region" class="rounded-lg bg-white shadow">
                    <h2 class="bg-black text-white">
                        <button
                            x-on:click="expanded = !expanded"
                            :aria-expanded="expanded"
                            class="flex w-full items-center justify-between px-6 py-4 text-sm font-bold "
                           
                        >
                       

                        <span class="jet-elements-icon jet-more-icon" style="font-size:x-large;"><i aria-hidden="true" class="fas fa-envelope"></i>
                            <span id="numero_msj"><?php echo sizeof($arrMensajesRequest['data']['messages']); ?></span>
                        </span>
                        
                            <span x-show="expanded" aria-hidden="true" class="ml-4">&minus;</span>
                            <span x-show="!expanded" aria-hidden="true" class="ml-4">&plus;</span>
                        </button>
                    </h2>

                    <div x-show="expanded" x-collapse class="bg-black text-white text-sm">
                        <div class="px-6 pb-4">
                            <div class="text-sm font-sm">
                            <?php 
                                foreach($arrMensajesRequest['data']['messages'] as $msj){
                                   //echo "<pre>".print_r($msj, true). "</pre>";
                                   ?>
                                    <div class="mensaje_admin text-justify px-6 py-4" style="font-size:small; border-bottom: 1px solid gray;">
                                        <span class="at"><?php echo $msj['created_at']; ?></span>
                                        <br />
                                        <span class="msj_text"><?php echo $msj['text']; ?></span>
                                    </div>
                            <?php
                                }
                            ?>
                            </div>
                        </div>
                    </div>
                </div>

                
            </div>
            
        </div>
        <?php } ?>
        <form entype="multipart/form-data" type="post" x-ref="fromcor" id="fromcor">

            <div class="rental-request-details-section tercio">
            <br />
                <h4>Rental Request Details</h4>
                </br />
                <p>Unit:<br /><b> <?php echo $nombreUnidad ?> </b></p>
                <p>Destination:<br /><b> <?php echo $destinoUnidad ?> </b></p>
                <p>Dates:<br /> <b><?php echo $fechas ?> </b></p>
                <p>Guest:<br /><b><?php echo $guestData ?> </b></p>
                

            </div>
            <div class="sales-channel-section tercio">
            <br />
                <h4>Sales Channel Details</h4>
                
                <p>Sales channel:<br /><b> <?php echo $channelFprThis ?> </b></p>
                <p><img src="<?php echo $logoChannel ?>" width="35%" height="auto"></p>
                <p><h4>Sales channel policies</h4></p>
                <p class="just">Payment Policy: <br /><?php echo $PayPolicy ?></p>
                <p class="just">Cancellation Policy: <br /><?php echo $CancelPolicy ?></p>
            </div>
            <div class="turn-upload-section tercio"  >
            <br />
                
           
                                

               
                <h4><p><b><?php 

                        $turnoEste = $arrTurnos[$arrDataRR[17]];
                        $esSuTurno = $turnoEste == $arrDataRR[10] ? true : false;

                   // echo "<pre>".print_r($arrDataRR, true). "</pre>";
                  //  echo  intval($arrDataRR[10]) == intval($arrDataRR[18]) ?  "You are currently  in turn to Respond this Rental Request":"You are currently in line to respond. If the person currently taking their turn is unable to fulfill this request, you will receive a notification indicating that it is your turn to respond.";
                  echo $esSuTurno ?  "You are currently  in turn to Respond this Rental Request":"You are currently in line to respond. If the person currently taking their turn is unable to fulfill this request, you will receive a notification indicating that it is your turn to respond.";

                ?></b></p></h4>
                </br />

                <?php
                    // echo "<pre>".print_r($arrDataRR, true)."</pre>";
                    if($esSuTurno):
                     //   echo "<pre>".print_r($arrDataRR, true)."</pre>";
                ?>

                <p class="just"> Send the image of the confirmation of the reservation with guest names to manager@vacationintervalsmanagement.com. Then, proceed by clicking the ACCEPT button; otherwise, click DECLINE to end your turn.</p>
                
                <div x-data="{ files: 'algo' }">
                <input type="hidden" value="<?php echo $arrDataRR[12];?>" id="requestID"  x-ref="requestID">
                <input type="hidden" value="<?php echo base64_encode($partnerEmail);?>" id="partnerEmail" x-ref="partnerEmail" >
                <div class="container mx-auto my-6" >
            
                        <div x-data="{ foo: false, texto_titulo: 'ACCEPTED', texto_popup:'', aceptado: false, declinado: false, }" class="flex justify-center">
                        
                        <!-- Modal -->
                        <div
                            id= "modalConfirmacion"
                            x-show="foo"
                            style="display: none"
                            x-on:keydown.escape.prevent.stop="foo = false"
                            role="dialog"
                            aria-modal="true"
                            x-id="['modal-title']"
                            :aria-labelledby="$id('modal-title')"
                            class="fixed inset-0 z-10 overflow-y-auto"
                        >
                            <!-- Overlay -->
                            <div x-show="foo" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-50"></div>

                            <!-- Panel -->
                            <div
                                x-show="foo" x-transition
                                x-on:click="foo = false"
                                class="relative flex min-h-screen items-center justify-center p-4"
                            >
                                <div
                                    x-on:click.stop
                                    x-trap.noscroll.inert="foo"
                                    class="relative w-full max-w-2xl overflow-y-auto rounded-xl bg-white p-12 shadow-lg"
                                >
                                    <!-- Title -->
                                    <h2 class="text-3xl font-bold" :id="$id('modal-title')" x-text="texto_titulo" ></h2>

                                    <!-- Content -->
                                    <p class="mt-2 text-gray-600"><span x-text="texto_popup"></span></p>
                                   
                                    <!-- Buttons -->
                                    <div class="mt-8 flex space-x-2 snap-center">
                                        <a href="https://www.vacationintervalsmanagement.com/member-area/rental-requests-responded/"
                                        style="display:block;margin: auto;">
                                        <button type="button"
                                        style="margin: auto;" 
                                        x-on:click="(event) => {
                                           // event.preventDefault(); 
                                           // event.stopPropagation(); 
                                            foo = false; 
                                            console.log(aceptado);
                                            console.log(declinado);
                                            if (aceptado) {
                                                acceptRequest($refs.requestID.value, $refs.partnerEmail.value);
                                                console.log(aceptado);
                                            }
                                            if (declinado) {
                                                declineRequest($refs.requestID.value, $refs.partnerEmail.value);
                                                console.log(declinado);
                                            }
                                        }" 
                                        class="rounded-md border border-gray-200 bg-white px-5 py-2.5 snap-center"
                                        value="<?php echo $arrDataRR[12] ?>">
                                            Confirm
                                        </button>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <label class="border-2 border-gray-200 p-3 w-full block rounded cursor-pointer my-2 mr-2" for="btnSubmit2" >
                            <input type="button" class="sr-only" id="btnSubmit2" value="accept_<?php echo $arrDataRR[12] ?>" x-on:click ="foo = true; texto_titulo = 'RESPONDED'; texto_popup = 'You have responded to this request.'; aceptado = true">
                            <span x-text="'ACCEPT'"></span>
                        </label>
                        <br />
                        <label class="border-2 border-gray-200 p-3 w-full block rounded cursor-pointer my-2 ml-2" for="btnSubmit"  x-bind:class="{ 'opacity-80': true}">
                                    <input type="button" class="sr-only" id="btnSubmit" value="decline_<?php echo $arrDataRR[12] ?>" x-on:click ="foo = true; texto_titulo = 'DECLINED'; texto_popup = 'You have declined to respond this request.'; declinado =  true">
                                    <span x-text="'DECLINE'"></span>
                        </label>
                        
                    </div> 
                    <!-- cierra modal -->
                
                    
                </div>
                <?php endif; ?>
            </div>


                
                </div>
            </div>
            </form>
        </div>
        
       

        

            
        <div id="modal_confirmation">
            <div x-data="{ modalConfirm: false }" class="flex justify-center">

                <span x-on:click="modalConfirm = false" id="confirmation_button" >
                    
                </span>
            
                <!-- Modal -->
                <div
                    x-show="modalConfirm"
                    style="display: none"
                    x-on:keydown.escape.prevent.stop="modalConfirm = false"
                    role="dialog"
                    aria-modal="true"
                    x-id="['modal-title']"
                    :aria-labelledby="$id('modal-title')"
                    class="fixed inset-0 z-10 overflow-y-auto"
                >
                    <!-- Overlay -->
                    <div x-show="modalConfirm" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-50"></div>
            
                    <!-- Panel -->
                    <div
                        x-show="modalConfirm" x-transition
                        x-on:click="modalConfirm = false"
                        class="relative flex min-h-screen items-center justify-center p-4"
                    >
                        <div
                            x-on:click.stop
                            x-trap.noscroll.inert="modalConfirm"
                            class="relative w-full max-w-2xl overflow-y-auto rounded-xl bg-white p-12 shadow-lg"
                        >
                            <!-- Title -->
                            <h2 class="text-3xl font-bold" id="modal_confirmation_h2">Uploading file</h2>
                            <br />
                            <!-- Content -->
                            <p class="mt-2 text-gray-600" id="loading_image"> 
                                <img src="https://vacationintervalsmanagement.com/wp-content/plugins/vima-service-layer-plugin-cliente/assets/loader/loader_mini_negro.gif" alt="loader.gif">
                            </p>
                            <p class="mt-2 text-gray-600" id="success_confirmation" style="display:none">The reservation confirmation image has been sent successfully. <br /><br />

                            Thank you for responding to this rental request. We will keep you updated about the process of the rental through the app, and you can check at any time the status in the  "Rental Requests Responded" section in your member's area Dashboard in the VIMA website.</p>
            
                             
                            <!-- Buttons -->
                            <div class="mt-8 flex space-x-2">
                                <button type="button" id="button_backto" style="display:none" x-on:click="modalConfirm = false; window.location.href = 'https://www.vacationintervalsmanagement.com/member-area/'" class="rounded-md border border-gray-200 bg-white px-5 py-2.5">
                                    Back Members Area
                                </button>
            
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- cierra modal confirmation -->

            </div>
            <!--   cierra div d entorno global - cierra todo -->
           
       

    <?php

});


add_shortcode('my-marketing-agreement', function(){

    $user = wp_get_current_user();

    $postAgreements = array(  'post_type'      => 'agreement', 
    'post_status'    => 'publish', 
    'orderby'        => 'post_date',
    'order'          => 'DESC', 
    'post_author' => $user->ID,   
    'posts_per_page' => 1 );  

    $postAgreementResult = get_posts($postAgreements);
   // echo "<pre>". print_r($postAgreementResult, true). "</pre>";


        echo "#".$postAgreementResult[0]->ID;
});

add_shortcode('my-marketing-agreement-list', function(){

  
    $user = wp_get_current_user();

    $postAgreements = array(  'post_type'      => 'agreement', 
    'post_status'    => 'publish', 
    'orderby'        => 'post_date',
    'order'          => 'DESC', 
    'author' => $user->ID,   
    'posts_per_page' => 1 );  

    $postAgreementResult = get_posts($postAgreements);
   // echo "<pre>". print_r($postAgreementResult, true). "</pre>";
   // echo "<pre>". print_r($user, true). "</pre>";

    if(count($postAgreementResult) < 1){
        echo "";
        
    }else{
        
     echo '<div style="width:100%; text-align:center;"><div class="elementor-element elementor-element-3210d9c elementor-widget elementor-widget-jet-posts" data-id="3210d9c" data-element_type="widget" data-settings="{&quot;columns&quot;:&quot;1&quot;}" data-widget_type="jet-posts.default">
     <div class="elementor-widget-container">
    <div class="elementor-jet-posts jet-elements">
    <div class="jet-posts col-row disable-rows-gap">
    <div class="jet-posts__item">
    <div class="jet-posts__inner-box"><div class="jet-posts__inner-content"><h4 class="entry-title">
    <a href="'.$postAgreementResult[0]->guid.'" target="">Agreement #'.$postAgreementResult[0]->ID.'</a></h4>
    <div class="post-meta">
    <span class="posted-by post-meta__item">Posted by '.$user->display_name.'</span>
    <span class="post__date post-meta__item">'.$postAgreementResult[0]->post_date.'</a>
    </span></div>
    <div class="jet-more-wrap"><a style="max-width: 300px; display:block; margin:auto; background-color: #666; border-color: #666;" href="'.$postAgreementResult[0]->guid.'" class="btn btn-primary elementor-button elementor-size-md jet-more" target="_new"><span class="btn__text">View My Marketing Agreement </span><span class="jet-elements-icon jet-more-icon"><i aria-hidden="true" class="fas fa-file-contract"></i></span></a></div></div></div>
    </div>
    </div>
    </div>		</div>
     </div></div>';
    }

        //echo "#".$postAgreementResult[0]->ID;
});




//para la seccion de mis mensajes 


add_shortcode( 'my-messages-section', function(){
    $currentUser = wp_get_current_user();
    $user_id = $currentUser->ID;
    $user_email =  $currentUser->user_email;
    //var_dump($user_email);

    /*
     
    */

    

    
    ?>
<style typep="text/css">
    .mensajes_nuevos{
        position: relative;
        top: 0px;
        left: -47px;
        vertical-align: text-top;
        font-size: x-small;
        font-family: monospace;
        background-color: #DEBB27;
        color: black !important;
        /* padding: 7px; */
        border-radius: 11px;
        padding-left: 7px;
        padding-right: 7px;
        padding-bottom: 4px;
        padding-top: 3px;
    }
</style>
<div class="snap-center	 justify-center w-full bg-black" style="margin:auto;">
            <?php

                $vimaServiceLayer = new VIMAServiceLayerClass();
                $msjs = $vimaServiceLayer->getNoRRRequestAdminMessages($user_email);
                $mensajes = json_decode($msjs, true);
                //echo "<pre>".print_r($mensajes['data']['messages'], true). "</pre>";
            
                if(sizeof($mensajes['data']['messages']) > 0){      
                    

                    function guardar_cantidad_mensajes_previa($user_id, $cantidad_mensajes_previa) {
                        update_user_meta($user_id, 'cantidad_mensajes_previa', $cantidad_mensajes_previa);
                    }

                  
                    function obtener_cantidad_mensajes_previa($user_id) {
                        return get_user_meta($user_id, 'cantidad_mensajes_previa', true);
                    }

                   
                    function obtener_cantidad_mensajes_nuevos($mensajes_actuales, $user_id) {
                        $cantidad_mensajes_previa = obtener_cantidad_mensajes_previa($user_id);
                        
                       // var_dump("Cantidad de mensajes previos: " . $cantidad_mensajes_previa);
                      //  var_dump("Cantidad de mensajes actuales: " . $mensajes_actuales);

                        if($cantidad_mensajes_previa !== false && $mensajes_actuales > $cantidad_mensajes_previa) {
                           
                            $cantidad_mensajes_nuevos = $mensajes_actuales - $cantidad_mensajes_previa;
                            return $cantidad_mensajes_nuevos;
                        } else {
                            // No hay nuevos mensajes
                            return 0;
                        }
                    }

                    
                    $cantidad_mensajes_actuales = sizeof($mensajes['data']['messages']);

                    
                   // var_dump("ID de usuario actual: " . $user_id);

                    $cantidad_mensajes_nuevos = obtener_cantidad_mensajes_nuevos($cantidad_mensajes_actuales, $user_id);

                  //  echo "Cantidad de mensajes nuevos: " . $cantidad_mensajes_nuevos;

                    guardar_cantidad_mensajes_previa($user_id, $cantidad_mensajes_actuales);
                   
            ?>
            


            <div x-data="{ active: 1 }" class="mx-auto max-w-3xl w-full">
                <div x-data="{
                    id: 1,
                    get expanded() {
                        return this.active === this.id
                    },
                    set expanded(value) {
                        this.active = value ? this.id : null
                    },
                }" role="region" class="rounded-lg bg-white shadow">
                    <h2 class="bg-black text-white">
                        <button
                            x-on:click="expanded = !expanded"
                            :aria-expanded="expanded"
                            class="flex w-full items-center justify-between px-6 py-4 text-sm font-bold "
                           
                        >
                       

                       

                        <span class="jet-elements-icon jet-more-icon" style="font-size:x-large;"><i aria-hidden="true" class="fas fa-envelope"></i>
                            <span style="position: relative;
                                        top: -2px;
                                        left: -2px;
                                        vertical-align: text-top;
                                        font-size: small;
                                        font-family: monospace;">
                            <?php echo sizeof($mensajes['data']['messages']); ?></span>
                            <?php

                               echo $cantidad_mensajes_nuevos > 0 ? 
                               '<span class="mensajes_nuevos">'.$cantidad_mensajes_nuevos.'</span>':
                               '';
                               


                            ?>
                            
                        </span>
                        
                        
                            <span x-show="expanded" aria-hidden="true" class="ml-4">&minus;</span>
                            <span x-show="!expanded" aria-hidden="true" class="ml-4">&plus;</span>
                        </button>
                    </h2>

                    <div x-show="expanded" x-collapse class="bg-black text-white text-sm">
                        <div class="px-6 pb-4">


                        <?php 
                                $numeroDeMensaje = 0;
                                foreach($mensajes['data']['messages'] as $msj){
                                   //echo "<pre>".print_r($msj, true). "</pre>";
                                   
                                   $cantidadDeNuevos = $cantidad_mensajes_nuevos;
                                   //$esNuevo = "color: white;";

                                   if($numeroDeMensaje < $cantidadDeNuevos){
                                    $esNuevo = "background-color: #DEBB27; color: black; ";
                                   }else{
                                    $esNuevo = "";
                                   }

                                  // var_dump($numeroDeMensaje);

                                    $numeroDeMensaje++;
                                //$esNuevo = "color: white;";

                                   ?>
                                    <div class="mensaje_admin text-justify px-6 py-4" style="font-size:small; border-bottom: 1px solid gray; <?php echo $esNuevo; ?>">
                                        <span class="at"><?php echo $msj['created_at']; ?></span>
                                        <br />
                                        <span class="msj_text"><?php echo $msj['text']; ?></span>
                                    </div>
                            <?php
                                }
                            ?>

                        </div>
                    </div>
                </div>

                
            </div>
            <?php }?>
        </div>

<?php

});


/*para incluir alpine */
// Wordpress Enqueue AlpineJS form CDN with IE11 support
// <script type="module" src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js"></script>
// <script nomodule src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine-ie11.min.js" defer></script>
function advertis_enqueue_scripts()
{
    $templatesInyectados = array(13233, 13754, 1428, 19605, 19241);
    if(in_array(get_the_ID(), $templatesInyectados)){
        //echo "<pre>".print_r(array("request" => $_REQUEST, "get" => $_GET, "post" => $_POST, "wp_post" => get_the_ID() ), true). "</pre>";
        // 'tailwind-js'
        wp_register_script('tailwind-css', 'https://vacationintervalsmanagement.com/wp-content/plugins/vima-service-layer-plugin-cliente/assets/js/tailwind.js', null, null, true);
        wp_script_add_data('tailwind-css', 'defer', true);
        wp_enqueue_script('tailwind-css');

        // 'alpine-focus'
        wp_register_script('alpine-focus', 'https://vacationintervalsmanagement.com/wp-content/plugins/vima-service-layer-plugin-cliente/assets/js/alpine_focus.js', null, null, true);
        wp_script_add_data('alpine-focus', 'defer', true);
        wp_enqueue_script('alpine-focus');
            //  'colapse.js'
        wp_register_script('AlpineJS-collapse-module', 'https://vacationintervalsmanagement.com/wp-content/plugins/vima-service-layer-plugin-cliente/assets/js/collapse.js',null, null, true);
        wp_script_add_data('AlpineJS-collapse-module', 'defer', true);
        wp_enqueue_script('AlpineJS-collapse-module');

        // 'alpine.js'
        wp_register_script('AlpineJS-module', 'https://vacationintervalsmanagement.com/wp-content/plugins/vima-service-layer-plugin-cliente/assets/js/alpine.js', null, null, true);
        wp_script_add_data('AlpineJS-module', 'defer', true);
        wp_enqueue_script('AlpineJS-module');

        
    }




    
}
add_action('wp_enqueue_scripts', 'advertis_enqueue_scripts');

// add_filter('script_loader_tag', 'advertis_defer_script', 10, 2);
// function advertis_defer_script($tag, $handle)
// {
//     if ($handle === 'AlpineJS-module') {
//         $tag = str_replace("type='text/javascript'", "type='module'", $tag);
//     }
//     if ($handle === 'AlpineJS-nomodule') {
//         $tag = str_replace("type='text/javascript'", "nomodule", $tag);
//         $tag = str_replace("type='text/javascript'", "", $tag);
//         $tag = str_replace("alpine.min.js'", "alpine.min.js' defer", $tag);
//     }
//     return $tag;
// }
?>