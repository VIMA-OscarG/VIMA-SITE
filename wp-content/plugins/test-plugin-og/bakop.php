<?php


 
function crearListaUnidadesEnRenta($unidadesEnRenta){

   
    foreach($unidadesEnRenta as $unidad):

    endforeach;

}




    

        //add_action( 'xx_reserva12', 'notifacarRentalExpirationsDate1' ); 
function recolectarDataReservasUnidad(): array{
    

      
    
    //notifacarRentalExpirationDate1();
    
   // wp_schedule__event( time(), '10secs', 'notificacionEmailReserva' );
    
    $arregloUnitsForRent = array();
    $args = array(
        'numberposts' => -1,  
        'post_type'   => 'rental-submissions',
        'order'       => 'ASC',
        'orderby'     => 'title'
      );
      $rentalSubmits = get_posts( $args );
 
      $cont = 0;
      $folioConsecutivoInicial = 315;
      

      foreach($rentalSubmits as $rentalSubmit):
        //var_dump(get_post_meta($rentalSubmit->ID, 'nombre'));
        //var_dump($rentalSubmit);
        
       $metaUnitForRent = get_post_meta($rentalSubmit->ID);
       $id = str_replace(" ", "", $rentalSubmit->post_title) ;
       $idTempArray = array();
       preg_match('/#([0-9]{4})/', $id, $idTempArray);
       
      

       

       $unit = $metaUnitForRent['unit-for-rent'][0];
       

        $arregloUnitsForRent[$unit][$cont] = array();
        $folioConsecutivo = $idTempArray[1];
       // echo "<pre>".print_r($metaUnitForRent, true)."</pre>";


        switch($idTempArray):

            case $folioConsecutivo <= 315:
                array_push($metaUnitForRent, $idTempArray[1]);
                break;
            case $folioConsecutivo > 315:
                $folioConsecutivoInicial ++;
               // echo $folioConsecutivoInicial;
                $folioConsecutivoInicial = strval("0".$folioConsecutivoInicial);
                array_push($metaUnitForRent, $folioConsecutivoInicial);
                break;

        endswitch;

        array_push($arregloUnitsForRent[$unit][$cont],array($unit => $metaUnitForRent) );

        
       
        $cont ++;
        
      endforeach;
     // $arregloUnitsForRent =  array_reverse($arregloUnitsForRent, true);
     
     //echo "<pre>".print_r($arregloUnitsForRent, true). "</pre>";
  
    return $arregloUnitsForRent;

      
      
      
}

function getPrivileges($arrPrivilegesRaw){


    $privilegesArray = array();
        preg_match_all('(\"[-\w\s]+\")', $arrPrivilegesRaw, $privilegesArray);

    $salida = "";
    foreach($privilegesArray as $priv):
       foreach($priv as $pv):
        $salida .=  str_replace('"', '', $pv)."<br />";
       endforeach;
    endforeach;
    return $salida;

}

function ordinal($number) {
    $ends = array('th','st','nd','rd','th','th','th','th','th','th');
    if ((($number % 100) >= 11) && (($number%100) <= 13))
        return $number. 'th';
    else
        return $number. $ends[$number % 10];
}


