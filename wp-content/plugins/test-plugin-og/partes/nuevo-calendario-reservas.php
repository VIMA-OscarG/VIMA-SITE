<?php


function getVimaIdiomasInstance(){

    //$filePath = plugin_dir_path(__FILE__) . 'traducciones/availabilityCalendarTranslations.json';
    $filePath = './traducciones/availability-calendar/availabilityCalendarTranslations.json';
    $VimaIdiomasClass = new VimaIdiomasClass($filePath);


    $currentLanguage = 'en'; // Idioma actual
    $targetLanguage = 'en'; // Idioma al que deseas traducir

    if(isset($_GET['lang'])){
        if($_GET['lang'] === 'es'){
            $targetLanguage = 'es';
        }
    }

    $VimaIdiomasClass->setCurrentLanguage($currentLanguage);
    $VimaIdiomasClass->setTargetLanguage($targetLanguage);

    return $VimaIdiomasClass;
}




function mapearPorClubes($item, $index, &$clubesDisponibles){

    $vidanataUnitsArr  = ['Estates', 'Luxxe'];
    $resMapeo = array_search('Estates', $vidanataUnitsArr);
    //var_dump($index);
    var_dump($resMapeo);
    //var_dump($clubesDisponibles);
   // return $index;

   
}

function getVidantaUnits($unidadesData, $unidadesVidanta, $listaPorClubesSL){
    
    array_walk($unidadesData, function($item, $index) use (&$unidadesVidanta, &$listaPorClubesSL) {
        // echo "<pre>".print_r($unidadesVidanta, true). "</pre>";
         if(is_numeric(array_search($index, $listaPorClubesSL['Vidanta']))){
            $resMapeo = array_search($index, $listaPorClubesSL['Vidanta']);
            array_push($unidadesVidanta, $listaPorClubesSL['Vidanta'][$resMapeo]);
         }
         
     }, $listaPorClubesSL); 
     
    // echo "<pre>".print_r($unidadesVidanta, true). "</pre>";
    return $unidadesVidanta;
  
}




function getGrandSolmarUnits($unidadesData, $unidadesGrandSolmar, $listaPorClubesSL){

    array_walk($unidadesData, function($item, $index) use (&$unidadesGrandSolmar, &$listaPorClubesSL) {

       if(is_numeric(array_search($index, $listaPorClubesSL['Grand Solmar']))){
       // echo "<pre>".print_r(array_search($index, $listaPorClubesSL['Grand Solmar']), true). "</pre>";
        $resMapeo = array_search($index, $listaPorClubesSL['Grand Solmar']);
        array_push($unidadesGrandSolmar, $listaPorClubesSL['Grand Solmar'][$resMapeo]);
       }     
        
     }, $listaPorClubesSL); 

     return $unidadesGrandSolmar;
  
}

function getPalaceResortsUnits($unidadesData, $unidadesPalaceResorts, $listaPorClubesSL){

    array_walk($unidadesData, function($item, $index) use (&$unidadesPalaceResorts, &$listaPorClubesSL) {

        if(is_numeric(array_search($index, $listaPorClubesSL['Palace Resorts']))){
        // echo "<pre>".print_r(array_search($index, $listaPorClubesSL['Palace Resort']), true). "</pre>";
         $resMapeo = array_search($index, $listaPorClubesSL['Palace Resorts']);
         array_push($unidadesPalaceResorts, $listaPorClubesSL['Palace Resorts'][$resMapeo]);
        }     
         
      }, $listaPorClubesSL); 
 
      return $unidadesPalaceResorts;
}




function getHaciendaEncantadaUnits($unidadesData, $unidadesHaciendaEncantada, $listaPorClubesSL){

    array_walk($unidadesData, function($item, $index) use (&$unidadesHaciendaEncantada, &$listaPorClubesSL) {

        if(is_numeric(array_search($index, $listaPorClubesSL['Hacienda Encantada']))){
         
        // echo "<pre>".print_r(array_search($index, $listaPorClubesSL['Palace Resort']), true). "</pre>";
         $resMapeo = array_search($index, $listaPorClubesSL['Hacienda Encantada']);
         array_push($unidadesHaciendaEncantada, $listaPorClubesSL['Hacienda Encantada'][$resMapeo]);
        // echo "<pre>".print_r($listaPorClubesSL['Hacienda Encantada'][$resMapeo], true). "</pre>";
        // echo "<pre>".print_r($unidadesData, true). "</pre>";
        }     
         
      }, $listaPorClubesSL); 
 
      return $unidadesHaciendaEncantada;
}


function getCaboVillasUnits($unidadesData, $unidadesCaboVillas, $listaPorClubesSL){

    array_walk($unidadesData, function($item, $index) use (&$unidadesCaboVillas, &$listaPorClubesSL) {

        if(is_numeric(array_search($index, $listaPorClubesSL['Cabo Villas']))){
        // echo "<pre>".print_r(array_search($index, $listaPorClubesSL['Palace Resort']), true). "</pre>";
         $resMapeo = array_search($index, $listaPorClubesSL['Cabo Villas']);
         array_push($unidadesCaboVillas, $listaPorClubesSL['Cabo Villas'][$resMapeo]);
        }     
         
      }, $listaPorClubesSL); 
 
      return $unidadesCaboVillas;
}


function getVillaGroupUnits($unidadesData, $unidadesVillaGroup, $listaPorClubesSL){

    array_walk($unidadesData, function($item, $index) use (&$unidadesVillaGroup, &$listaPorClubesSL) {

        if(is_numeric(array_search($index, $listaPorClubesSL['Villa Group']))){
        // echo "<pre>".print_r(array_search($index, $listaPorClubesSL['Palace Resort']), true). "</pre>";
         $resMapeo = array_search($index, $listaPorClubesSL['Villa Group']);
         array_push($unidadesVillaGroup, $listaPorClubesSL['Villa Group'][$resMapeo]);
        }     
         
      }, $listaPorClubesSL); 
 
      return $unidadesVillaGroup;
}

