<?php
/**
 * Plugin Name:       VIMA - PRR Busquedas
 * Plugin URI:        https://plugin-uri/
 * Description:       Plugin que solo se usa para gnerar el buscador de PRR en prrbusquedas/
 * Version:           beta.1.0
 * Author:            Dev OG
 * Author URI:        og.lopar711@gmail.com
 * License:           GPL v2 or later
 */

function normalizarTexto($texto) {

    $texto = str_ireplace(['Two', 'One', 'Three', 'Four'], ['2', '1', '3', '4'], $texto);
    $texto = preg_replace('/[^a-zA-Z0-9\s]/', '', $texto);
    //$texto = strtolower($texto);
    //$texto = str_replace('Estate', 'Estates', $texto);
    return $texto;
}

 function getUnidadesPRR() {
 
    
 
    return array(
        "4 Bedroom Empire Estate", //1
        "Four Bedroom Empire Estate East Cape",
        "Two Bedroom Empire Estate East Cape",
        "4 Bedroom Empire Estate", //1
        "4 Bedroom Estates",
        "3 Bedroom Estates",
        "2 Bedroom Estates",
        "1 Bedroom Estates",
        "Grand Luxxe 4 Bedroom Residence",
        "Grand Luxxe 3 Bedroom Loft",
        "Grand Luxxe 2 Bedroom Loft",
        "Grand Luxxe 3 Bedroom Spa Suite",
        "Grand Luxxe 2 Bedroom Spa Suite",
        "Grand Luxxe 2 Bedroom Villa",
        "Grand Luxxe 1 Bedroom Villa",
        "Grand Luxxe Junior Villa",
        "Grand Luxxe 2 Bedroom Suite",
        "Grand Luxxe 1 Bedroom Suite ",
        "Grand Luxxe Studio",
        "Grand Mayan 2 Bedroom Suite",
        "Grand Mayan 1 Bedroom Suite",
        "Grand Mayan Studio",
        "Corazon Bayview 2 Bedroom Ocean View Suite Los Cabos",
        "Grand Solmar Pacific Dunes 2 Bedroom Penthouse Pacific Coast",
        "Grand Solmar Lands End 1 Bedroom Grand Suite Los Cabos", //2
        "Villa La Valencia Three Bedroom Penthouse Ocean Front Suite Los Cabos Corridor",
        "Villa La Valencia Three Bedroom Ocean View Los Cabos Corridor",
        "Villa La Valencia Three Bedroom Ocean Front Lock off Unit Los Cabos Corridor",
        "Villa La Valencia Two Bedroom Suite Ocean View Los Cabos Corridor",
        "Villa La Valencia Two Bedroom Suite Ocean Front Los Cabos Corridor",
        "Villa La Valencia Two Bedroom Suite Pool View Los Cabos Corridor",
        "Villa La Valencia One Bedroom Suite Ocean View Los Cabos Corridor",
        "Villa La Valencia Junior Suite Ocean View Los Cabos Corridor",
        "Villa La Valencia Deluxe Studio Ocean View Los Cabos Corridor",
        "Villa La Valencia Deluxe Studio Pool View Los Cabos Corridor",
        "Villa La Valencia Two Bedroom Suite Ocean-Front Los Cabos Corridor",
        "Villa la Valencia Three Bedroom Ocean Front Lock Off Unit Los Cabos",
        "4 Bedroom Villa Hacienda Encantada Los Cabos Corridor", //3
        "3 Bedroom Villa Hacienda Encantada Los Cabos Corridor",
        "2 Bedroom Villa Hacienda Encantada Los Cabos Corridor",
        "4 Bedroom Vista Encantada Los Cabos Corridor",
        "3 Bedroom Vista Encantada Los Cabos Corridor",
        "2 Bedroom Vista Encantada Los Cabos Corridor",
        "1 Bedroom Vista Encantada Los Cabos Corridor",
        "Mayan Palace Two Bedroom Suite",
        "Mayan Palace Two Bedroom Suite",
        "Mayan Palace One Bedroom Suite",
        "Mayan Palace One Bedroom Suite",
        "Grand Moon Palace 4 Bedroom Villa",
        "Grand Moon Palace 2 Bedroom Grand Presidential Suite",
        "Villa Diamante San Miguel de Allende",
        "Master Suite San Miguel de Allende",
        "Junior Suite Amatte San Miguel de Allende",
        "Junior Suite San Miguel de Allende",
        "Junior Suite Deluxe San Miguel de Allende",
        "Deluxe Studio San Miguel de Allende",
        "One Bedroom Occidental Xcaret",
        "Two Bedroom Occidental Xcaret",
        "Junior Suite Occidental Xcaret",
        "Studio Occidental Xcaret"

        );
}