function crearFilasOutput($unidad, $dateOfStart){
    $placeReserva = "1st ";
    $salida = "";
    
          // echo "<pre>".print_r($dateOfStart, true)."</pre>";
    $repetidas = 0;
    foreach ($unidad as $key => $una):
        
        $reps = count(array_keys($dateOfStart, $una['date-of-start'][0]));
        if ($reps > 1) 
        {
            //echo "FECHA INCIO DUPLICADA";
            $repetidas += $reps;
               // var_dump(ordinal($repetidas));
        }
        else{
            $repetidas = 1;
        }
        $privileges = getPrivileges($una['privileges'][0]);
        //var_dump($privileges);

        $salida .=' <tr class="jet-table__body-row elementor-repeater-item-ead2d50 repetidas_'.$repetidas.'">
        <td class="jet-table__cell elementor-repeater-item-8aa31bf jet-table__body-cell"
        style="
                font-family: Didact Gothic, sans-serif;
                font-size: 15px !important;
                text-align:center;
               
                line-height:25.5px;
                border-bottom: thin solid rgb(215,215,215);
                ">
            <div class="jet-table__cell-inner" style="border: thin rgb(215,215,215)" ><div class="jet-table__cell-content"
            style="
                    width:100%;
                    display:block;
                    text-align:center;
                    ">
            <div class="jet-table__cell-text">'.$privileges.'</div></div></div>
        </td>
        <td class="jet-table__cell elementor-repeater-item-891de7d jet-table__body-cell"
        style="
                font-family: Didact Gothic, sans-serif;
                font-size: 15px !important;
                text-align:center;
               
                line-height:25.5px;
                border-bottom: thin solid rgb(215,215,215);
                border-left: thin solid rgb(215,215,215);
                ">
            <div class="jet-table__cell-inner"><div class="jet-table__cell-content"
            style="
                    width:100%;
                    display:block;
                    text-align:center;
                    ">
            <div class="jet-table__cell-text">'.date("M/d",$una['date-of-start'][0]).' - '.date("M/d/Y",$una['date-of-end'][0]).'</div></div></div>
        </td>
        <td class="jet-table__cell elementor-repeater-item-79ca0ef jet-table__body-cell" 
        style="
                font-family: Didact Gothic, sans-serif;
                font-size: 15px !important;
                text-align:center;
                border-bottom: thin solid rgb(215,215,215);
                border-left: thin solid rgb(215,215,215);
                line-height:25.5px;
                ">
            <div class="jet-table__cell-inner"><div class="jet-table__cell-content"
            style="
                    width:100%;
                    display:block;
                    text-align:center;
                    ">
            <div class="jet-table__cell-text">'.obtenerUnitType($key).'</div></div></div>
        </td>
        <td class="jet-table__cell elementor-repeater-item-cdba2e7 jet-table__body-cell" 
        style="
                font-family: Didact Gothic, sans-serif;
                font-size: 15px !important;
                text-align:center;
                border-left: thin solid rgb(215,215,215);
                border-bottom: thin solid rgb(215,215,215);
                border-right: thin solid rgb(215,215,215);
                line-height:25.5px;
                ">
            <div class="jet-table__cell-inner"><div class="jet-table__cell-content"
            style="
                    width:100%;
                    display:block;
                    text-align:center;
                    ">
            <div class="jet-table__cell-text"><span>'.ordinal($repetidas).' </span>'.$una['initials'][0].'<br /><strong>'.$una[0].'</strong></div></div></div>
        </td>
    </tr>';
   

    endforeach;
    
    return $salida;
}