function getAmatteUnits($unidadesData, $unidadesAmatte, $listaPorClubesSL){

    array_walk($unidadesData, function($item, $index) use (&$unidadesAmatte, &$listaPorClubesSL) {

        if(is_numeric(array_search($index, $listaPorClubesSL['Amatte']))){
         $resMapeo = array_search($index, $listaPorClubesSL['Amatte']);
         array_push($unidadesAmatte, $listaPorClubesSL['Amatte'][$resMapeo]);
        }     
         
      }, $listaPorClubesSL); 
 
      return $unidadesAmatte;
}

function getOccidentalXcaret($unidadesData, $unidadesOccidentalXcaret, $listaPorClubesSL){

    array_walk($unidadesData, function($item, $index) use (&$unidadesOccidentalXcaret, &$listaPorClubesSL) {

        if(is_numeric(array_search($index, $listaPorClubesSL['Occidental Xcaret']))){
         $resMapeo = array_search($index, $listaPorClubesSL['Occidental Xcaret']);
         array_push($unidadesOccidentalXcaret, $listaPorClubesSL['Occidental Xcaret'][$resMapeo]);
        }     
         
      }, $listaPorClubesSL); 
     // echo "<pre>".print_r($unidadesOccidentalXcaret, true). "</pre>";
 
      return $unidadesOccidentalXcaret;
}

function getCasaDelMar($unidadesData, $unidadesCasaDelMar, $listaPorClubesSL){

    array_walk($unidadesData, function($item, $index) use (&$unidadesCasaDelMar, &$listaPorClubesSL) {

        if(is_numeric(array_search($index, $listaPorClubesSL['Casa Del Mar']))){
         $resMapeo = array_search($index, $listaPorClubesSL['Casa Del Mar']);
         array_push($unidadesCasaDelMar, $listaPorClubesSL['Casa Del Mar'][$resMapeo]);
        }     
         
      }, $listaPorClubesSL); 
     // echo "<pre>".print_r($unidadesCasaDelMar, true). "</pre>";
 
      return $unidadesCasaDelMar;
}

function getMarival($unidadesData, $unidadesMarival, $listaPorClubesSL){

    array_walk($unidadesData, function($item, $index) use (&$unidadesMarival, &$listaPorClubesSL) {

        if(is_numeric(array_search($index, $listaPorClubesSL['Marival']))){
         $resMapeo = array_search($index, $listaPorClubesSL['Marival']);
         array_push($unidadesMarival, $listaPorClubesSL['Marival'][$resMapeo]);
        }     
         
      }, $listaPorClubesSL); 
     // echo "<pre>".print_r($unidadesMarival, true). "</pre>";
 
      return $unidadesMarival;
}

function getVidantaNuevoVallarta($unidadesData, $arrUnidadesVidantaDestinosNuevoVallarta, $listaPorClubesSL){
    
    $arraPorDestinos = array();
    array_walk($unidadesData, function($item, $index) use (&$arrUnidadesVidantaDestinosNuevoVallarta, &$listaPorClubesSL) {
        // echo "<pre>".print_r($arrUnidadesVidantaDestinosNuevoVallarta, true). "</pre>";

         if(is_numeric(array_search($index, $listaPorClubesSL['Vidanta']))){
            $resMapeo = array_search($index, $listaPorClubesSL['Vidanta']);

           $destinoSubmission =  implode(" ", array_splice(explode(" ", trim($index)), -2));
           
           if($destinoSubmission === "Nuevo Vallarta") {
            array_push($arrUnidadesVidantaDestinosNuevoVallarta, $listaPorClubesSL['Vidanta'][$resMapeo]);
          }
           
         }
         
     }, $listaPorClubesSL); 
     
    // echo "<pre>".print_r($arrUnidadesVidantaDestinosNuevoVallarta, true). "</pre>";
    return $arrUnidadesVidantaDestinosNuevoVallarta;
  
}

function getVidantaRivieraMaya($unidadesData, $arrUnidadesVidantaDestinosRivieraMaya, $listaPorClubesSL){
    
    $arraPorDestinos = array();
    array_walk($unidadesData, function($item, $index) use (&$arrUnidadesVidantaDestinosRivieraMaya, &$listaPorClubesSL) {
        // echo "<pre>".print_r($arrUnidadesVidantaDestinosRivieraMaya, true). "</pre>";

         if(is_numeric(array_search($index, $listaPorClubesSL['Vidanta']))){
            $resMapeo = array_search($index, $listaPorClubesSL['Vidanta']);

           $destinoSubmission =  implode(" ", array_splice(explode(" ", trim($index)), -2));
           
           if($destinoSubmission === "Riviera Maya") {
            array_push($arrUnidadesVidantaDestinosRivieraMaya, $listaPorClubesSL['Vidanta'][$resMapeo]);
          }
           
         }
         
     }, $listaPorClubesSL); 
     
    // echo "<pre>".print_r($arrUnidadesVidantaDestinosRivieraMaya, true). "</pre>";
    return $arrUnidadesVidantaDestinosRivieraMaya;
  
}

function getVidantaLosCabos($unidadesData, $arrUnidadesVidantaDestinosLosCabos, $listaPorClubesSL){
    
    $arraPorDestinos = array();
    array_walk($unidadesData, function($item, $index) use (&$arrUnidadesVidantaDestinosLosCabos, &$listaPorClubesSL) {
        // echo "<pre>".print_r($arrUnidadesVidantaDestinosLosCabos, true). "</pre>";

         if(is_numeric(array_search($index, $listaPorClubesSL['Vidanta']))){
            $resMapeo = array_search($index, $listaPorClubesSL['Vidanta']);

           $destinoSubmission =  implode(" ", array_splice(explode(" ", trim($index)), -2));
           
           if($destinoSubmission === "Los Cabos" || $destinoSubmission === "East Cape") {
            array_push($arrUnidadesVidantaDestinosLosCabos, $listaPorClubesSL['Vidanta'][$resMapeo]);
          }
           
         }
         
     }, $listaPorClubesSL); 
     
    // echo "<pre>".print_r($arrUnidadesVidantaDestinosLosCabos, true). "</pre>";
    return $arrUnidadesVidantaDestinosLosCabos;
  
}