function crearSelect($unidades){
    ?>
    <style>
        .unidad_form_prr select{
            min-height: 50px;
            background-color: #DEBB27;
            color: #000;
            text-align: center;
        }
        .unidad_form_prr select option{
           margin-top: 10px;
        }

        .unidad_form_prr input{
            display: block;
            margin:auto;
            margin-top:30px;
            color: #DEBB27;
            background-color: #000;
            padding: 5px 10px 5px 10px;
            cursor:pointer;
            width: 230px;
        }
        .emails{
            color: #000;
            font-size: 14px;
            padding: 7px;
        }
        .titulo{
            font-size: 18px;
            display: block;
            margin:auto;
        }
    </style>
    <?php
    $selectString = "<div class='elementor-widget-container'><form method='POST' class='unidad_form_prr'>";
    $selectString .= "<select name='prr_unidad_buscar'>";

    foreach ($unidades as $unidad){
        $isSelected = "";
        if(isset($_POST["prr_unidad_buscar"]) and $_POST["prr_unidad_buscar"] == $unidad){
            $isSelected = "selected";
        }
      //  var_dump($isSelected);

        $selectString .= "<option value='".$unidad."' ".$isSelected." >".$unidad."</option>";
    }

    $selectString .= "</select>";

    $selectString .= "<input type='submit' value='Search'>";

    $selectString .= "</form></div>";
   // echo "<pre>".print_r($selectString, true). "</pre>";
    return $selectString;

}

function init(){
    $unidades = getUnidadesPRR();
    $select = crearSelect($unidades);
    //echo "<pre>".print_r($select, true). "</pre>";
   // echo "<pre>".print_r($unidades, true). "</pre>";
   echo $select;
}




 add_shortcode( "busquedasprr", function (){

    init();


    $unidadBuscar = "4 Bedroom Empire Estate Nuevo Vallarta";
    if(isset($_POST['prr_unidad_buscar'])){
        $unidadBuscar = $_POST['prr_unidad_buscar'];
        
    }
    
    //var_dump($unidadBuscar);
    $abs_path= __FILE__;
    $get_path=explode('wp-content',$abs_path);
    $path=$get_path[0].'wp-load.php';
    require_once($path);
    
/////////////////////////////
global $wpdb;

// Consulta SQL personalizada
$query = $wpdb->prepare("
    SELECT p.*
    FROM {$wpdb->posts} p
    INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
    WHERE p.post_type = %s
      AND p.post_status = 'publish'
      AND pm.meta_key = 'unit'
      AND pm.meta_value LIKE %s
      AND p.ID IN (
          SELECT MAX(p1.ID)
          FROM {$wpdb->posts} p1
          WHERE p1.post_type = %s
            AND p1.post_status = 'publish'
            AND p1.post_author = p.post_author
      )
    ORDER BY p.post_date DESC
", 'preferred-rentals', '%' . $wpdb->esc_like($unidadBuscar) . '%', 'preferred-rentals');

//echo "<pre>" . print_r($wpdb->esc_like($unidadBuscar), true) . "</pre>";
$posts = $wpdb->get_results($query);

$unit_users = array();

// Iterar sobre los resultados para obtener los correos electrónicos de los autores
foreach ($posts as $post) {
    //echo "<pre>" . print_r($post->ID, true) . "</pre>";
    $author_id = $post->post_author;
    $author_email = get_the_author_meta('user_email', $author_id);

    $unit_users[] = $author_email;
}

// Mostrar resultados
//echo "<pre>" . print_r($unit_users, true) . "</pre>";
echo "<br />";
 echo "<h2 class='titulo'>Partners who selected this unit in their PRR settings</h2>";
 echo "<br />";
$unidadNorm = normalizarTexto($unidadBuscar);
foreach ($unit_users as $user){
    echo '<h3 class="emails">'.$user.'<small><i></i></small></h3>';
    $userID = get_user_by("email", $user)->ID;
    $data = get_user_meta(get_user_by("email", $user)->ID, "user_units_data", true);
     if (is_array($data)) {
        if(is_array($data[$unidadNorm])){
            array_walk($data[$unidadNorm], function ($value, $key) {
                echo "{$key}: {$value} | ";
            });
        }
        
    }
    
}

echo "<br />";
echo "<br />";
echo "<hr />";

function get_users_with_agreements_count_without_preferred_rentals( $statuses = array('publish') ) {
    global $wpdb;

    if (empty($statuses)) {
        $statuses = array('publish');
    }

    $ph = implode(',', array_fill(0, count($statuses), '%s'));

    $sql = "
        SELECT 
            u.ID,
            u.user_login,
            u.user_email,
            YEAR(u.user_registered) AS anio_registro,
            COUNT(a.ID) AS total_agreements
        FROM {$wpdb->users} u
        INNER JOIN {$wpdb->posts} a
            ON (a.post_author = u.ID 
                AND a.post_type = %s 
                AND a.post_status IN ($ph))
        LEFT JOIN {$wpdb->posts} pr
            ON (pr.post_author = u.ID 
                AND pr.post_type = %s 
                AND pr.post_status IN ($ph))
        WHERE pr.ID IS NULL
        GROUP BY u.ID
    ";

    $params = array_merge(
        array('agreement'),
        $statuses,
        array('preferred-rentals'),
        $statuses
    );

    $prepared = $wpdb->prepare($sql, $params);

    return $wpdb->get_results($prepared);
}



$usuarios = get_users_with_agreements_count_without_preferred_rentals(array('publish'));
if (!empty($usuarios)) {

    echo "<h2>Partners who have not created a post of preferred-rentals:</h2>";
    //create a table to display
    echo "<table>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Año de Registro</th><th>Total Agreements</th></tr>";
    foreach ($usuarios as $user) {
        echo "<tr>";
        echo "<td>{$user->ID}</td>";
        echo "<td>{$user->user_login}</td>";
        echo "<td>{$user->user_email}</td>";
        echo "<td>{$user->anio_registro}</td>";
        echo "<td>{$user->total_agreements}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Todos los usuarios han creado al menos un post de este tipo.";
}
 } );
?>