function obtenerUnitType($unidad){
    $unidad = str_replace(".","",$unidad);
    //var_dump($unidad);
    $arrayUnidadesUnitTypes = array();

    $arrayUnidadesUnitTypes['Luxe 4 Bedroom Residence Nuevo Vallarta'] = "Luxe 4BR Res <br />NV ";
    $arrayUnidadesUnitTypes['Luxe 3 Bedroom Loft Nuevo Vallarta'] = "Luxe 2BR Villa <br />RM";
    $arrayUnidadesUnitTypes['Luxe 2 Bedroom Loft Nuevo Vallarta'] = "Luxe 2BR Loft <br />NV";
    $arrayUnidadesUnitTypes['Luxe 3 Bedroom Spa Suite Nuevo Vallarta'] = "Luxe 3BR Spa <br />NV";
    $arrayUnidadesUnitTypes['Luxe 2 Bedroom Spa Suite Nuevo Vallarta'] = "Luxe 2BR Villa <br />RM";
    $arrayUnidadesUnitTypes['Luxe 2 Bedroom Villa Nuevo Vallarta'] = "Luxe 2BR Villa <br />NV";
    $arrayUnidadesUnitTypes['Luxe 1 Bedroom Villa Nuevo Vallarta'] = "Luxe 1BR Villa <br />NV";
    $arrayUnidadesUnitTypes['Luxe One Bedroom Villa Nuevo Vallarta'] = "Luxe 1BR Villa <br />NV";
    $arrayUnidadesUnitTypes['Luxe Junior Villa Nuevo Vallarta'] = "Luxe Junior Villa <br />NV";
    $arrayUnidadesUnitTypes['Luxe 2 Bedroom Suite Nuevo Vallarta'] = "Luxe 2BR Suite <br/ >NV";
    $arrayUnidadesUnitTypes['Luxe 1 Bedroom Suite Nuevo Vallarta'] = "Luxe 1BR Suite <br />NV";
    $arrayUnidadesUnitTypes['Luxe One Bedroom Suite Nuevo Vallarta'] = "Luxe 1BR Suite <br />NV";
    $arrayUnidadesUnitTypes['Luxe Studio Nuevo Vallarta'] = "Luxe Studio <br />NV";
    $arrayUnidadesUnitTypes['G Mayan 2 Bedroom Suite Nuevo Vallarta'] = "GM 2BR Suite <br />NV";
    $arrayUnidadesUnitTypes['G Mayan 1 Bedroom Suite Nuevo Vallarta'] = "GM 1BR Suite <br />NV";
    $arrayUnidadesUnitTypes['G Mayan One Bedroom Suite Nuevo Vallarta'] = "GM 1BR Suite <br />NV";
    $arrayUnidadesUnitTypes['G Mayan Studio Nuevo Vallarta'] = "GM Studio <br />NV";
    $arrayUnidadesUnitTypes['Luxe 4 Bedroom Residence Riviera Maya'] = "Luxe 4BR Res <br />RM";
    $arrayUnidadesUnitTypes['Luxe 3 Bedroom Loft Riviera Maya'] = "Luxe 3BR Loft <br />RM";
    $arrayUnidadesUnitTypes['Luxe 2 Bedroom Villa Riviera Maya'] = "Luxe 2BR Villa <br /> RM";
    $arrayUnidadesUnitTypes['Luxe 1 Bedroom Villa Riviera Maya'] = "Luxe 1BR Villa <br />RM";
    $arrayUnidadesUnitTypes['Luxe Junior Villa Riviera Maya'] = "Luxe Junior Villa <br />RM";
    $arrayUnidadesUnitTypes['Luxe 2 Bedroom Suite Riviera Maya'] = "Luxe 2BR Suite <br />RM";
    $arrayUnidadesUnitTypes['Luxe 1 Bedroom Suite Riviera Maya'] = "Luxe 1BR Suite <br />RM";
    $arrayUnidadesUnitTypes['Luxe One Bedroom Suite Riviera Maya'] = "Luxe 1BR Suite <br />RM";
    $arrayUnidadesUnitTypes['Luxe Studio Riviera Maya'] = "Luxe Studio <br />RM";
    $arrayUnidadesUnitTypes['G Mayan 2 Bedroom Suite Riviera Maya'] = "GM 2BR Suite <br />RM";
    $arrayUnidadesUnitTypes['G Mayan 1 Bedroom Suite Riviera Maya'] = "GM 1BR Suite <br />RM";
    $arrayUnidadesUnitTypes['G Mayan Studio Riviera Maya'] = "GM Studio <br />RM";
    $arrayUnidadesUnitTypes['G Mayan 2 Bedroom Penthouse Los Cabos'] = "GM 2BR PH <br />Los Cabos";
    $arrayUnidadesUnitTypes['G Mayan 2 Bedroom Suite Los Cabos'] = "GM 2BR PH Los <br />Cabos";
    $arrayUnidadesUnitTypes['G Mayan 1 Bedroom Penthouse Los Cabos'] = "GM 1BR PH <br />Los Cabos";
    $arrayUnidadesUnitTypes['G Mayan One Bedroom Suite Los Cabos'] = "GM 1BR PH <br />Los Cabos";
    $arrayUnidadesUnitTypes['G Mayan Studio Penthouse Los Cabos'] = "GM Studio PH <br />Los Cabos";
    $arrayUnidadesUnitTypes['G Mayan 2 Bedroom Suite Los Cabos'] = "GM 2BR Suite <br />Los Cabos";
    $arrayUnidadesUnitTypes['G Mayan 1 Bedroom Suite Los Cabos'] = "GM 1BR Suite <br />Los Cabos";
    $arrayUnidadesUnitTypes['G Mayan Studio Los Cabos'] = "GM Studio PH <br /> Los Cabos";

    

    return $arrayUnidadesUnitTypes[$unidad];


    
    

}

