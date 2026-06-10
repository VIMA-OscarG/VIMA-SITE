<?php

function mostrarCheckboxes($currentUser, $currentPRR) {
	$crear = true;

	if(isset($currentPRR['unit'])){
		$crear = false;
	}

	//echo "<pre>".print_r($currentPRR['unit']['4 Bedroom Empire Estate Nuevo Vallarta'], true). "</pre>";
	function testValor($array, $indice){
		if(sizeof($array) > 0){
			if(isset($array[$indice])){
				return $array[$indice];
			}
		}

		return "";
		
	}

	function determinarSeleccion($currentPRR, $unitName){
    	return isset($currentPRR['unit'][$unitName]) && $currentPRR['unit'][$unitName] === "true";
	}
    ?>
<style>
	/* Contenedor con scroll horizontal */
#resumen-unidades {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}
#resumen-unidades .sync_btn {
    font-size: 15px;
    text-align: center;
    pointer-events: none;
    font-weight: 800;
    color: mediumseagreen;

  }

.all{
	text-align:center;
  }

  #resumen-unidades tr{
	background-color: transparent !important;
	border-bottom: 1px solid #ccc !important;
  }
/* 
@media (max-width: 768px) {

  #resumen-unidades table {
    min-width: 700px; 
    border-collapse: collapse;
  }

  #resumen-unidades th,
  #resumen-unidades td {
    white-space: nowrap;
    padding: 6px 8px;
    font-size: 13px;
  }
*/
  #resumen-unidades input{

  }
    #resumen-unidades select{

  }

  .weeks, .fee, .rental, .multi{
  }
  .multiplier_select{
	  
  }

  .nueva_fila td:first-child{
	text-align: left !important;
  }
  
  li:has(.dtr-data) span:first-child {
	width: 170px;
    padding-right: 10px;
  }
/*
  #resumen-unidades .sync_btn {
    font-size: 12px;
    padding: 4px 8px;
  }

} */


  .save_under{
	margin: 16px 0;
    padding: 5px 10px;
    border: 1px solid #666;
    width: 100%;
    color: black;
  }

  .save_under:hover{
	background-color: lightgray;
	border-color: black;
  }
table.dataTable>tbody>tr>td {
    padding: 4px 5px !important;
	font-size: 1.1em;
}
</style>
<style>
.loader_sync {
  border: 2px solid #f3f3f3;
  border-radius: 50%;
  border-top: 2px solid #45494bff;
  width: 14px;
  height: 14px;
  -webkit-animation: spin 2s linear infinite; /* Safari */
  animation: spin 2s linear infinite;
}

