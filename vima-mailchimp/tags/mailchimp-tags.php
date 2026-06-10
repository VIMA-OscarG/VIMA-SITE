<?php 
require_once('vendor/autoload.php');


//modificacion para asegurar que los tags están creados antes de intentar asignarlos



function hacerPing(){
    $mailchimp = new \MailchimpMarketing\ApiClient();
    $mailchimp->setConfig([
        'apiKey' => '59aeee4a956fc222de1ff1400c6cadc8',
        'server' => 'us5'
    ]);

    $response = $mailchimp->ping->get();
    echo "<pre>".print_r($response, true). "</pre>";
}

function getTagsByUserEmail($userEmail){

    $client = new MailchimpMarketing\ApiClient();
    $client->setConfig([
        'apiKey' => '59aeee4a956fc222de1ff1400c6cadc8',
        'server' => 'us5',
    ]);

    $response = $client->lists->getListMemberTags("6b17adc54b",  md5($userEmail));
    echo "<pre>".print_r($response, true). "</pre>";
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
    return $listaOrdenUnidadesClub;
}
function construitArrayTodasLasUnits(){
    $arr = getOrdenamientoUnitsClubsServiceLayer();
    // $todasUnits = array_map(function($value) {
    //     return $value;
    // }, $arr['data']['units-list']);
    $todasUnits = array_merge(
        $arr['data']['units-list']['Vidanta'],
        $arr['data']['units-list']['Hacienda Encantada'],
        $arr['data']['units-list']['Cabo Villas'],
        $arr['data']['units-list']['Villa Group'],
        $arr['data']['units-list']['Grand Solmar'],
        $arr['data']['units-list']['Palace Resorts']
    );

   $todasUnitsTags = array_map(function($value){
    return ["name" => $value, "status" => 'inactive'];
   }, $todasUnits); 
   return $todasUnitsTags;

}

function setTagsByUserEmail($userEmail, $tags){
    // === CONFIG ===
    $apiKey = '59aeee4a956fc222de1ff1400c6cadc8';
    $server = 'us5';
    $listId = '6b17adc54b';

    // Cliente SDK
    $client = new MailchimpMarketing\ApiClient();
    $client->setConfig([
        'apiKey' => $apiKey,
        'server' => $server,
    ]);

    $arrTodasUnits = construitArrayTodasLasUnits();

    $tagNames = array_map('trim', explode(",", $tags));
    $tagNames = array_values(array_filter($tagNames, fn($t) => $t !== ''));

    $arrTags = [];
    foreach ($tagNames as $tag){
        $needle = array_search($tag, array_column($arrTodasUnits, 'name'));
        if ($needle !== false && isset($arrTodasUnits[$needle])) {
            $arrTodasUnits[$needle]['status'] = 'active';
        } else {
            $arrTodasUnits[] = [
                'name' => $tag,
                'status' => 'active'
            ];
        }
        $arrTags[] = ["name" => $tag, "status" => "active"];
    }

    try{

        $response = $client->lists->updateListMemberTags(
            $listId,
            md5($userEmail),
            [ "tags" => $arrTodasUnits ]
        );

        return json_encode([
            "response" => $response,
            "status" => "se actualizaron los tags en mailchimp",
            "tags" => $arrTodasUnits
        ]);

    }catch(Exception $e){
        return [
            "error" => $e->getMessage(),
            "desc" => "no se pudieron actualizar las tags"
        ];
    }
}


function getListDetails(){
    $client = new MailchimpMarketing\ApiClient();
    $client->setConfig([
        'apiKey' => '59aeee4a956fc222de1ff1400c6cadc8',
        'server' => 'us5',
    ]);
    $response = $client->lists->getList("6b17adc54b");
    echo "<pre>".print_r($response, true). "</pre>";
}

 function getLists(){
    $client = new MailchimpMarketing\ApiClient();
    $client->setConfig([
        'apiKey' => '59aeee4a956fc222de1ff1400c6cadc8',
        'server' => 'us5',
    ]);
    $response = $client->lists->getAllLists();
    echo "<pre>".print_r($response, true). "</pre>";
}

function getContactList(){
    $client = new MailchimpMarketing\ApiClient();
    $client->setConfig([
        'apiKey' => '59aeee4a956fc222de1ff1400c6cadc8',
        'server' => 'us5',
    ]);
    $response = $client->lists->getListMembersInfo("6b17adc54b");
    echo "<pre>".print_r($response, true). "</pre>";
}

function getContactListByEmail($email){
    $client = new MailchimpMarketing\ApiClient();
    $client->setConfig([
        'apiKey' => '59aeee4a956fc222de1ff1400c6cadc8',
        'server' => 'us5',
    ]);
    $response = $client->lists->getListMember("6b17adc54b", md5($email));
    echo "<pre>".print_r($response, true). "</pre>";
}


if(isset($_POST['update_tags_mailchimp'])){
    echo "update TAGS MAILCHIMP <br>";
    if(isset($_POST['tags_list'])){
        echo json_encode(["result" => setTagsByUserEmail($_POST['user_email'], $_POST['tags_list']), "post_tags" => $_POST['tags_list']]);
    }
}


if(isset($_GET['debug']) && $_GET['debug'] == '711debug'){
    // echo "PING <br>";
    // hacerPing();
   // echo "<br> LIST DETAILS <br>";
   // getListDetails();
   // echo "<br> LISTS <br>";
   // getLists();
     echo "<br> GET TAGS BY USER EMAIL <br>";
     getTagsByUserEmail('apyr84life@yahoo.com');
     echo "<br> CONSTRUIR ARRAY DE TODAS LAS UNITS <br>";
     echo json_encode(construitArrayTodasLasUnits());
    // echo "<br> GET CONTACT LIST <br>";
    // getContactList();
     echo "<br> GET CONTACT LIST BY EMAIL <br>";
     getContactListByEmail('apyr84life@yahoo.com');
    // echo "<br> SET TAGS BY USER EMAIL <br>";
   // echo json_encode(setTagsByUserEmail('apyr84life@yahoo.com', ["tags-uno", "tags-dos", "otra"]));
    //getTagsByUserEmail('og.lopar711@gmail.com');
    //setTagsByUserEmail('og.lopar711@gmail.com', ["tags-uno", "tags-dos", "otra"]);
}

?>