function crearSalidaUnidadReservas($unidadEnRentaName, $unidadEnRentaArray, $turno){
        //$firstKey = array_key_first($unidadEnRentaArray);
       
        $placeReserva = "1st ";

       $filasOutput = "";
        $fila = "";
       
        $fechasEncimadas = array();
      /*  usort($unidadEnRentaArray, function($a, $b) {
            // var_dump($keyUnit);
             echo "<pre>".print_r($a[array_key_first($a)][array_key_first($a[array_key_first($a)])]['date-of-start'])."</pre>";
            // var_dump($b['date-of-start']);
             return intval($a[array_key_first($a)][array_key_first($a[array_key_first($a)])]['date-of-start']) - intval($b[array_key_first($b)][array_key_first($b[array_key_first($b)])]['date-of-start']);
         });*/
        
        foreach( $unidadEnRentaArray as $key => $unidadRenta):
            
                 //  echo "<pre>".print_r( $$unidadRenta[0][$unidadEnRentaName]['date-of-start'][0], true)."</pre>";
                 
                 $fechasEncimadas[$key] =  $unidadRenta[0][$unidadEnRentaName]['date-of-start'][0];

            if(sizeof($unidadEnRentaArray) > 1){
                foreach($unidadRenta as  $unidad):
                   // var_dump($key);
                
                
                    $fila = crearFilasOutput($unidad, $fechasEncimadas);
                    $filasOutput .= $fila;
                    
                endforeach;
                
            }else{
                $privi = getPrivileges($unidadRenta[0][$unidadEnRentaName]['privileges'][0]);
                $fila = ' <tr class="jet-table__body-row elementor-repeater-item-ead2d50">
                <td class="jet-table__cell elementor-repeater-item-8aa31bf jet-table__body-cell"
                style="
                font-family: Didact Gothic, sans-serif;
                font-size: 15px !important;
                text-align:center;
                border: thin solid rgb(215,215,215) !important;
                border-left:none !important;
                line-height:25.5px;
                ">
                    <div class="jet-table__cell-inner"  >
                    <div class="jet-table__cell-content" 
                    style="
                    width:100%;
                    display:block;
                    text-align:center;
                    ">
                    <div class="jet-table__cell-text">'.$privi.'</div></div></div>
                </td>
                <td class="jet-table__cell elementor-repeater-item-891de7d jet-table__body-cell"
                style="
                font-family: Didact Gothic, sans-serif;
                font-size: 15px !important;
                text-align:center;
                border: thin solid rgb(215,215,215) !important;
                border-left:none !important;
                line-height:25.5px;
                ">
                    <div class="jet-table__cell-inner"  ><div class="jet-table__cell-content"
                    style="
                    width:100%;
                    display:block;
                    text-align:center;
                    ">
                    <div class="jet-table__cell-text">'.date("M/d",$unidadRenta[0][$unidadEnRentaName]['date-of-start'][0]).' - '.date("M/d/Y",$unidadRenta[0][$unidadEnRentaName]['date-of-end'][0]).'</div></div></div>
                </td>
                <td class="jet-table__cell elementor-repeater-item-79ca0ef jet-table__body-cell"
                style="
                font-family: Didact Gothic, sans-serif;
                font-size: 15px !important;
                text-align:center;
                border: thin solid rgb(215,215,215) !important;
                border-left:none !important;
                line-height:25.5px;
                ">
                    <div class="jet-table__cell-inner"   ><div class="jet-table__cell-content"
                    style="
                    width:100%;
                    display:block;
                    text-align:center;
                    ">
                    <div class="jet-table__cell-text">'.obtenerUnitType($unidadEnRentaName).'</div></div></div>
                </td>
                <td class="jet-table__cell elementor-repeater-item-cdba2e7 jet-table__body-cell"
                style="
                font-family: Didact Gothic, sans-serif;
                font-size: 15px !important;
                text-align:center;
                border: thin solid rgb(215,215,215) !important;
                border-left:none !important;
                line-height:25.5px;
                ">
                    <div class="jet-table__cell-inner"   ><div class="jet-table__cell-content"
                    style="
                    width:100%;
                    display:block;
                    text-align:center;
                    ">
                    <div class="jet-table__cell-text"><span>'.$placeReserva.'</span>'.$unidadRenta[0][$unidadEnRentaName]['initials'][0].'<br /><strong>'.$unidadRenta[0][$unidadEnRentaName][0].'</strong></div></div></div>
                </td>
            </tr>';
            $filasOutput .= $fila;
            //echo "<pre>".print_r($unidadRenta[0][$unidadEnRentaName], true)."</pre>";
            }
           // echo "<pre>".sizeof($unidadRenta)."</pre>";
            //$filasOutput = "";

                

            
            
            
            
            
        endforeach;
       
    
         // var_dump($unidadEnRentaName);
           

       $htmlOutput = '
   
       <div class="jet-accordion__item jet-toggle jet-toggle-move-up-effect ">
            <div id="jet-toggle-control-2181'.$turno.'" class="jet-toggle__control elementor-menu-anchor" data-toggle="'.$unidadEnRentaArray[0]['confirmation-of-reservation'][0].'" role="tab" aria-controls="jet-toggle-content-2181'.$turno.'" aria-expanded="false" data-template-id="'.$unidadEnRentaArray[0]['confirmation-of-reservation'][0].'">
                <div class="jet-toggle__label-text">'.$unidadEnRentaName.'</div>
            </div>
                       
            <div id="jet-toggle-content-2181'.$turno.'" class="jet-toggle__content" data-toggle="'.$unidadEnRentaArray[0]['confirmation-of-reservation'][0].'" role="tabpanel" aria-hidden="true" data-template-id="'.$unidadEnRentaArray[0]['confirmation-of-reservation'][0].'">
                <div class="jet-toggle__content-inner">		
                    <div data-elementor-type="page" data-elementor-id="2714" class="elementor elementor-2714" data-elementor-settings="[]">
                        <div class="elementor-section-wrap">
                            <section class="elementor-section elementor-top-section elementor-element elementor-element-b3294fd elementor-section-full_width elementor-section-height-default elementor-section-height-default jet-parallax-section" data-id="b3294fd" data-element_type="section" data-settings="{"jet_parallax_layout_list":[{"jet_parallax_layout_image":{"url":","id":"},"_id":"a8a3a0b","jet_parallax_layout_image_tablet":{"url":","id":"},"jet_parallax_layout_image_mobile":{"url":","id":"},"jet_parallax_layout_speed":{"unit":"%","size":50,"sizes":[]},"jet_parallax_layout_type":"scroll","jet_parallax_layout_direction":null,"jet_parallax_layout_fx_direction":null,"jet_parallax_layout_z_index":","jet_parallax_layout_bg_x":50,"jet_parallax_layout_bg_x_tablet":","jet_parallax_layout_bg_x_mobile":","jet_parallax_layout_bg_y":50,"jet_parallax_layout_bg_y_tablet":","jet_parallax_layout_bg_y_mobile":","jet_parallax_layout_bg_size":"auto","jet_parallax_layout_bg_size_tablet":","jet_parallax_layout_bg_size_mobile":","jet_parallax_layout_animation_prop":"transform","jet_parallax_layout_on":["desktop","tablet"]}]}"><div class="jet-parallax-section__layout elementor-repeater-item-a8a3a0b jet-parallax-section__scroll-layout"><div class="jet-parallax-section__image" style="background-position: 50% 50%; background-image: url("); transform: translateY(133.4px);"></div></div>
                                <div class="elementor-container elementor-column-gap-no">
                                    <div class="elementor-column elementor-col-100 elementor-top-column elementor-element elementor-element-92d041b" data-id="92d041b" data-element_type="column">
                                        <div class="elementor-widget-wrap elementor-element-populated">
                                            <div class="elementor-element elementor-element-eec5cd9 elementor-widget elementor-widget-jet-table" data-id="eec5cd9" data-element_type="widget" data-widget_type="jet-table.default">
                                                <div class="elementor-widget-container">
                                                    <div class="elementor-jet-table jet-elements">
                                                        <div class="jet-table-wrapper jet-table-responsive-mobile jet-table-responsive-tablet"
                                                        style="
                                                            max-width: 90%;
                                                            margin-left: auto;
                                                            margin-right: auto;
                                                            box-shadow: 0px 0px 4px 0px rgb(98 87 87 / 50%);

                                                        ">
                                                            <table class="jet-table jet-table--fa5-compat">
                                                                <thead class="jet-table__head" style="
                                                                border-bottom-color : rgb(215, 215, 215);
                                                                border-collapse : separate;
                                                                border-left-color : rgb(215, 215, 215);
                                                                border-right-color : rgb(215, 215, 215);
                                                                border-top-color : rgb(215, 215, 215);
                                                                border-width:thin;
                                                                ">
                                                                    <tr class="jet-table__head-row">
                                                                        <th class="jet-table__cell elementor-repeater-item-0890e2d jet-table__head-cell" scope="col"
                                                                        style="background-color: #000 !important;
                                                                        font-family: Cormorant Garamond, sans-serif;
                                                                        font-size:18px;
                                                                        text-align:center : !important;
                                                                        border-top-left-radius: 5px;
                                                                        background-color : rgb(0, 0, 0);
                                                                        border: thin solid rgb(215,215,215) !important;
                                                                        ">
                                                                            <div class="jet-table__cell-inner"><div class="jet-table__cell-content" style="width:100%; display:block; text-align:center;"><div class="jet-table__cell-text">Privileges</div></div></div>
                                                                        </th>
                                                                            <th class="jet-table__cell elementor-repeater-item-069bfb6 jet-table__head-cell" scope="col" 
                                                                            style="background-color: #000 !important;
                                                                        font-family: Cormorant Garamond, sans-serif;
                                                                        font-size:18px;
                                                                        text-align:center !important;
                                                                        border: thin solid rgb(215,215,215) !important;
                                                                        
                                                                        ">
                                                                            <div class="jet-table__cell-inner"><div class="jet-table__cell-content" style="width:100%; display:block; text-align:center;"><div class="jet-table__cell-text">Dates</div></div></div>
                                                                        </th>
                                                                            <th class="jet-table__cell elementor-repeater-item-fa3f41e jet-table__head-cell" scope="col"
                                                                            style="background-color: #000 !important;
                                                                            font-family: Cormorant Garamond, sans-serif;
                                                                            font-size:18px;
                                                                            text-align:center !important;
                                                                            border: thin solid rgb(215,215,215) !important;
                                                                            
                                                                            "><div class="jet-table__cell-inner"><div class="jet-table__cell-content" style="width:100%; display:block; text-align:center;"><div class="jet-table__cell-text">Unit Type</div></div></div>
                                                                        </th>
                                                                            <th class="jet-table__cell elementor-repeater-item-0c491f0 jet-table__head-cell" scope="col"
                                                                            style="background-color: #000 !important;
                                                                            font-family: Cormorant Garamond, sans-serif;
                                                                            font-size:18px;
                                                                            text-align:center !important;
                                                                            border-top-right-radius: 5px;
                                                                            border: thin solid rgb(215,215,215) !important;
                                                                            "><div class="jet-table__cell-inner"><div class="jet-table__cell-content" style="width:100%; display:block; text-align:center;"><div class="jet-table__cell-text">Initials</div></div></div>
                                                                        </th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="jet-table__body">
                                                                '.$filasOutput.'
                                                                </tbody>
                                                            </table>
                                                        </div>

                                                    </div>		
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </div>
               
                                                  
                                                  ';
        return  $htmlOutput;
}