function crearSalidaUnidadReservasNew($unidadEnRentaName, $unidadEnRentaArray, $turno){

    $VimaIdiomasClass = getVimaIdiomasInstance();
    //echo "<pre>".print_r( $unidadEnRentaArray, true)."</pre>";
    $placeReserva = "1st ";

   $filasOutput = "";
    $fila = "";
   
    $fechasEncimadas = array();
  
    if(gettype($unidadEnRentaArray) == 'array'){
       // error_log("unidad con semanas depositadas - log :  ". $unidadEnRentaName);
        foreach( $unidadEnRentaArray as $key => $unidadRenta):
            
        
            //  echo "<pre>".print_r( $$unidadRenta[0][$unidadEnRentaName]['date-of-start'][0], true)."</pre>";
            
            $fechasEncimadas[$key] =  $unidadRenta[0][$unidadEnRentaName]['date-of-start'][0];
            

       if(sizeof($unidadEnRentaArray) > 1){
           foreach($unidadRenta as $unidad):
              // var_dump($key);
           
           
               $fila = crearFilasOutput($unidad, $fechasEncimadas);
               ///inserta el link a la submision si el usuario es admin
               if(current_user_can( 'manage_options' )){
                //echo "<pre>".print_r($unidad, true)."</pre>";
                $fila .= '<br />';
                $fila .= '<a onmouseover="resaltarSubmission" class="link_to_submission" href="'.get_edit_post_link( $key ).'" target="_blank">View Submission: '.$key.' - Dates: <b>'.date("M/d",$unidad[$unidadEnRentaName]['date-of-start'][0]).'-'.date("M/d/Y",$unidad[$unidadEnRentaName]['date-of-end'][0]).'</b> '.$unidad[$unidadEnRentaName]['initials'][0].' </a>';
               }
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
       ///inserta el link a la submision si el usuario es admin
               if(current_user_can( 'manage_options' )){
                //echo "<pre>".print_r($unidad, true)."</pre>";
                $fila .= '<br />';
                $fila .= '<a onmouseover="resaltarSubmission" class="link_to_submission" href="'.get_edit_post_link( $key ).'" target="_blank">View Submission: '.$key.' - Dates: <b>'.date("M/d",$unidadRenta[0][$unidadEnRentaName]['date-of-start'][0]).'-'.date("M/d/Y",$$unidadRenta[0][$unidadEnRentaName]['date-of-end'][0]).'</b> '.$$unidadRenta[0][$unidadEnRentaName]['initials'][0].' </a>';
               }
       $filasOutput .= $fila;
       //echo "<pre>".print_r($unidadRenta[0][$unidadEnRentaName], true)."</pre>";
       }
      // echo "<pre>".sizeof($unidadRenta)."</pre>";
       //$filasOutput = "";

           

       
       
       
       
       
   endforeach;
        //return;
    }else{
      //  error_log("unidad sin semanas - log :  ". $unidadEnRentaName);
    }
    
   

     // var_dump($unidadEnRentaName);
       

   $htmlOutput = '

   <div class="jet-accordion__item jet-toggle jet-toggle-move-up-effect '.str_replace(" ", "-", $unidadEnRentaName).'">
        <div id="jet-toggle-control-2181'.$turno.'" class="jet-toggle__control elementor-menu-anchor" data-toggle="'.$unidadEnRentaArray[0]['confirmation-of-reservation'][0].'" role="tab" aria-controls="jet-toggle-content-2181'.$turno.'" aria-expanded="false" data-template-id="'.$unidadEnRentaArray[0]['confirmation-of-reservation'][0].'">
            <div class="jet-toggle__label-text">'.$VimaIdiomasClass->translate($unidadEnRentaName).'</div>
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
                                                        max-width: 98%;
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
                                                                        <div class="jet-table__cell-inner"><div class="jet-table__cell-content" style="width:100%; display:block; text-align:center;"><div class="jet-table__cell-text">'.$VimaIdiomasClass->translate('Privileges').'</div></div></div>
                                                                    </th>
                                                                        <th class="jet-table__cell elementor-repeater-item-069bfb6 jet-table__head-cell" scope="col" 
                                                                        style="background-color: #000 !important;
                                                                    font-family: Cormorant Garamond, sans-serif;
                                                                    font-size:18px;
                                                                    text-align:center !important;
                                                                    border: thin solid rgb(215,215,215) !important;
                                                                    
                                                                    ">
                                                                        <div class="jet-table__cell-inner"><div class="jet-table__cell-content" style="width:100%; display:block; text-align:center;"><div class="jet-table__cell-text">'.$VimaIdiomasClass->translate('Dates').'</div></div></div>
                                                                    </th>
                                                                        <th class="jet-table__cell elementor-repeater-item-fa3f41e jet-table__head-cell" scope="col"
                                                                        style="background-color: #000 !important;
                                                                        font-family: Cormorant Garamond, sans-serif;
                                                                        font-size:18px;
                                                                        text-align:center !important;
                                                                        border: thin solid rgb(215,215,215) !important;
                                                                        
                                                                        "><div class="jet-table__cell-inner"><div class="jet-table__cell-content" style="width:100%; display:block; text-align:center;"><div class="jet-table__cell-text">'.$VimaIdiomasClass->translate('Unit Type').'</div></div></div>
                                                                    </th>
                                                                        <th class="jet-table__cell elementor-repeater-item-0c491f0 jet-table__head-cell" scope="col"
                                                                        style="background-color: #000 !important;
                                                                        font-family: Cormorant Garamond, sans-serif;
                                                                        font-size:18px;
                                                                        text-align:center !important;
                                                                        border-top-right-radius: 5px;
                                                                        border: thin solid rgb(215,215,215) !important;
                                                                        "><div class="jet-table__cell-inner"><div class="jet-table__cell-content" style="width:100%; display:block; text-align:center;"><div class="jet-table__cell-text">'.$VimaIdiomasClass->translate('Initials').'</div></div></div>
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

function comprobarVigencia97Dias($fecha){
    $diasBorrarNotificacion = 1; // se actualiza peticion egerardo para quitar la regla de los 97 
    $fechaInicio = $fecha;
    
    $fechaInicio = strtotime(date("Y-m-d",$fechaInicio));
   // echo "<pre>".print_r($fechaInicio, true)."</pre>";
    $fecha97dias = strtotime(date("Y-m-d", $fechaInicio).'-'.$diasBorrarNotificacion.' day');
   // echo "<pre>".print_r($fecha97dias, true)."</pre>";
    $fechaMenosUnDia = strtotime(date("Y-m-d", $fechaInicio).'-1 day');

    if($fecha97dias < strtotime(date("Y-m-d"))){
        return false;
    }

    return true;
}
function generarAcordion($unidadesData, $unidades, $unidadName){

    echo '<div class="elementor-element elementor-element-4c14f0c elementor-widget elementor-widget-jet-accordion cabecera_accordion" data-id="4c14f0c" data-element_type="widget" data-widget_type="jet-accordion.default">
    <div class="elementor-widget-container">
        <div class="jet-accordion" data-settings="{&quot;collapsible&quot;:false,&quot;ajaxTemplate&quot;:false,&quot;switchScrolling&quot;:false,&quot;switchScrollingOffset&quot;:0,&quot;switchScrollingDelay&quot;:500}">
<div class="jet-accordion__inner">
    <div class="jet-accordion__item jet-toggle jet-toggle-move-up-effect ">
                <div id="jet-toggle-control-7971" class="jet-toggle__control elementor-menu-anchor" data-toggle="1" role="button" tabindex="0" aria-controls="jet-toggle-content-7971" aria-expanded="false" data-template-id="7025">
                    <div class="jet-toggle__label-text">'.$unidadName.'</div>							</div>
                <div id="jet-toggle-content-7971" class="jet-toggle__content" data-toggle="1" role="region" data-template-id="7025">
                    <div class="jet-toggle__content-inner">		<div data-elementor-type="page" data-elementor-id="7025" class="elementor elementor-7025" data-elementor-post-type="elementor_library">
            <section class="elementor-section elementor-top-section elementor-element elementor-element-766fe85 elementor-section-full_width elementor-section-height-default elementor-section-height-default jet-parallax-section" data-id="766fe85" data-element_type="section" data-settings="{&quot;jet_parallax_layout_list&quot;:[{&quot;jet_parallax_layout_image&quot;:{&quot;url&quot;:&quot;&quot;,&quot;id&quot;:&quot;&quot;,&quot;size&quot;:&quot;&quot;},&quot;_id&quot;:&quot;a8a3a0b&quot;,&quot;jet_parallax_layout_image_tablet&quot;:{&quot;url&quot;:&quot;&quot;,&quot;id&quot;:&quot;&quot;,&quot;size&quot;:&quot;&quot;},&quot;jet_parallax_layout_image_mobile&quot;:{&quot;url&quot;:&quot;&quot;,&quot;id&quot;:&quot;&quot;,&quot;size&quot;:&quot;&quot;},&quot;jet_parallax_layout_speed&quot;:{&quot;unit&quot;:&quot;%&quot;,&quot;size&quot;:50,&quot;sizes&quot;:[]},&quot;jet_parallax_layout_type&quot;:&quot;scroll&quot;,&quot;jet_parallax_layout_direction&quot;:null,&quot;jet_parallax_layout_fx_direction&quot;:null,&quot;jet_parallax_layout_z_index&quot;:&quot;&quot;,&quot;jet_parallax_layout_bg_x&quot;:50,&quot;jet_parallax_layout_bg_x_tablet&quot;:&quot;&quot;,&quot;jet_parallax_layout_bg_x_mobile&quot;:&quot;&quot;,&quot;jet_parallax_layout_bg_y&quot;:50,&quot;jet_parallax_layout_bg_y_tablet&quot;:&quot;&quot;,&quot;jet_parallax_layout_bg_y_mobile&quot;:&quot;&quot;,&quot;jet_parallax_layout_bg_size&quot;:&quot;auto&quot;,&quot;jet_parallax_layout_bg_size_tablet&quot;:&quot;&quot;,&quot;jet_parallax_layout_bg_size_mobile&quot;:&quot;&quot;,&quot;jet_parallax_layout_animation_prop&quot;:&quot;transform&quot;,&quot;jet_parallax_layout_on&quot;:[&quot;desktop&quot;,&quot;tablet&quot;]}]}"><div class="jet-parallax-section__layout elementor-repeater-item-a8a3a0b jet-parallax-section__scroll-layout"><div class="jet-parallax-section__image" style="background-position: 50% 50%; background-image: url(&quot;&quot;); transform: translateY(225.1px);"></div></div>
            <div class="elementor-container elementor-column-gap-no">
        <div class="elementor-column elementor-col-100 elementor-top-column elementor-element elementor-element-a45fae6" data-id="a45fae6" data-element_type="column">
<div class="elementor-widget-wrap elementor-element-populated">
            <div class="elementor-element elementor-element-faedda5 elementor-widget elementor-widget-jet-table" data-id="faedda5" data-element_type="widget" data-widget_type="jet-table.default">
    <div class="elementor-widget-container">';
    echo '<div class="elementor-element elementor-element-0996476 elementor-widget elementor-widget-shortcode" data-id="0996476" data-element_type="widget" data-widget_type="shortcode.default">
    <div class="elementor-widget-container">
        <div class="elementor-element elementor-element-d0684f7 elementor-widget accordion_reservas_og elementor-widget-jet-accordion" data-id="d0684f7" data-element_type="widget" data-widget_type="jet-accordion.default">
            <div class="elementor-widget-container">
                <div class="jet-accordion" data-settings="{" collapsible":false,"ajaxtemplate":false,"switchscrolling":false}"="" role="tablist">
                    <div class="jet-accordion__inner">';

                    $turno = 0; 
                    $newArrUnitsDataKey  = array();
                    foreach($unidades as $rentUnit):
            
                        $dataRentUnit =  $unidadesData[$rentUnit];
                        $newArrUnitsDataKey = array();

                        if(gettype($dataRentUnit) === 'array'){
                            foreach($dataRentUnit as $key => $dataRent){

                                 $fechaVigente = comprobarVigencia97Dias(get_post_meta( $key, 'date-of-start', true ));
                                if(!$fechaVigente){
                                 continue;
                                }
                                $tempArr = [$dataRent[0]['unit-name'] => get_post_meta( $key )];
                                $newArrUnitsDataKey[$key] = [$tempArr];
                                 
                             }
                        }

                        $forRentUnits =  crearSalidaUnidadReservasNew($rentUnit, $newArrUnitsDataKey, $turno);
                        echo $forRentUnits;
                        $turno ++;
                    endforeach;
                    
                            
                                         
    echo '                           </div>
                    </div>
                </div>
            </div>		
        </div>
    </div>'; 
    echo'					</div>
    </div>
        </div>
    </div>
        </div>
    </section>
    </div>
    </div>
                </div>
            </div>								</div>
    </div>
    </div>
    </div>';
}

function generarAccordionInternoVidanta($unidadesData, $unidades, $destino ){
    echo '<div class="elementor-element elementor-element-4c14f0c elementor-widget elementor-widget-jet-accordion cabecera_accordion" data-id="4c14f0c" data-element_type="widget" data-widget_type="jet-accordion.default">
    <div class="elementor-widget-container">
        <div class="jet-accordion" data-settings="{&quot;collapsible&quot;:false,&quot;ajaxTemplate&quot;:false,&quot;switchScrolling&quot;:false,&quot;switchScrollingOffset&quot;:0,&quot;switchScrollingDelay&quot;:500}">
<div class="jet-accordion__inner">
    <div class="jet-accordion__item jet-toggle jet-toggle-move-up-effect ">
                <div id="jet-toggle-control-7971" class="jet-toggle__control elementor-menu-anchor" data-toggle="1" role="button" tabindex="0" aria-controls="jet-toggle-content-7971" aria-expanded="false" data-template-id="7025">
                    <div class="jet-toggle__label-text">'.$destino .'</div>							</div>
                <div id="jet-toggle-content-7971" class="jet-toggle__content" data-toggle="1" role="region" data-template-id="7025">
                    <div class="jet-toggle__content-inner">		<div data-elementor-type="page" data-elementor-id="7025" class="elementor elementor-7025" data-elementor-post-type="elementor_library">
            <section class="elementor-section elementor-top-section elementor-element elementor-element-766fe85 elementor-section-full_width elementor-section-height-default elementor-section-height-default jet-parallax-section" data-id="766fe85" data-element_type="section" data-settings="{&quot;jet_parallax_layout_list&quot;:[{&quot;jet_parallax_layout_image&quot;:{&quot;url&quot;:&quot;&quot;,&quot;id&quot;:&quot;&quot;,&quot;size&quot;:&quot;&quot;},&quot;_id&quot;:&quot;a8a3a0b&quot;,&quot;jet_parallax_layout_image_tablet&quot;:{&quot;url&quot;:&quot;&quot;,&quot;id&quot;:&quot;&quot;,&quot;size&quot;:&quot;&quot;},&quot;jet_parallax_layout_image_mobile&quot;:{&quot;url&quot;:&quot;&quot;,&quot;id&quot;:&quot;&quot;,&quot;size&quot;:&quot;&quot;},&quot;jet_parallax_layout_speed&quot;:{&quot;unit&quot;:&quot;%&quot;,&quot;size&quot;:50,&quot;sizes&quot;:[]},&quot;jet_parallax_layout_type&quot;:&quot;scroll&quot;,&quot;jet_parallax_layout_direction&quot;:null,&quot;jet_parallax_layout_fx_direction&quot;:null,&quot;jet_parallax_layout_z_index&quot;:&quot;&quot;,&quot;jet_parallax_layout_bg_x&quot;:50,&quot;jet_parallax_layout_bg_x_tablet&quot;:&quot;&quot;,&quot;jet_parallax_layout_bg_x_mobile&quot;:&quot;&quot;,&quot;jet_parallax_layout_bg_y&quot;:50,&quot;jet_parallax_layout_bg_y_tablet&quot;:&quot;&quot;,&quot;jet_parallax_layout_bg_y_mobile&quot;:&quot;&quot;,&quot;jet_parallax_layout_bg_size&quot;:&quot;auto&quot;,&quot;jet_parallax_layout_bg_size_tablet&quot;:&quot;&quot;,&quot;jet_parallax_layout_bg_size_mobile&quot;:&quot;&quot;,&quot;jet_parallax_layout_animation_prop&quot;:&quot;transform&quot;,&quot;jet_parallax_layout_on&quot;:[&quot;desktop&quot;,&quot;tablet&quot;]}]}"><div class="jet-parallax-section__layout elementor-repeater-item-a8a3a0b jet-parallax-section__scroll-layout"><div class="jet-parallax-section__image" style="background-position: 50% 50%; background-image: url(&quot;&quot;); transform: translateY(225.1px);"></div></div>
            <div class="elementor-container elementor-column-gap-no">
        <div class="elementor-column elementor-col-100 elementor-top-column elementor-element elementor-element-a45fae6" data-id="a45fae6" data-element_type="column">
<div class="elementor-widget-wrap elementor-element-populated">
            <div class="elementor-element elementor-element-faedda5 elementor-widget elementor-widget-jet-table" data-id="faedda5" data-element_type="widget" data-widget_type="jet-table.default">
    <div class="elementor-widget-container">';
    echo '<div class="elementor-element elementor-element-0996476 elementor-widget elementor-widget-shortcode" data-id="0996476" data-element_type="widget" data-widget_type="shortcode.default">
    <div class="elementor-widget-container">
        <div class="elementor-element elementor-element-d0684f7 elementor-widget accordion_reservas_og elementor-widget-jet-accordion" data-id="d0684f7" data-element_type="widget" data-widget_type="jet-accordion.default">
            <div class="elementor-widget-container">
                <div class="jet-accordion" data-settings="{" collapsible":false,"ajaxtemplate":false,"switchscrolling":false}"="" role="tablist">
                    <div class="jet-accordion__inner">';

                    $turno = 0; 
                    $newArrUnitsDataKey  = array();
                    foreach($unidades as $rentUnit):
            
                        $dataRentUnit =  $unidadesData[$rentUnit];
                        $newArrUnitsDataKey = array();
                      
                        if(gettype($dataRentUnit) === 'array'){
                            foreach($dataRentUnit as $key => $dataRent){

                                 $fechaVigente = comprobarVigencia97Dias(get_post_meta( $key, 'date-of-start', true ));
                                if(!$fechaVigente) continue;
                                $tempArr = [$dataRent[0]['unit-name'] => get_post_meta( $key )];
                                $newArrUnitsDataKey[$key] = [$tempArr];      
                             }
                        }
            
                        $forRentUnits =  crearSalidaUnidadReservasNew($rentUnit, $newArrUnitsDataKey, $turno);
                        echo $forRentUnits;
                        $turno ++;
                    endforeach;
                    
                            
                                         
    echo '                           </div>
                    </div>
                </div>
            </div>		
        </div>
    </div>'; 
    echo'					</div>
    </div>
        </div>
    </div>
        </div>
    </section>
    </div>
    </div>
                </div>
            </div>								</div>
    </div>
    </div>
    </div>';
}
function generarAcordionVidanta($unidadesData, $unidades, $unidadName){
             
    echo '<div class="elementor-element elementor-element-4c14f0c elementor-widget elementor-widget-jet-accordion cabecera_accordion" data-id="4c14f0c" data-element_type="widget" data-widget_type="jet-accordion.default">
    <div class="elementor-widget-container">
        <div class="jet-accordion" data-settings="{&quot;collapsible&quot;:false,&quot;ajaxTemplate&quot;:false,&quot;switchScrolling&quot;:false,&quot;switchScrollingOffset&quot;:0,&quot;switchScrollingDelay&quot;:500}">
<div class="jet-accordion__inner">
    <div class="jet-accordion__item jet-toggle jet-toggle-move-up-effect ">
                <div id="jet-toggle-control-7971" class="jet-toggle__control elementor-menu-anchor" data-toggle="1" role="button" tabindex="0" aria-controls="jet-toggle-content-7971" aria-expanded="false" data-template-id="7025">
                    <div class="jet-toggle__label-text">'.$unidadName.'</div>							</div>
                <div id="jet-toggle-content-7971" class="jet-toggle__content" data-toggle="1" role="region" data-template-id="7025">
                    <div class="jet-toggle__content-inner">		<div data-elementor-type="page" data-elementor-id="7025" class="elementor elementor-7025" data-elementor-post-type="elementor_library">
            <section class="elementor-section elementor-top-section elementor-element elementor-element-766fe85 elementor-section-full_width elementor-section-height-default elementor-section-height-default jet-parallax-section" data-id="766fe85" data-element_type="section" data-settings="{&quot;jet_parallax_layout_list&quot;:[{&quot;jet_parallax_layout_image&quot;:{&quot;url&quot;:&quot;&quot;,&quot;id&quot;:&quot;&quot;,&quot;size&quot;:&quot;&quot;},&quot;_id&quot;:&quot;a8a3a0b&quot;,&quot;jet_parallax_layout_image_tablet&quot;:{&quot;url&quot;:&quot;&quot;,&quot;id&quot;:&quot;&quot;,&quot;size&quot;:&quot;&quot;},&quot;jet_parallax_layout_image_mobile&quot;:{&quot;url&quot;:&quot;&quot;,&quot;id&quot;:&quot;&quot;,&quot;size&quot;:&quot;&quot;},&quot;jet_parallax_layout_speed&quot;:{&quot;unit&quot;:&quot;%&quot;,&quot;size&quot;:50,&quot;sizes&quot;:[]},&quot;jet_parallax_layout_type&quot;:&quot;scroll&quot;,&quot;jet_parallax_layout_direction&quot;:null,&quot;jet_parallax_layout_fx_direction&quot;:null,&quot;jet_parallax_layout_z_index&quot;:&quot;&quot;,&quot;jet_parallax_layout_bg_x&quot;:50,&quot;jet_parallax_layout_bg_x_tablet&quot;:&quot;&quot;,&quot;jet_parallax_layout_bg_x_mobile&quot;:&quot;&quot;,&quot;jet_parallax_layout_bg_y&quot;:50,&quot;jet_parallax_layout_bg_y_tablet&quot;:&quot;&quot;,&quot;jet_parallax_layout_bg_y_mobile&quot;:&quot;&quot;,&quot;jet_parallax_layout_bg_size&quot;:&quot;auto&quot;,&quot;jet_parallax_layout_bg_size_tablet&quot;:&quot;&quot;,&quot;jet_parallax_layout_bg_size_mobile&quot;:&quot;&quot;,&quot;jet_parallax_layout_animation_prop&quot;:&quot;transform&quot;,&quot;jet_parallax_layout_on&quot;:[&quot;desktop&quot;,&quot;tablet&quot;]}]}"><div class="jet-parallax-section__layout elementor-repeater-item-a8a3a0b jet-parallax-section__scroll-layout"><div class="jet-parallax-section__image" style="background-position: 50% 50%; background-image: url(&quot;&quot;); transform: translateY(225.1px);"></div></div>
            <div class="elementor-container elementor-column-gap-no">
        <div class="elementor-column elementor-col-100 elementor-top-column elementor-element elementor-element-a45fae6" data-id="a45fae6" data-element_type="column">
<div class="elementor-widget-wrap elementor-element-populated">
            <div class="elementor-element elementor-element-faedda5 elementor-widget elementor-widget-jet-table" data-id="faedda5" data-element_type="widget" data-widget_type="jet-table.default">
    <div class="elementor-widget-container">';
    echo '<div class="elementor-element elementor-element-0996476 elementor-widget elementor-widget-shortcode" data-id="0996476" data-element_type="widget" data-widget_type="shortcode.default">
    <div class="elementor-widget-container">
        <div class="elementor-element elementor-element-d0684f7 elementor-widget accordion_reservas_og elementor-widget-jet-accordion" data-id="d0684f7" data-element_type="widget" data-widget_type="jet-accordion.default">
            <div class="elementor-widget-container">
                <div class="jet-accordion" data-settings="{" collapsible":false,"ajaxtemplate":false,"switchscrolling":false}"="" role="tablist">
                    <div class="jet-accordion__inner">';

                    generarAccordionInternoVidanta($unidadesData, $unidades[0], 'Nuevo Vallarta' );
                    generarAccordionInternoVidanta($unidadesData, $unidades[1], 'Riviera Maya' );
                    generarAccordionInternoVidanta($unidadesData, $unidades[2], 'Los Cabos' );
                    
                            
                                         
    echo '                           </div>
                    </div>
                </div>
            </div>		
        </div>
    </div>'; 
    echo'					</div>
    </div>
        </div>
    </div>
        </div>
    </section>
    </div>
    </div>
                </div>
            </div>								</div>
    </div>
    </div>
    </div>';
}
function agruparPorClub($unidadesData, $listaPorClubesSL){
    $VimaIdiomasClass = getVimaIdiomasInstance();
    echo '<div class="elementor-element elementor-element-40ccfbe elementor-widget elementor-widget-heading" data-id="40ccfbe" data-element_type="widget" data-widget_type="heading.default">
    <div class="elementor-widget-container">
<style>/*! elementor - v3.23.0 - 05-08-2024 */
.elementor-heading-title{padding:0;margin:0;line-height:1}.elementor-widget-heading .elementor-heading-title[class*=elementor-size-]>a{color:inherit;font-size:inherit;line-height:inherit}.elementor-widget-heading .elementor-heading-title.elementor-size-small{font-size:15px}.elementor-widget-heading .elementor-heading-title.elementor-size-medium{font-size:19px}.elementor-widget-heading .elementor-heading-title.elementor-size-large{font-size:29px}.elementor-widget-heading .elementor-heading-title.elementor-size-xl{font-size:39px}.elementor-widget-heading .elementor-heading-title.elementor-size-xxl{font-size:59px}</style><h4 class="elementor-heading-title elementor-size-default">'.$VimaIdiomasClass->translate('Click on the name of your property').'<br>'.$VimaIdiomasClass->translate('to check the availability').'</h4>		</div>
    </div>';
    $unidadesVidanta = array();
    $unidadesVidanta = getVidantaUnits($unidadesData, $unidadesVidanta, $listaPorClubesSL);
    
    $unidadesGrandSolmar = array();
    $unidadesGrandSolmar = getGrandSolmarUnits($unidadesData, $unidadesGrandSolmar, $listaPorClubesSL);

    $unidadesPalaceResorts = array();
    $unidadesPalaceResorts = getPalaceResortsUnits($unidadesData, $unidadesPalaceResorts, $listaPorClubesSL);

    $unidadesHaciendaEncantada = array();
    $unidadesHaciendaEncantada = getHaciendaEncantadaUnits($unidadesData, $unidadesHaciendaEncantada, $listaPorClubesSL);

    $unidadesCaboVillas = array();
    $unidadesCaboVillas = getCaboVillasUnits($unidadesData, $unidadesCaboVillas, $listaPorClubesSL);

    $unidadesVillaGroup = array();
    $unidadesVillaGroup = getVillaGroupUnits($unidadesData, $unidadesVillaGroup, $listaPorClubesSL);

    $unidadesAmatte = array();
    $unidadesAmatte = getAmatteUnits($unidadesData, $unidadesAmatte, $listaPorClubesSL);

    $unidadesOccidentalXcaret = array();
    $unidadesOccidentalXcaret = getOccidentalXcaret($unidadesData, $unidadesOccidentalXcaret, $listaPorClubesSL);

    $unidadesCasaDelMar = array();
    $unidadesCasaDelMar = getCasaDelMar($unidadesData, $unidadesCasaDelMar, $listaPorClubesSL);

    $unidadesMarival = array();
    $unidadesMarival = getMarival($unidadesData, $unidadesMarival, $listaPorClubesSL);
    

    $arrUnidadesVidantaDestinosNuevoVallarta = array();
    $arrUnidadesVidantaDestinosNuevoVallarta = getVidantaNuevoVallarta($unidadesData, $arrUnidadesVidantaDestinosNuevoVallarta, $listaPorClubesSL);

    $arrUnidadesVidantaDestinosRivieraMaya = array();
    $arrUnidadesVidantaDestinosRivieraMaya = getVidantaRivieraMaya($unidadesData, $arrUnidadesVidantaDestinosRivieraMaya, $listaPorClubesSL);

    $arrUnidadesVidantaDestinosLosCabos = array();
    $arrUnidadesVidantaDestinosLosCabos = getVidantaLosCabos($unidadesData, $arrUnidadesVidantaDestinosLosCabos, $listaPorClubesSL);
    //echo "<pre>".print_r($unidadesVidanta, true)."</pre>";
    //echo "<pre>".print_r($unidadesGrandSolmar, true)."</pre>";
    //echo "<pre>".print_r($unidadesPalaceResorts, true)."</pre>";
    //echo "<pre>".print_r($unidadesHaciendaEncantada, true)."</pre>";
    //echo "<pre>".print_r($unidadesCaboVillas, true)."</pre>";
    //echo "<pre>".print_r($unidadesVillaGroup, true)."</pre>";
    //echo "<pre>".print_r($arrUnidadesVidantaDestinosNuevoVallarta, true)."</pre>";
    //echo "<pre>".print_r($arrUnidadesVidantaDestinosRivieraMaya, true)."</pre>";
    //echo "<pre>".print_r($arrUnidadesVidantaDestinosLosCabos, true)."</pre>";

                /////UNIDADES VIDANTA
                generarAcordionVidanta($unidadesData, [$arrUnidadesVidantaDestinosNuevoVallarta, $arrUnidadesVidantaDestinosRivieraMaya, $arrUnidadesVidantaDestinosLosCabos ], 'Vidanta');
                //////TERMINA UNIDADES VIDANTA

                /// UNIDADES GRAND SOLMAR
                generarAcordion($unidadesData, $unidadesGrandSolmar, 'Grand Solmar');
                /////TERMINA UNIDADES GRAND SOLMAR

                /// UNIDADES PALACE RESORTS
                generarAcordion($unidadesData, $unidadesPalaceResorts, 'Palace Resorts');
                /////TERMINA UNIDADES PALACE RESORTS

                /// UNIDADES HACIENDA ENCANTADA
                generarAcordion($unidadesData, $unidadesHaciendaEncantada, 'Hacienda Encantada');
                /////TERMINA UNIDADES HACIENDA ENCANTADA

                /// UNIDADES CABO VILLAS
                generarAcordion($unidadesData, $unidadesCaboVillas, 'Cabo Villas');
                /////TERMINA UNIDADES CABO VILLAS

                /// UNIDADES VILLA GROUP
                generarAcordion($unidadesData, $unidadesVillaGroup, 'Villa Group');
                /////TERMINA UNIDADES VILLA GROUP

                /// UNIDADES AMATTE
                generarAcordion($unidadesData, $unidadesAmatte, 'Amatte');
                /////TERMINA UNIDADES AMATTE

                /// UNIDADES OCCIDENTAL XCARET
                generarAcordion($unidadesData, $unidadesOccidentalXcaret, 'Occidental Xcaret');
                /////TERMINA OCCIDENTAL XCARET

                /// UNIDADES CASA DEL MAR
                generarAcordion($unidadesData, $unidadesCasaDelMar, 'Zoëtry Casa del Mar');
                /////TERMINA CASA DEL MAR

                /// UNIDADES MARIVAL
                generarAcordion($unidadesData, $unidadesMarival, 'Marival');
                /////TERMINA MARIVAL
   
}



function getOrdenamientoUnitsClubsServiceLayer(){
    $url = "https://www.vacationintervalsmanagement.com/service-layer/api/v1/vima/units-list";
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "anton@vacationintervalsmanagement.com:VimaServiceLayer##2023-2024##!?");

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return "Error: $error_msg";
    }

    curl_close($ch);
    $listaOrdenUnidadesClub = json_decode($response, true);
    
    //echo "<pre>".print_r($listaOrdenUnidadesClub, true). "</pre>";


    //echo 'nueva tabla reservas';
    return $listaOrdenUnidadesClub;
}