/* Safari */
@-webkit-keyframes spin {
  0% { -webkit-transform: rotate(0deg); }
  100% { -webkit-transform: rotate(360deg); }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>

<link href="https://cdn.datatables.net/v/dt/dt-2.3.5/r-3.0.7/datatables.min.css" rel="stylesheet" integrity="sha384-BPLVaqLd/VHVzAwnRw5/zfDXOt6A3og8QHnmyFlLy/air1BZuwu0quAVk1Z6NkGB" crossorigin="anonymous">
 
<script src="https://cdn.datatables.net/v/dt/dt-2.3.5/r-3.0.7/datatables.min.js" integrity="sha384-R5oraIW0WVnbh3qO11mw/9A6JxGWPTdP/SreINwDA+r+CqRMUHAyIUXD9cPSl/pS" crossorigin="anonymous"></script>

<form action="" method="post">
    <input type="hidden" name="field_title" value="titulo_personalizado_prr">
        
        <!-- field email -->
        <div class="jet-form-row jet-form-row--first-visible">
            <div class="jet-form-col jet-form-col-12  field-type-text jet-form-field-container" data-field="field_email" data-conditional="false">
                <div class="jet-form__label">
                    <span class="jet-form__label-text">Email<span class="jet-form__required">*</span></span>
                </div>
                <input class="jet-form__field text-field " value="<?php echo $currentUser->user_email ?>" required="required" name="field_email" id="field_email" type="email" data-field-name="field_email">
            </div>
        </div>
        <!-- fin field_email -->

        <!-- fiel name -->
        <div class="jet-form-row">
            <div class="jet-form-col jet-form-col-12  field-type-text  jet-form-field-container" data-field="field_name" data-conditional="false">
                <div class="jet-form__label">
	                <span class="jet-form__label-text">Type your name here:<span class="jet-form__required">*</span></span>
	            </div>
                <input class="jet-form__field text-field " value="<?php echo $currentUser->display_name ?>" required="required" name="field_name" id="field_name" type="text" data-field-name="field_name">
            </div>
        </div>

        <!-- fin field_name -->

        <!-- fiel initials -->
        <div class="jet-form-row">
            <div class="jet-form-col jet-form-col-12  field-type-text  jet-form-field-container" data-field="field_initials" data-conditional="false">
                <div class="jet-form__label">
	                <span class="jet-form__label-text">Type your initials:<span class="jet-form__required">*</span></span>
					
	            </div>
                <input class="jet-form__field text-field " required="required" value="<?php echo testValor($currentPRR, 'initials'); ?>" name="field_initials" id="field_initials" type="text" data-field-name="field_initials">
            </div>
        </div>

        <!-- fin field_initials -->
        
		<!-- fields extension_loaded -->
		<input type="hidden" name="todo" value="<?php echo $crear ? 'create': 'update'; ?>">
		<input type="hidden" name="user" value="<?php echo $currentUser->ID; ?>">
		<input type="hidden" name="username" value="<?php echo $currentUser->display_name; ?>">
		<input type="hidden" name="useremail" value="<?php echo $currentUser->user_email; ?>">
		<input type="hidden" name="alreadypost" value="<?php echo testValor($currentPRR, 'alreadypost') ?>">
		<input type="hidden" name="iniciales" value="<?php echo testValor($currentPRR, 'initials'); ?>">
		<input type="hidden" name="weeksavailable" value="<?php echo testValor($currentPRR, 'number-of-weeks-available'); ?>">
		
		<!-- fin field extras -->
		
		<!-- loader -->
		<div class="loader loader-pos" id="loading"></div>
		<!-- fin loader -->

		<div id="resumen-unidades" style="margin-top:20px; margin-bottom:20px;">
			<a href="#inicio_tabla"></a>
				<table id="tabla_prr" >
				
				<thead>
					<tr>
						<th style=" color: #555; min-width: 200px;">Units selected (summary):</th>
						<th style="text-align: center !important;">status</th>
					</tr>
				</thead>
				<tbody id="lista-unidades-seleccionadas" style="font-size:12px; color: #000;">

				</tbody>
				</table>
				
				<span id="mensaje_summary"></span>
		</div> 
		<!-- fin resumen unidades -->

	<div class="jet-form-col jet-form-col-12  field-type-checkboxes  jet-form-field-container" data-field="field_unit" id="contenedor_checkboxes" data-conditional="false"><div class="jet-form__label">
	<span class="jet-form__label-text">Your unit for rent is: <small>(check at least one unit)</small> <span class="jet-form__required">*</span></span>
	
	</div><div class="jet-form__fields-group checkradio-wrap">			
            <!-- vidanta -->
		
        <div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" <?php echo (isset($currentPRR['unit']['4 Bedroom Empire Estate Nuevo Vallarta'])) && $currentPRR['unit']['4 Bedroom Empire Estate Nuevo Vallarta'] == "true" ? "checked": ""; ?> class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="4 Bedroom Empire Estate Nuevo Vallarta" data-field-name="field_unit">
				4 Bedroom Empire Estate 			</label>
		</div>
		<div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
				<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Four Bedroom Empire Estate East Cape" <?php echo isset($currentPRR['unit']['Four Bedroom Empire Estate East Cape']) && $currentPRR['unit']['Four Bedroom Empire Estate East Cape'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				4 Bedroom Empire Estate East Cape</label>
		</div>
		<div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
				<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Two Bedroom Empire Estate East Cape" <?php echo isset($currentPRR['unit']['Two Bedroom Empire Estate East Cape']) && $currentPRR['unit']['Two Bedroom Empire Estate East Cape'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				2 Bedroom Empire Estate East Cape</label>
		</div>
		<div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" data-grupal='{"index": "4 Bedroom Estates", "iguales": [" Riviera Maya"]}' name="field_unit[]" <?php echo isset($currentPRR['unit']['4 Bedroom Estates Loft Nuevo Vallarta']) && $currentPRR['unit']['4 Bedroom Estates Loft Nuevo Vallarta'] == "true" ? "checked" : ""; ?> class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="4 Bedroom Estates Loft Nuevo Vallarta" data-field-name="field_unit">
				4 Bedroom Estates		</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]"  data-grupal='{"index": "3 Bedroom Estates", "iguales": [" Riviera Maya"]}' <?php echo isset($currentPRR['unit']['3 Bedroom Estates Nuevo Vallarta']) && $currentPRR['unit']['3 Bedroom Estates Nuevo Vallarta'] == "true"  ? "checked" : ""; ?> class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="3 Bedroom Estates Nuevo Vallarta" data-field-name="field_unit">
				3 Bedroom Estates			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]"  data-grupal='{"index": "2 Bedroom Estates", "iguales": [" Riviera Maya"]}' <?php echo isset($currentPRR['unit']['2 Bedroom Estates Nuevo Vallarta']) && $currentPRR['unit']['2 Bedroom Estates Nuevo Vallarta'] == "true" ? "checked" : ""; ?> class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="2 Bedroom Estates Nuevo Vallarta" data-field-name="field_unit">
				2 Bedroom Estates			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]"  data-grupal='{"index": "1 Bedroom Estates", "iguales": [" Riviera Maya"]}' <?php echo isset($currentPRR['unit']['1 Bedroom Estates Nuevo Vallarta']) && $currentPRR['unit']['1 Bedroom Estates Nuevo Vallarta'] == "true" ? "checked" : ""; ?> class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="1 Bedroom Estates Nuevo Vallarta" data-field-name="field_unit">
				1 Bedroom Estates			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]"  data-grupal='{"index": "Grand Luxxe 4 Bedroom Residence", "iguales": [" Riviera Maya"]}' <?php echo isset($currentPRR['unit']['Grand Luxxe 4 Bedroom Residence Nuevo Vallarta']) && $currentPRR['unit']['Grand Luxxe 4 Bedroom Residence Nuevo Vallarta'] == "true" ? "checked" : ""; ?> class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Luxxe 4 Bedroom Residence Nuevo Vallarta" data-field-name="field_unit">
				Grand Luxxe 4 Bedroom Residence		</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" data-grupal='{"index": "Grand Luxxe 3 Bedroom Loft", "iguales": [" Riviera Maya"]}'  <?php echo isset($currentPRR['unit']['Grand Luxxe 3 Bedroom Loft Nuevo Vallarta']) && $currentPRR['unit']['Grand Luxxe 3 Bedroom Loft Nuevo Vallarta'] == "true" ? "checked" : ""; ?> class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Luxxe 3 Bedroom Loft Nuevo Vallarta" data-field-name="field_unit">
				Grand Luxxe 3 Bedroom Loft			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" data-grupal='{"index": "Grand Luxxe 2 Bedroom Loft", "iguales": [" Riviera Maya"]}' <?php echo isset($currentPRR['unit']['Grand Luxxe 2 Bedroom Loft Nuevo Vallarta']) && $currentPRR['unit']['Grand Luxxe 2 Bedroom Loft Nuevo Vallarta'] == "true" ? "checked" : ""; ?> class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Luxxe 2 Bedroom Loft Nuevo Vallarta" data-field-name="field_unit">
				Grand Luxxe 2 Bedroom Loft			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]"  data-grupal='{"index": "Grand Luxxe 3 Bedroom Spa Suite", "iguales": [" Riviera Maya"]}' <?php echo isset($currentPRR['unit']['Grand Luxxe 3 Bedroom Spa Suite Nuevo Vallarta']) && $currentPRR['unit']['Grand Luxxe 3 Bedroom Spa Suite Nuevo Vallarta'] == "true" ? "checked" : ""; ?> class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Luxxe 3 Bedroom Spa Suite Nuevo Vallarta" data-field-name="field_unit">
				Grand Luxxe 3 Bedroom Spa Suite			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" <?php echo isset($currentPRR['unit']['Grand Luxxe 2 Bedroom Spa Suite Nuevo Vallarta']) && $currentPRR['unit']['Grand Luxxe 2 Bedroom Spa Suite Nuevo Vallarta'] == "true" ? "checked" : ""; ?> class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Luxxe 2 Bedroom Spa Suite Nuevo Vallarta" data-field-name="field_unit">
				Grand Luxxe 2 Bedroom Spa Suite		</label>
		</div>
		
		<div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" <?php echo isset($currentPRR['unit']['Grand Luxxe 3 Bedroom Spa Suite Riviera Maya']) && $currentPRR['unit']['Grand Luxxe 3 Bedroom Spa Suite Riviera Maya'] == "true" ? "checked" : ""; ?> class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Luxxe 3 Bedroom Spa Suite Riviera Maya" data-field-name="field_unit">
				Grand Luxxe 3 Bedroom Spa Suite</label>
		</div>
		
		<div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" data-grupal='{"index": "Grand Luxxe 2 Bedroom Villa", "iguales": [" Riviera Maya"]}' <?php echo isset($currentPRR['unit']['Grand Luxxe 2 Bedroom Villa Nuevo Vallarta']) && $currentPRR['unit']['Grand Luxxe 2 Bedroom Villa Nuevo Vallarta'] == "true" ? "checked" : ""; ?> class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Luxxe 2 Bedroom Villa Nuevo Vallarta" data-field-name="field_unit">
				Grand Luxxe 2 Bedroom Villa			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" data-grupal='{"index": "Grand Luxxe 1 Bedroom Villa", "iguales": [" Riviera Maya"]}' <?php echo isset($currentPRR['unit']['Grand Luxxe 1 Bedroom Villa Nuevo Vallarta']) && $currentPRR['unit']['Grand Luxxe 1 Bedroom Villa Nuevo Vallarta'] == "true" ? "checked" : ""; ?> class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Luxxe 1 Bedroom Villa Nuevo Vallarta" data-field-name="field_unit">
				Grand Luxxe 1 Bedroom Villa		</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" data-grupal='{"index": "Grand Luxxe Junior Villa", "iguales": [" Riviera Maya"]}' <?php echo isset($currentPRR['unit']['Grand Luxxe Junior Villa Nuevo Vallarta']) && $currentPRR['unit']['Grand Luxxe Junior Villa Nuevo Vallarta'] == "true" ? "checked" : ""; ?> class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Luxxe Junior Villa Nuevo Vallarta" data-field-name="field_unit">
				Grand Luxxe Junior Villa		</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" data-grupal='{"index": "Grand Luxxe 2 Bedroom Suite", "iguales": [" Riviera Maya"]}' <?php echo isset($currentPRR['unit']['Grand Luxxe 2 Bedroom Suite Nuevo Vallarta']) && $currentPRR['unit']['Grand Luxxe 2 Bedroom Suite Nuevo Vallarta'] == "true" ? "checked" : ""; ?> class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Luxxe 2 Bedroom Suite Nuevo Vallarta" data-field-name="field_unit">
				Grand Luxxe 2 Bedroom Suite		</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]"  data-grupal='{"index": "Grand Luxxe 1 Bedroom Suite", "iguales": [" Riviera Maya"]}' <?php echo isset($currentPRR['unit']['Grand Luxxe 1 Bedroom Suite Nuevo Vallarta']) && $currentPRR['unit']['Grand Luxxe 1 Bedroom Suite Nuevo Vallarta'] == "true" ? "checked" : ""; ?> class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Luxxe 1 Bedroom Suite Nuevo Vallarta" data-field-name="field_unit">
				Grand Luxxe 1 Bedroom Suite			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" data-grupal='{"index": "Grand Luxxe Studio", "iguales": [" Riviera Maya"]}' <?php echo isset($currentPRR['unit']['Grand Luxxe Studio Nuevo Vallarta']) && $currentPRR['unit']['Grand Luxxe Studio Nuevo Vallarta'] == "true" ? "checked" : ""; ?> class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Luxxe Studio Nuevo Vallarta" data-field-name="field_unit">
				Grand Luxxe Studio		</label>
		</div>
		
		<div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" data-grupal='{"index": "Grand Mayan 2 Bedroom Suite", "iguales": [" Riviera Maya", " Los Cabos"]}' <?php echo isset($currentPRR['unit']['Grand Mayan 2 Bedroom Suite Nuevo Vallarta']) && $currentPRR['unit']['Grand Mayan 2 Bedroom Suite Nuevo Vallarta'] == "true" ? "checked" : ""; ?> class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Mayan 2 Bedroom Suite Nuevo Vallarta" data-field-name="field_unit">
				Grand Mayan 2 Bedroom Suite			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]"  data-grupal='{"index": "Grand Mayan 1 Bedroom Suite", "iguales": [" Riviera Maya", " Los Cabos"]}' <?php echo isset($currentPRR['unit']['Grand Mayan 1 Bedroom Suite Nuevo Vallarta']) && $currentPRR['unit']['Grand Mayan 1 Bedroom Suite Nuevo Vallarta'] == "true" ? "checked" : ""; ?> class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Mayan 1 Bedroom Suite Nuevo Vallarta" data-field-name="field_unit">
				Grand Mayan 1 Bedroom Suite		</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]"  data-grupal='{"index": "Grand Mayan Studio", "iguales": [" Riviera Maya", " Los Cabos"]}' <?php echo isset($currentPRR['unit']['Grand Mayan Studio Nuevo Vallarta']) && $currentPRR['unit']['Grand Mayan Studio Nuevo Vallarta'] == "true" ? "checked" : ""; ?> class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Mayan Studio Nuevo Vallarta" data-field-name="field_unit">
				Grand Mayan Studio		</label>
		</div>
		<div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" data-grupal='{"index": "Mayan Palace Two Bedroom Suite", "iguales": [" Riviera Maya"]}' <?php echo isset($currentPRR['unit']['Mayan Palace Two Bedroom Suite Nuevo Vallarta']) && $currentPRR['unit']['Mayan Palace Two Bedroom Suite Nuevo Vallarta'] == "true" ? "checked" : ""; ?> class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Mayan Palace Two Bedroom Suite Nuevo Vallarta" data-field-name="field_unit">
				Mayan Palace 2 Bedroom Suite		</label>
		</div>
		<div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" data-grupal='{"index": "Mayan Palace One Bedroom Suite", "iguales": [" Riviera Maya"]}' <?php echo isset($currentPRR['unit']['Mayan Palace One Bedroom Suite Nuevo Vallarta']) && $currentPRR['unit']['Mayan Palace One Bedroom Suite Nuevo Vallarta'] == "true" ? "checked" : ""; ?> class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Mayan Palace One Bedroom Suite Nuevo Vallarta" data-field-name="field_unit">
				Mayan Palace 1 Bedroom Suite			</label>
		</div>
		<div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="repetido jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="4 Bedroom Estates Riviera Maya" <?php echo isset($currentPRR['unit']['4 Bedroom Estates Riviera Maya']) && $currentPRR['unit']['4 Bedroom Estates Riviera Maya'] == "true" ? "checked" : ""; ?>  data-field-name="field_unit">
				4 Bedroom Estates Riviera Maya			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="repetido jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="3 Bedroom Estates Riviera Maya"  <?php echo isset($currentPRR['unit']['3 Bedroom Estates Riviera Maya']) && $currentPRR['unit']['3 Bedroom Estates Riviera Maya'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				3 Bedroom Estates Riviera Maya			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="repetido jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="2 Bedroom Estates Riviera Maya"  <?php echo isset($currentPRR['unit']['2 Bedroom Estates Riviera Maya']) && $currentPRR['unit']['2 Bedroom Estates Riviera Maya'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				 2 Bedroom Estates Riviera Maya			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="repetido jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="1 Bedroom Estates Riviera Maya" <?php echo isset($currentPRR['unit']['1 Bedroom Estates Riviera Maya']) && $currentPRR['unit']['1 Bedroom Estates Riviera Maya'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				 1 Bedroom Estates Riviera Maya			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="repetido jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Luxxe 4 Bedroom Residence Riviera Maya"  <?php echo isset($currentPRR['unit']['Grand Luxxe 4 Bedroom Residence Riviera Maya']) && $currentPRR['unit']['Grand Luxxe 4 Bedroom Residence Riviera Maya'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Grand Luxxe 4 Bedroom Residence Riviera Maya			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="repetido jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Luxxe 3 Bedroom Loft Riviera Maya" <?php echo isset($currentPRR['unit']['Grand Luxxe 3 Bedroom Loft Riviera Maya']) && $currentPRR['unit']['Grand Luxxe 3 Bedroom Loft Riviera Maya'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Grand Luxxe 3 Bedroom Loft Riviera Maya			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="repetido jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Luxxe 2 Bedroom Loft Riviera Maya" <?php echo isset($currentPRR['unit']['Grand Luxxe 2 Bedroom Loft Riviera Maya']) && $currentPRR['unit']['Grand Luxxe 2 Bedroom Loft Riviera Maya'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Grand Luxxe 2 Bedroom Loft Riviera Maya			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="repetido jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Luxxe 2 Bedroom Villa Riviera Maya" <?php echo isset($currentPRR['unit']['Grand Luxxe 2 Bedroom Villa Riviera Maya']) && $currentPRR['unit']['Grand Luxxe 2 Bedroom Villa Riviera Maya'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Grand Luxxe 2 Bedroom Villa Riviera Maya			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="repetido jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Luxxe 1 Bedroom Villa Riviera Maya" <?php echo isset($currentPRR['unit']['Grand Luxxe 1 Bedroom Villa Riviera Maya']) && $currentPRR['unit']['Grand Luxxe 1 Bedroom Villa Riviera Maya'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Grand Luxxe 1 Bedroom Villa Riviera Maya			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="repetido jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Luxxe Junior Villa Riviera Maya" <?php echo isset($currentPRR['unit']['Grand Luxxe Junior Villa Riviera Maya']) && $currentPRR['unit']['Grand Luxxe Junior Villa Riviera Maya'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Grand Luxxe Junior Villa Riviera Maya			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="repetido jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Luxxe 2 Bedroom Suite Riviera Maya" <?php echo isset($currentPRR['unit']['Grand Luxxe 2 Bedroom Suite Riviera Maya']) && $currentPRR['unit']['Grand Luxxe 2 Bedroom Suite Riviera Maya'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Grand Luxxe 2 Bedroom Suite Riviera Maya			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="repetido jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Luxxe 1 Bedroom Suite Riviera Maya" <?php echo isset($currentPRR['unit']['Grand Luxxe 1 Bedroom Suite Riviera Maya']) && $currentPRR['unit']['Grand Luxxe 1 Bedroom Suite Riviera Maya'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Grand Luxxe 1 Bedroom Suite Riviera Maya			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="repetido jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Luxxe Studio Riviera Maya" <?php echo isset($currentPRR['unit']['Grand Luxxe Studio Riviera Maya']) && $currentPRR['unit']['Grand Luxxe Studio Riviera Maya'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Grand Luxxe Studio Riviera Maya			</label>
		</div>
		<div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" data-grupal='{"index": "Mayan Palace Two Bedroom Suite", "iguales": [" Riviera Maya"]}' <?php echo isset($currentPRR['unit']['Mayan Palace Two Bedroom Suite Riviera Maya']) && $currentPRR['unit']['Mayan Palace Two Bedroom Suite Riviera Maya'] == "true" ? "checked" : ""; ?> class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Mayan Palace Two Bedroom Suite Riviera Maya" data-field-name="field_unit">
				Mayan Palace 2 Bedroom Suite Riviera Maya</label>
		</div>
		<div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" data-grupal='{"index": "Mayan Palace One Bedroom Suite", "iguales": [" Riviera Maya"]}' <?php echo isset($currentPRR['unit']['Mayan Palace One Bedroom Suite Riviera Maya']) && $currentPRR['unit']['Mayan Palace One Bedroom Suite Riviera Maya'] == "true" ? "checked" : ""; ?> class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Mayan Palace One Bedroom Suite Riviera Maya" data-field-name="field_unit">
				Mayan Palace 1 Bedroom Suite Riviera Maya			</label>
		</div>
		<div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="repetido jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Mayan 2 Bedroom Suite Riviera Maya" <?php echo isset($currentPRR['unit']['Grand Mayan 2 Bedroom Suite Riviera Maya']) && $currentPRR['unit']['Grand Mayan 2 Bedroom Suite Riviera Maya'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Grand Mayan 2 Bedroom Suite Riviera Maya			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="repetido jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Mayan 1 Bedroom Suite Riviera Maya" <?php echo isset($currentPRR['unit']['Grand Mayan 1 Bedroom Suite Riviera Maya']) && $currentPRR['unit']['Grand Mayan 1 Bedroom Suite Riviera Maya'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Grand Mayan 1 Bedroom Suite Riviera Maya			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="repetido jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Mayan Studio Riviera Maya" <?php echo isset($currentPRR['unit']['Grand Mayan Studio Riviera Maya']) && $currentPRR['unit']['Grand Mayan Studio Riviera Maya'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Grand Mayan Studio Riviera Maya			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Mayan 2 Bedroom Penthouse Los Cabos"  <?php echo isset($currentPRR['unit']['Grand Mayan 2 Bedroom Penthouse Los Cabos']) && $currentPRR['unit']['Grand Mayan 2 Bedroom Penthouse Los Cabos'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Grand Mayan 2 Bedroom Penthouse San José del Cabo		</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Mayan 1 Bedroom Penthouse Los Cabos" <?php echo isset($currentPRR['unit']['Grand Mayan 1 Bedroom Penthouse Los Cabos']) && $currentPRR['unit']['Grand Mayan 1 Bedroom Penthouse Los Cabos'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Grand Mayan 1 Bedroom Penthouse San José del Cabo			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Mayan Studio Penthouse Los Cabos" <?php echo isset($currentPRR['unit']['Grand Mayan Studio Penthouse Los Cabos']) && $currentPRR['unit']['Grand Mayan Studio Penthouse Los Cabos'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Grand Mayan Studio Penthouse San José del Cabo		</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Mayan 2 Bedroom Suite Los Cabos" <?php echo isset($currentPRR['unit']['Grand Mayan 2 Bedroom Suite Los Cabos']) && $currentPRR['unit']['Grand Mayan 2 Bedroom Suite Los Cabos'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Grand Mayan 2 Bedroom Suite Los Cabos			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Mayan 1 Bedroom Suite Los Cabos" <?php echo isset($currentPRR['unit']['Grand Mayan 1 Bedroom Suite Los Cabos']) && $currentPRR['unit']['Grand Mayan 1 Bedroom Suite Los Cabos'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Grand Mayan 1 Bedroom Suite Los Cabos			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Mayan Studio Los Cabos" <?php echo isset($currentPRR['unit']['Grand Mayan Studio Los Cabos']) && $currentPRR['unit']['Grand Mayan Studio Los Cabos'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Grand Mayan Studio Los Cabos			</label>
		</div>
        <!-- grand solmar -->
		 <div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Solmar Lands End Two Bedroom Penthouse Los Cabos" <?php echo isset($currentPRR['unit']['Grand Solmar Lands End Two Bedroom Penthouse Los Cabos']) && $currentPRR['unit']['Grand Solmar Lands End Two Bedroom Penthouse Los Cabos'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Grand Solmar Lands End 2 Bedroom Penthouse Cabo San Lucas</label>
		</div>
        <div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Solmar Lands End 1 Bedroom Grand Suite Los Cabos" <?php echo isset($currentPRR['unit']['Grand Solmar Lands End 1 Bedroom Grand Suite Los Cabos']) && $currentPRR['unit']['Grand Solmar Lands End 1 Bedroom Grand Suite Los Cabos'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Grand Solmar Lands End 1 Bedroom Grand Suite Cabo San Lucas	</label>
		</div>
		<div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Solmar Pacific Dunes 2 Bedroom Penthouse Pacific Coast Los Cabos" <?php echo isset($currentPRR['unit']['Grand Solmar Pacific Dunes 2 Bedroom Penthouse Pacific Coast Los Cabos']) && $currentPRR['unit']['Grand Solmar Pacific Dunes 2 Bedroom Penthouse Pacific Coast Los Cabos'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Grand Solmar Pacific Dunes 2 Bedroom Penthouse Pacific Coast Cabo San Lucas	</label>
		</div></div></div>
        
        <!-- grand moon -->
        <div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Moon Palace 4 Bedroom Villa Riviera Maya" <?php echo isset($currentPRR['unit']['Grand Moon Palace 4 Bedroom Villa Riviera Maya']) && $currentPRR['unit']['Grand Moon Palace 4 Bedroom Villa Riviera Maya'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Grand Moon Palace 4 Bedroom Villa Riviera Maya			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Grand Moon Palace 2 Bedroom Grand Presidential Suite Riviera Maya" <?php echo isset($currentPRR['unit']['Grand Moon Palace 2 Bedroom Grand Presidential Suite Riviera Maya']) && $currentPRR['unit']['Grand Moon Palace 2 Bedroom Grand Presidential Suite Riviera Maya'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Grand Moon Palace 2 Bedroom Grand Presidential Suite Riviera Maya			</label>
		</div>
        
        <!-- hacienda encantada -->
        <div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="4 Bedroom Villa Hacienda EncantadaLos Cabos Corridor" <?php echo isset($currentPRR['unit']['4 Bedroom Villa Hacienda EncantadaLos Cabos Corridor']) && $currentPRR['unit']['4 Bedroom Villa Hacienda EncantadaLos Cabos Corridor'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				4 Bedroom Villa Hacienda Encantada Los Cabos Corridor			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="3 Bedroom Villa Hacienda Encantada Los Cabos Corridor" <?php echo isset($currentPRR['unit']['3 Bedroom Villa Hacienda Encantada Los Cabos Corridor']) && $currentPRR['unit']['3 Bedroom Villa Hacienda Encantada Los Cabos Corridor'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				3 Bedroom Villa Hacienda Encantada Los Cabos Corridor			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" <?php echo isset($currentPRR['unit']['2 Bedroom Villa Hacienda Encantada Los Cabos Corridor']) && $currentPRR['unit']['2 Bedroom Villa Hacienda Encantada Los Cabos Corridor'] == "true" ? "checked" : ""; ?> class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="2 Bedroom Villa Hacienda Encantada Los Cabos Corridor" data-field-name="field_unit">
				2 Bedroom Villa Hacienda Encantada Los Cabos Corridor			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="4 Bedroom Vista Encantada Los Cabos Corridor" <?php echo isset($currentPRR['unit']['4 Bedroom Vista Encantada Los Cabos Corridor']) && $currentPRR['unit']['4 Bedroom Vista Encantada Los Cabos Corridor'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				4 Bedroom Vista Encantada Los Cabos Corridor			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="3 Bedroom Vista Encantada Los Cabos Corridor" <?php echo isset($currentPRR['unit']['3 Bedroom Vista Encantada Los Cabos Corridor']) && $currentPRR['unit']['3 Bedroom Vista Encantada Los Cabos Corridor'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				3 Bedroom Vista Encantada Los Cabos Corridor			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="2 Bedroom Vista Encantada Los Cabos Corridor" <?php echo isset($currentPRR['unit']['2 Bedroom Vista Encantada Los Cabos Corridor']) && $currentPRR['unit']['2 Bedroom Vista Encantada Los Cabos Corridor'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				2 Bedroom Vista Encantada Los Cabos Corridor			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="1 Bedroom Vista Encantada Los Cabos Corridor" <?php echo isset($currentPRR['unit']['1 Bedroom Vista Encantada Los Cabos Corridor']) && $currentPRR['unit']['1 Bedroom Vista Encantada Los Cabos Corridor'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				1 Bedroom Vista Encantada Los Cabos Corridor			</label>
		</div>
        
        <!-- cabo villas -->
        <div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Corazon Bayview 2 Bedroom Ocean View Suite Cabo San Lucas" <?php echo isset($currentPRR['unit']['Corazon Bayview 2 Bedroom Ocean View Suite Cabo San Lucas']) && $currentPRR['unit']['Corazon Bayview 2 Bedroom Ocean View Suite Cabo San Lucas'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Corazon Bayview 2 Bedroom Ocean View Suite Cabo San Lucas			</label>
		</div>
        
        <!-- villas group -->
        <div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Villa La Valencia Three Bedroom Penthouse Ocean Front Suite Los Cabos Corridor" <?php echo isset($currentPRR['unit']['Villa La Valencia Three Bedroom Penthouse Ocean Front Suite Los Cabos Corridor']) && $currentPRR['unit']['Villa La Valencia Three Bedroom Penthouse Ocean Front Suite Los Cabos Corridor'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Villa La Valencia Three Bedroom Penthouse Ocean Front Suite Los Cabos Corridor			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Villa La Valencia Three Bedroom Ocean View Los Cabos Corridor" <?php echo isset($currentPRR['unit']['Villa La Valencia Three Bedroom Ocean View Los Cabos Corridor']) && $currentPRR['unit']['Villa La Valencia Three Bedroom Ocean View Los Cabos Corridor'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Villa La Valencia Three Bedroom Ocean View Los Cabos Corridor			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Villa La Valencia Three Bedroom Ocean Front Lock off Unit Los Cabos Corridor" <?php echo isset($currentPRR['unit']['Villa La Valencia Three Bedroom Ocean Front Lock off Unit Los Cabos Corridor']) && $currentPRR['unit']['Villa La Valencia Three Bedroom Ocean Front Lock off Unit Los Cabos Corridor'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Villa La Valencia Three Bedroom Ocean Front Lock Off Unit Los Cabos Corridor			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Villa La Valencia Two Bedroom Suite Ocean View Los Cabos Corridor" <?php echo isset($currentPRR['unit']['Villa La Valencia Two Bedroom Suite Ocean View Los Cabos Corridor']) && $currentPRR['unit']['Villa La Valencia Two Bedroom Suite Ocean View Los Cabos Corridor'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Villa La Valencia Two Bedroom Suite Ocean View Los Cabos Corridor			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Villa La Valencia Two Bedroom Suite OceanFront Corridor Los Cabos" <?php echo isset($currentPRR['unit']['Villa La Valencia Two Bedroom Suite OceanFront Corridor Los Cabos']) && $currentPRR['unit']['Villa La Valencia Two Bedroom Suite OceanFront Corridor Los Cabos'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Villa La Valencia Two Bedroom Suite Ocean Front Corridor Los Cabos			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Villa La Valencia Two Bedroom Suite Pool View Los Cabos Corridor" <?php echo isset($currentPRR['unit']['Villa La Valencia Two Bedroom Suite Pool View Los Cabos Corridor']) && $currentPRR['unit']['Villa La Valencia Two Bedroom Suite Pool View Los Cabos Corridor'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Villa La Valencia Two Bedroom Suite Pool View Los Cabos Corridor			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Villa La Valencia One Bedroom Suite Ocean View Los Cabos Corridor" <?php echo isset($currentPRR['unit']['Villa La Valencia One Bedroom Suite Ocean View Los Cabos Corridor']) && $currentPRR['unit']['Villa La Valencia One Bedroom Suite Ocean View Los Cabos Corridor'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Villa La Valencia One Bedroom Suite Ocean View Los Cabos Corridor			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Villa La Valencia Junior Suite OceanView Corridor Los Cabos" <?php echo isset($currentPRR['unit']['Villa La Valencia Junior Suite OceanView Corridor Los Cabos']) && $currentPRR['unit']['Villa La Valencia Junior Suite OceanView Corridor Los Cabos'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Villa La Valencia Junior Suite Ocean View Los Cabos			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Villa La Valencia Deluxe Studio Ocean View Los Cabos Corridor" <?php echo isset($currentPRR['unit']['Villa La Valencia Deluxe Studio Ocean View Los Cabos Corridor']) && $currentPRR['unit']['Villa La Valencia Deluxe Studio Ocean View Los Cabos Corridor'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Villa La Valencia Deluxe Studio Ocean View Los Cabos Corridor			</label>
		</div><div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
						<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Villa La Valencia Deluxe Studio Pool View Los Cabos Corridor" <?php echo isset($currentPRR['unit']['Villa La Valencia Deluxe Studio Pool View Los Cabos Corridor']) && $currentPRR['unit']['Villa La Valencia Deluxe Studio Pool View Los Cabos Corridor'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Villa La Valencia Deluxe Studio Pool View Los Cabos Corridor			</label>
		</div>

		 <!-- amatte -->
		 <div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
				<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Villa Diamante San Miguel de Allende" <?php echo isset($currentPRR['unit']['Villa Diamante San Miguel de Allende']) && $currentPRR['unit']['Villa Diamante San Miguel de Allende'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Villa Diamante San Miguel de Allende</label>
		</div>
		<div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
				<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Master Suite San Miguel de Allende" <?php echo isset($currentPRR['unit']['Master Suite San Miguel de Allende']) && $currentPRR['unit']['Master Suite San Miguel de Allende'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Master Suite San Miguel de Allende</label>
		</div>
		<div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
				<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Junior Suite Amatte San Miguel de Allende" <?php echo isset($currentPRR['unit']['Junior Suite Amatte San Miguel de Allende']) && $currentPRR['unit']['Junior Suite Amatte San Miguel de Allende'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Junior Suite Amatte San Miguel de Allende</label>
		</div>
		<div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
				<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Junior Suite San Miguel de Allende" <?php echo isset($currentPRR['unit']['Junior Suite San Miguel de Allende']) && $currentPRR['unit']['Junior Suite San Miguel de Allende'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Junior Suite San Miguel de Allende</label>
		</div>
		<div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
				<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Junior Suite Deluxe San Miguel de Allende" <?php echo isset($currentPRR['unit']['Junior Suite Deluxe San Miguel de Allende']) && $currentPRR['unit']['Junior Suite Deluxe San Miguel de Allende'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Junior Suite Deluxe San Miguel de Allende</label>
		</div>
		<div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
				<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Deluxe Studio San Miguel de Allende" <?php echo isset($currentPRR['unit']['Deluxe Studio San Miguel de Allende']) && $currentPRR['unit']['Deluxe Studio San Miguel de Allende'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Deluxe Studio San Miguel de Allende</label>
		</div>
		

		<!-- Occidental Xcaret -->
		 <div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
				<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="One Bedroom Occidental Xcaret" <?php echo isset($currentPRR['unit']['One Bedroom Occidental Xcaret']) && $currentPRR['unit']['One Bedroom Occidental Xcaret'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				One Bedroom Occidental Xcaret</label>
		</div>
		<div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
				<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Two Bedroom Occidental Xcaret" <?php echo isset($currentPRR['unit']['Two Bedroom Occidental Xcaret']) && $currentPRR['unit']['Two Bedroom Occidental Xcaret'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Two Bedroom Occidental Xcaret</label>
		</div>
		<div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
				<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Junior Suite Occidental Xcaret" <?php echo isset($currentPRR['unit']['Junior Suite Occidental Xcaret']) && $currentPRR['unit']['Junior Suite Occidental Xcaret'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Junior Suite Occidental Xcaret</label>
		</div>
		<div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
				<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Studio Occidental Xcaret" <?php echo isset($currentPRR['unit']['Studio Occidental Xcaret']) && $currentPRR['unit']['Studio Occidental Xcaret'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Studio Occidental Xcaret</label>
		</div>

		<!-- Casa Del Mar -->
		 <div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
				<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="3 Bedroom Casa Del Mar" <?php echo isset($currentPRR['unit']['3 Bedroom Casa Del Mar']) && $currentPRR['unit']['3 Bedroom Casa Del Mar'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Three Bedroom Casa Del Mar</label>
		</div>
		<div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
				<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="2 Bedroom Casa Del Mar" <?php echo isset($currentPRR['unit']['2 Bedroom Casa Del Mar']) && $currentPRR['unit']['2 Bedroom Casa Del Mar'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Two Bedroom Casa Del Mar</label>
		</div>
		<div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
				<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="1 Bedroom Casa Del Mar" <?php echo isset($currentPRR['unit']['1 Bedroom Casa Del Mar']) && $currentPRR['unit']['1 Bedroom Casa Del Mar'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				One Bedroom Casa Del Mar</label>
		</div>
		<div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
				<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Junior Suite Casa Del Mar" <?php echo isset($currentPRR['unit']['Junior Suite Casa Del Mar']) && $currentPRR['unit']['Junior Suite Casa Del Mar'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Junior Suite Casa Del Mar</label>
		</div>

		<!--Marival -->
	
		<div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
				<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Four Bedroom Penthouse Distinct" <?php echo isset($currentPRR['unit']['Four Bedroom Penthouse Distinct']) && $currentPRR['unit']['Four Bedroom Penthouse Distinct'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Marival Four Bedroom Penthouse Distinct</label>
		</div>
		<div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
				<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Three Bedroom Villa Distinct" <?php echo isset($currentPRR['unit']['Three Bedroom Villa Distinct']) && $currentPRR['unit']['Three Bedroom Villa Distinct'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Marival Three Bedroom Villa Distinct</label>
		</div>
		<div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
				<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="Two Bedroom Lock Off Armony Suite Ocean View" <?php echo isset($currentPRR['unit']['Two Bedroom Lock Off Armony Suite Ocean View']) && $currentPRR['unit']['Two Bedroom Lock Off Armony Suite Ocean View'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Marival Two Bedroom Lock Off Armony Suite Ocean View</label>
		</div>
		<div class="jet-form__field-wrap checkboxes-wrap checkradio-wrap hidden">
				<label class="jet-form__field-label">
				<input type="checkbox" name="field_unit[]" class="jet-form__field checkboxes-field checkradio-field checkboxes-group-required" value="One Bedroom Lock Off Armony Suite Ocean View" <?php echo isset($currentPRR['unit']['One Bedroom Lock Off Armony Suite Ocean View']) && $currentPRR['unit']['One Bedroom Lock Off Armony Suite Ocean View'] == "true" ? "checked" : ""; ?> data-field-name="field_unit">
				Marival One Bedroom Lock Off Armony Suite Ocean View</label>
		</div>

		<!-- filed number of weeks available -->
		<div class="jet-form-row">
			<div class="jet-form-col jet-form-col-12  field-type-text  jet-form-field-container" data-field="number_of_weeks_available" data-conditional="false">
				<div class="jet-form__label">
					<span class="jet-form__label-text">Number of weeks available<span class="jet-form__required">*</span></span>
				</div>
				<input class="jet-form__field text-field " value="<?php echo testValor($currentPRR, 'number-of-weeks-available'); ?>" required="required" name="number_of_weeks_available" id="number_of_weeks_available" type="text" data-field-name="number_of_weeks_available">
			</div>
		</div>

		<!-- fin number_of_weeks_available -->


		<!-- submit your prr button -->
		<div class="jet-form-row jet-form-row--submit">
			<div class="jet-form-col jet-form-col-12  field-type-submit  jet-form-field-container" data-field="Submit" data-conditional="false">
				<div class="jet-form__submit-wrap">
					<button class="jet-form__submit submit-type-ajax" type="submit" id="btn_submit">Submit Your Preferred Rentals Requests</button>	
				</div>
			</div>
		</div>
		<!-- fin submit your prr button -->
        </form>

		<dialog id="confirmacion">
			<h3>Settings saved.</h3>
		</dialog>
		<div class="template template-popup-saved" id="popup-saved" style="display:none">
		<p>Saving, wait a moment please... </p>
		<img style=width:20px;height:20px;object-fit:fill;display:inline;margin-left:10px; src=https://vacationintervalsmanagement.com/wp-content/plugins/vima-service-layer-plugin-cliente/loader-circle.gif />
		</div>
		<script type="text/javascript">

			const normalizeId = (s) => String(s ?? "").trim().replace(/\s+/g, "_");
			const rowSelectorByUnit = (unitValue) => `tr.${CSS.escape(normalizeId(unitValue))}`;

			function markRowDirtyByInput(target) {
			const idRaw = target?.id ?? target?.target?.id;
			if (!idRaw) return;

			const iden = idRaw.replace(/^fee_/, "").replace(/^multiplier_/, "").replace(/^weeks_/, "");
			const row = document.getElementsByClassName(iden)[0];
			if (!row) return;

			const btnSync = row.querySelector('.sync_btn');
			if (btnSync) btnSync.innerHTML = "&#9744;";
			}

			function removeRowFromDataTable(table, unitValue) {
			const id = normalizeId(unitValue);
			const row = table.row(`tr.${CSS.escape(id)}`);
			if (row.any()) {
				row.remove().draw(false);
				return true;
			}
			return false;
			}

			function removeAllRowsByUnit(table, unitValue) {
			const id = normalizeId(unitValue);
			const rows = table.rows((idx, data, node) => node?.classList?.contains(id));
			if (rows.any()) rows.remove().draw(false);
			}

			document.addEventListener('DOMContentLoaded', () => {


				const filaHaCambiado = (target) => {
				//console.log(target);
				if(target.id){
					let iden = target.id.replace("fee_", ""). replace("multiplier_", "").replace("weeks_", "");
					//console.log(iden);
					const row = document.getElementsByClassName(iden)[0];
					//console.log(row);
					const btnSync = row.querySelector('.sync_btn');
					//console.log(btnSync);
					btnSync.innerHTML = "&#9744;";
					//console.log(btnSync);
				}else{
					let iden = target.target.id.replace("fee_", ""). replace("multiplier_", "").replace("weeks_", "");
					const row = document.getElementsByClassName(iden)[0];
					const btnSync = row.querySelector('.sync_btn');
					btnSync.innerHTML = "&#9744;";
				}

				
				
			}
				//make table datatable

				

				let checkboxesUnits = document.querySelectorAll('input[data-grupal]');
				let btnSubmit =  document.getElementById('btn_submit');

				btnSubmit.addEventListener('click', (evt) => {
					let msjHTML = document.getElementById('popup-saved').innerHTML;
					//let msjHTML = 'Saving, wait a moment please... ';
					evt.target.innerHTML = msjHTML;
				});


				Array.from(checkboxesUnits).forEach(checkGrupal => {

					let dataGrupal = JSON.parse(checkGrupal.dataset.grupal);
					let labelParentText = checkGrupal.parentElement.childNodes[2];
					
				
					//linea que quita los destinos del label
					//labelParentText.textContent +=  ',' + dataGrupal.iguales;
					let hijosEncontrados = [];
					dataGrupal.iguales.forEach(destino => {
						hijosEncontrados.push(buscarGrupalesHijos(dataGrupal.index+destino));
						
					});

					checkGrupal.addEventListener("change", (evt) => {
						
						let estadoSelected = evt.currentTarget.checked;
						hijosEncontrados.forEach(hijo => {
							hijo.checked = estadoSelected;
							
						});
					});
				});

				// Al cargar página
				crearResumenUnidades();
				let table = new DataTable('#tabla_prr', {
					order: [[1, 'desc']],
					responsive: true,
					pageLength: 12,
					lengthMenu: [5, 10, 20, 50, 100, 200],
					pagingType: 'full_numbers',
					columnDefs: [
						{ className: 'all', targets: [0,1] },
						{ className: 'none', targets: '_all' }
					],
					layout: {
						topStart: null,
						bottom: 'paging',
						bottomStart: null,
						bottomEnd: null
					},
					columns: [{ width: '80%' }, {width: '20%'}]
				});

				const tabla = document.querySelector('#tabla_prr');

				tabla.addEventListener('change', (e) => {
					//console.log("changing");

				if (e.target.classList.contains('fee') ||
					e.target.classList.contains('multi')) {

						
						let iden = e.target.classList[2].replace("multiplier_", "").replace("fee_", "");
						const row = e.target.closest('tr');
						if (!row) return;

						const feeInput   = row.querySelector('.fee');
						const multiInput = row.querySelector('.multi');
						const rentalInput = row.querySelector('.rental');

						const fee   = Number(feeInput?.value || 0);
						const multi = Number(multiInput?.value || 0);

						if (rentalInput) {
							//rentalInput.value = fee * multi;
							Array.from(document.getElementsByClassName("rental_"+iden)).forEach((elem) => {
								elem.value= fee + (fee * multi / 100);
							})
						}

						filaHaCambiado(e.target);
					}
				if(e.target.classList.contains('weeks')){
					filaHaCambiado(e.target);
				}

				
				//para propagar el valor del input en todas las copias que crea datatables responsive :V
						if(e.target.classList.contains('fee')){

							//console.log(e.target.classList[2]);
							Array.from(document.getElementsByClassName(e.target.classList[2])).forEach((elem) => {
								elem.value= e.target.value
							})
						}
						if(e.target.classList.contains('multi')){
							//console.log(e.target.classList[2]);
							Array.from(document.getElementsByClassName(e.target.classList[2])).forEach((elem) => {
								elem.value= e.target.value
							})
						}
					
						if(e.target.classList.contains('weeks')){
							//console.log(e.target.classList[2]);
							Array.from(document.getElementsByClassName(e.target.classList[2])).forEach((elem) => {
								elem.value= e.target.value
							})
						}
			
			});

			
			

				// Cada que hagan click en un checkbox
				document.querySelectorAll('input[name="field_unit[]"]').forEach(cb => {
				cb.addEventListener("change", (evt) => {
					const unitValue = evt.currentTarget.value;     // usa currentTarget, no cb
					const iden = normalizeId(unitValue);

					if (!evt.currentTarget.checked) {
					removeAllRowsByUnit(table, unitValue);
					return;
					}

					// IMPORTANT: antes de agregar, limpia cualquier duplicado existente
					removeAllRowsByUnit(table, unitValue);

					const unitName = evt.currentTarget.parentElement.textContent.trim();

					const rowNode = table
					.row.add([
						`<td class="unit_name_${iden}">${unitName}</td>`,
						`<td><button type="button" readonly class="sync_btn sync_${iden}">&nbsp; &#10003</button></td>`
					])
					.draw(false)
					.node();

					//$(rowNode).prependTo(table.table().body());
					rowNode.classList.add(iden, 'nueva_fila');
					rowNode.setAttribute('data-unit-value', unitValue);

					//document.getElementById('inicio_tabla').scrollIntoView();
				});
				});


				let inputsTabla = document.querySelectorAll("#resumen-unidades input, #resumen-unidades select");
					Array.from(inputsTabla).forEach((input) => {
						input.addEventListener("change", filaHaCambiado);
					});
			});


			const buscarGrupalesHijos = (selector) => {
				let checkboxesHijos = document.querySelector('input[value="'+selector+'"]');
				checkboxesHijos.parentElement.style.display= "none";
				return checkboxesHijos;
			}

			const mostrarLoaderGuardado = () => {

				document.getElementById('confirmacion').showModal();

			}

			// const ponerLoaderButtonSSubmit = evt => {
			// 	evt.preventDefault();
			// 	console.log(evt.target);
				
			
			// 	let btnSubmit = evt.target.querySelector('button[type="submit"]');
			// 	console.log(btnSubmit);
			
			// 	let loadingClon = document.cloneNode('loading');
			// 	btnSubmit.style.display = "none";
			// 	evt.target.replaceChild(loadingClon, btnSubmit);
			// }

			function crearResumenUnidades() {

				let DataUnidades =  <?php echo json_encode(get_user_meta(get_current_user_id(), "user_units_data", true)); ?>;

				//console.log(DataUnidades);
				// Actualizar lista
				const lista = document.getElementById("lista-unidades-seleccionadas");
				lista.innerHTML = "";

				document.querySelectorAll('input[name="field_unit[]"]').forEach(cb => {
				
					
				
					if (cb.checked) {

						if(!DataUnidades[cb.parentElement.textContent.trim()]){
							//return;

							//console.log("no hay data");
						}
						if(cb.classList.contains('repetido')){
							return;
							
						}

						//console.log(DataUnidades[cb.parentElement.textContent.trim()]);
						let synced = false;
						if(DataUnidades[cb.parentElement.textContent.trim()]){
							synced = true;
						}
						dataUnidadSeleccionada = DataUnidades[cb.parentElement.textContent.trim()] || {
							fee: "",
							multiplier: "20",
							rental: ""
						};

						let syncBtn = "";
							
							syncBtn = `
								<td style="min-width:150px;text-align:left;" class="unit_name_${cb.value.replaceAll(" ", "_")}" id="unit_name_${cb.value.replaceAll(" ", "_")}">${cb.parentElement.textContent.trim()}</td>
								<td  style="text-align:center;"><button type='button' readonly class="sync_btn sync_${cb.value.replaceAll(" ", "_")}">&#10003; </button></td>`;
						
							 
						

						
						const tr = document.createElement("tr");
						tr.setAttribute("data-unit-value", cb.value);
						tr.classList.add(cb.value.replaceAll(" ", "_"));
						tr.style.backgroundColor = "#f9f9f9";
						if(!synced){
							tr.style.backgroundColor = "#fcdea6ff";
						}
						tr.style.borderBottom = "1px solid #ddd";
						tr.innerHTML += syncBtn;
						lista.appendChild(tr);
					}
					
				});
			}

			

		</script>

		<script>

			

			document.addEventListener('click', function(event) {
				
				if (event.target && event.target.classList.contains('save_under')){
					event.preventDefault();
					let syncBtnEow = event.target.parentElement.parentElement.parentElement.parentElement.parentElement.previousSibling;
					let BtnEow = syncBtnEow.querySelector(".sync_btn");
					//BtnEow.innerText  = "Updating..";
					//console.log(BtnEow);
					BtnEow.click();
					
				}
				//alert(event.target.classList);
				if (event.target && event.target.classList.contains('sync_btn')) {
					const botonSync = event.target;
					const btnSyncHTML = botonSync.innerHTML;
					botonSync.innerHTML = '<div class="loader_sync"></div>';
					const fila = event.target.closest('tr');
					const unitValue = fila.getAttribute('data-unit-value').replaceAll(" ", "_");
					
					

					

					let feeAmount;
					let rentalMultiplier;
					let rentalRate;
					let unitName;

					feeAmount = fila.querySelector('[class^="fee_"]')?.value;
					rentalMultiplier = fila.querySelector('[class^="multiplier_"]')?.value;
					rentalRate = fila.querySelector('[class^="rental_"]')?.value;
					unitName = fila.querySelector('td:first-child')?.textContent;

					//console.log(unitName);


					if (!feeAmount || !rentalMultiplier || !rentalRate) {
						alert('Please fill in all fields before saving.');
						botonSync.innerHTML = btnSyncHTML;
						return;
					}

					jQuery.ajax({
						url: '/wp-admin/admin-ajax.php',
						type: 'POST',
						data: {
							action: 'save_unit_meta',
							unit: unitName,
							fee: feeAmount,
							multiplier: rentalMultiplier,
							rental: rentalRate
						},
						success: function(response) {
							//console.log("OK:", response);
							botonSync.innerHTML = "&#10003;";
						},
						error: function(xhr) {
							console.log("Status:", xhr.status);
							console.log("Response:", xhr.responseText);
						}
					});
				}
			});

			const actualizarRate = (value, id, tipo) => {
				
				//console.log({value, id, tipo});
				id = id.replace("fee_", "").replace("multiplier_", "");
				//console.log(id);

				let fee = document.getElementById("fee_"+id).value; 
				let multi = document.getElementById("multiplier_"+id).value; 
				let ratePercentage = multi / 100;
				let rental = fee * ratePercentage;
				//console.log(rental);
				//console.log("rental_"+id);
				//console.log(rentalPercentage);

				document.getElementById("rental_"+id).setAttribute("value", rental);

			}

			const mostarMensajeGuardarCambios = () => {
				document.getElementById("mensaje_summary").innerText = "You have unsaved changes. Please make sure to save your changes before continuing, otherwise your updates may be lost.";
			}

			
		</script>
    <?php
}
?>