function calendarioResevarHook($data = null){

    $data = recolectarDataReservasUnidad();

    //echo "<pre>".print_r($data, true)."</pre>";

    add_shortcode('calendario_reservas_og', function() use ($data) {

        echo '<div class="elementor-element elementor-element-d0684f7 elementor-widget accordion_reservas_og elementor-widget-jet-accordion" data-id="d0684f7" data-element_type="widget" data-widget_type="jet-accordion.default">
        <div class="elementor-widget-container">
        <div class="jet-accordion" data-settings="{"collapsible":false,"ajaxTemplate":false,"switchScrolling":false}" role="tablist">
        <div class="jet-accordion__inner">';
          $turno = 0; 
        foreach($data as $keyUnit => $rentUnit):

            
           // $rentUnit = array_reverse($rentUnit, true);
            //echo "<pre>".print_r($rentUnit[array_key_first($rentUnit)][0], true)."</pre>";
            $forRentUnits =  crearSalidaUnidadReservas($keyUnit, $rentUnit, $turno);
            echo $forRentUnits;
            $turno ++;
        endforeach;

        echo '
        </div>
        </div>
        </div>
        </div>';
   
     
     
     });
}




function cargarReservas(){
    $url = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $end = array_slice(explode('/', $url), -2)[0];

    //echo "<pre>".print_r(wp_get_schedules(  ), true). "</pre>";
    
    if($end == 'availability-calendar'):

        ?>
        <style type="text/css">
            .elementor-widget-jet-accordion{
                display: none;
            }
            .accordion_reservas_og{
                display: revert;
            }
            .repetidas_2{
                background-color: #2D7BCB !important;
                color: #fff;
            }
            .repetidas_3{
                background-color: #002C59 !important;
                color: #fff;
            }
            .repetidas_4{
                background-color: #002C59 !important;
                color: #fff;
            }
            .repetidas_5{
                background-color: #002C59 !important;
                color: #fff;
            }
        </style>
        <script type="text/javascript" >
            window.onload = () =>{
                document.getElementsByClassName('elementor-widget-jet-accordion')[0].remove();
            }
            

        </script>

        <?php

        calendarioResevarHook(); 

    endif;
    

}