add_shortcode('availability_calendar_sorted', function() {
    ?>
    <style type="text/css">
            .elementor-widget-jet-accordion{
                /* display: none; */
            }
            .elementor-element-4c14f0c{
                /* display: none; */
            }
            .accordion_reservas_og{
                display: revert;
            }
            .repetidas_2{
                background-color: #2D7BCB !important;
                color: #fff;
            }
            .repetidas_2 td{
                
                color: #fff !important;
            }
            .repetidas_3{
                background-color: #002C59 !important;
                color: #fff;
            }

            .repetidas_3 td{
                
                color: #fff !important;
            }
            .repetidas_4{
                background-color: #002C59 !important;
                color: #fff;
            }
            .repetidas_5{
                background-color: #002C59 !important;
                color: #fff;
            }


            .cabecera_accordion{

            }
            .jet-toggle__content-inner{
                padding: 0px !important;
            }
            .elementor-widget:not(:last-child){
                margin-bottom: 0px !important;
            }
            .jet-accordion{
                padding-top: 10px;
            }
            .subtituloDivisor{
                text-align: center;
                margin-bottom: 20px;
                font-family: 'Playfair Display';
                font-size: 1.2em;
                color: black;
            }
            

           
        </style>
        <script>
            document.getElementsByClassName('elementor-element-4c14f0c')[0].style.display="none";
            const divisorEastCape = () => {
                let primerElemEastCape = document.getElementsByClassName('Four-Bedroom-Empire-Estate-East-Cape')[0];
                let primerElemEastCapeParent = document.getElementsByClassName('Four-Bedroom-Empire-Estate-East-Cape')[0].parentElement;
                let line = document.createElement('div');
                line.classList.add('subtituloDivisor');
                line.innerHTML = 'East Cape<br>';
                primerElemEastCapeParent.insertBefore(line, primerElemEastCape);
            };

            document.addEventListener('DOMContentLoaded', divisorEastCape);
        </script>
    <?php

    $url = "https://www.vacationintervalsmanagement.com/service-layer/api/v1/vima/avcalendar/?partneremail=testmember@testemember.com&sesstoken=a2ac87f1b419526d5065740047e51e33";

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "anton@vacationintervalsmanagement.com:VimaServiceLayer##2023-2024##!?");

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return "Error: $error_msg";
    }

    curl_close($ch);
    $calendarioDataServiceLayer = json_decode($response, true);
    $listaPorClubesServiceLayer = getOrdenamientoUnitsClubsServiceLayer();
    $calendarioAgrupado = agruparPorClub($calendarioDataServiceLayer['data']['avcalendar'], $listaPorClubesServiceLayer['data']['units-list']);
    
    if(current_user_can( 'manage_options' )){
        echo '<pre>'.print_r("usuario es admin = true", true).'</pre>';
        //echo '<pre>'.print_r($calendarioDataServiceLayer['data']['avcalendar'], true).'</pre>';

    //     array_walk($unidadesData, function($item, $index) use (&$unidadesVidanta, &$listaPorClubesSL) {
    //     // echo "<pre>".print_r($unidadesVidanta, true). "</pre>";
    //      if(is_numeric(array_search($index, $listaPorClubesSL['Vidanta']))){
    //         $resMapeo = array_search($index, $listaPorClubesSL['Vidanta']);
    //         array_push($unidadesVidanta, $listaPorClubesSL['Vidanta'][$resMapeo]);
    //      }
         
    //  }, $listaPorClubesSL); 
    }
    
    //echo "<pre>".print_r($calendarioDataServiceLayer, true). "</pre>";
  //https://www.vacationintervalsmanagement.com/agreements/rental-submission-92/

    //echo 'nueva tabla reservas';

 
 
 });