add_action( 'wp', 'cargarReservas' );



////////para crear vista del archivo y colocar link en el correo que recibe el admin el enviar una "rental-submission"

/*
 * Set Page templates for CPT "help_lessions"
 */



function my_plugin_templates( $template ) {
    $post_types = array( 'rental-submissions' );
   // var_dump(plugin_dir_path(__FILE__)) ;
   /// var_dump(is_singular( $post_types ));

    if ( is_singular( $post_types ) && file_exists( plugin_dir_path(__FILE__) . 'templates/post-rental-submission.php' ) ){
        $template = plugin_dir_path(__FILE__) . 'templates/post-rental-submission.php';
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




///crear schedules para envio de email


function crearRecurrenciaMailNotifReservas( $schedules ) 
{
    if(!isset($schedules['97dias']))
    {
        $schedules['97dias'] = array(
            'display' => __( 'When 97 days have passed', 'twentyfifteen' ),
            'interval' => 97 * DAY_IN_SECONDS,
        );
    }
     
    if(!isset($schedules['37dias']))
    {
        $schedules['37dias'] = array(
        'display' => __( 'When 97 days have passed', 'twentyfifteen' ),
        'interval' => 37 * DAY_IN_SECONDS,
        );
    }

    if(!isset($schedules['60dias']))
    {
        $schedules['60dias'] = array(
        'display' => __( 'When 97 days have passed', 'twentyfifteen' ),
        'interval' => 60 * DAY_IN_SECONDS,
        );
    }

    if(!isset($schedules['10secs']))
    {
        $schedules['10secs'] = array(
        'display' => __( 'Cada 10 secs -**for testing ONLY', 'twentyfifteen' ),
        'interval' => 0.1667 * MINUTE_IN_SECONDS,
        );
    }
    if(!isset($schedules['40min']))
    {
        $schedules['40min'] = array(
        'display' => __( 'Cada 40 min - para email', 'twentyfifteen' ),
        'interval' => 40 * MINUTE_IN_SECONDS,
        );
    }
     
    return $schedules;
}
add_filter( 'cron_schedules', 'crearRecurrenciaMailNotifReservas' );


// If the task does not exist yet, it is created here:
/*if ( ! wp_next_scheduled( 'notificacionEmailReserva' ) ) {
    // you can specify what time() it should run (it is a unix timestamp)
    // in your case 'daily' would be a good choice:
    wp_schedule_event( time(), '10secs', 'notificacionEmailReserva' );
  }*/
  

  // If the task does not exist yet, it is created here:

    // you can specify what time() it should run (it is a unix timestamp)
    // in your case 'daily' would be a good choice:
    
   
if (date('Hi') > 2000 && date('Hi') < 2100) {
   // var_dump(date('Hi'));
    wp_clear_scheduled_hook('l711');
    if ( ! wp_next_scheduled( 'l711' ) ) {
        
        // you can specify what time() it should run (it is a unix timestamp)
        // in your case 'daily' would be a good choice:
        wp_schedule_single_event( time(), '10secs', 'l711');
    } 
    add_action( 'l711', 'notifacarRentalExpirationDate1' );

    
}
   
     
function wpse27856_set_content_type(){
    return "text/html";
}
add_filter( 'wp_mail_content_type','wpse27856_set_content_type' );
  

        


  function notifacarRentalExpirationDate1() 
  {

    $diasMenosNotificacionUno = 97;
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
       $fechaMas97dias = strtotime(date("Y-m-d", $fechaInicio).'-'.$diasMenosNotificacionUno.' day');
       $datediff =  strtotime(date("Y-m-d")) - $fechaMas97dias;
       $fechaHoy = strtotime(date("Y-m-d"));
       $fechaMenos97dias = $fechaMas97dias;
      /* var_dump($rentalSubmit->ID);
       var_dump(date("Y-m-d"));
       
       var_dump(date("Y-m-d", $fechaInicio));
       var_dump(date("Y-m-d",$fechaMas97dias));
       var_dump($fechaHoy);
       var_dump($fechaMenos97dias);
       
       echo "<br />";*/
         
       
        if(intval($fechaInicio) > intval($fechaMas97dias)){
            
           // var_dump($fechaHoy);
           // var_dump($fechaMenos97dias);
       
            if($fechaHoy == $fechaMenos97dias ){

                $to = 'lopar_711@hotmail.com';
                $subject = 'Reservation about to expire - 97 days left';
                $unitObjReserved = obtenerUnitType(get_post_meta($rentalSubmit->ID, 'unit-for-rent')[0]);
                $arrUnit = explode("<br />", $unitObjReserved);
                $unitReserved = $arrUnit[0];
      $body = "
      
Hi ".get_post_meta($rentalSubmit->ID, 'nombre')[0].", hope this message finds you well.<br /><br />

This is Felipe, Project Director for VIMA Vacation Intervals Management, 
the company founded to help you rent out your Grand Luxxe timeshare weeks 
through Luxury Retreats, Airbnb Luxxe, and Private Luxury Resorts.<br /><br />

This email has been sent to remind you that the following week(s) hasn't been rented yet and that the time to cancel your reservation with Vidanta is close to expiring:<br /><br />

Week(s) reserved: ".date("l M/d/Y",get_post_meta($rentalSubmit->ID, 'date-of-start')[0])." - ".date("l M/d/Y",get_post_meta($rentalSubmit->ID, 'date-of-end')[0])." <br />
Unit reserved: ".$unitReserved." <br />
Location: ".get_post_meta($rentalSubmit->ID, 'unit-for-rent')[0]."<br /><br />

Please log in to your account on our website and check the Availability Calendar tab where you'll be able to see all the units and the timeframes available.<br /><br />

<a href='https://vacationintervalsmanagement.com/login'>Click here to log in to our website</a><br /><br />

I hope this information serves you. If you have any questions, please don't hesitate to ask and we'll be glad to assist you! 
<br /><br />
Best regards
      
      ";
     // $headers = array('Content-Type: text/html; charset=UTF-8');
      /*$headers[] = 'From: VIMA Admin';
      $headers[] = 'Cc: VIMA Admin <manager@vacationintervalsmanagement.com>';*/
      

                if(wp_mail( $to, $subject, $body )){
                    continue;
                    //return;
                }
            
            }
            
        }
        
        




      endforeach;
      
      
      
     /* wp_mail( 'og.lopar711@gmail.com', 
      'Your task should be completed in a week', 
      'Hey, the deadline for your task blablabla is due in a week, get going or else!');*/
    
 
   }

   add_filter('wp_mail_from','yoursite_wp_mail_from');
    function yoursite_wp_mail_from($content_type) {
    return 'manager@vacationintervalsmanagement.com';
    }

    add_filter('wp_mail_from_name','yoursite_wp_mail_from_name');
    function yoursite_wp_mail_from_name($name) {
    return 'VIMA Vacation Intervals Management';
    }
   function notifacarRentalExpirationDate2() {
    // query which Completion Dates are in a week
    // loop through the results (if any)
    // and send every user in the result (or every task that is almost due) the email:
     // Send a mail:
     wp_mail( 'og.lopar711@gmail.com', 
      'Your task should be completed in a week', 
      'Hey, the deadline for your task blablabla is due in a week, get going or else!');

   }

?>