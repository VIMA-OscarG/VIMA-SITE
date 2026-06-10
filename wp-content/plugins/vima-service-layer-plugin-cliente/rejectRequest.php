<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['status' => 'error', 'message' => 'Error al decodificar los datos JSON']);
        exit;
    }
    $requestID = $data['requestid'];
    $partnerEmail = $data['partneremail']; 
    $apiResponse = rejectRequestCall($requestID, $partnerEmail);

    $response = [
        'status' => 'success',
        'message' => 'Request declined',
        'data' => $apiResponse
    ];

    echo json_encode($response);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
}

function rejectRequestCall($requestID, $partnerEmail) {
    
    $api_url = 'https://www.vacationintervalsmanagement.com/service-layer/api/v1/vima/service-page-request-reject/' . $requestID;
    $post_data = [
        'partneremail' => $partnerEmail
    ];
    $json_data = json_encode($post_data);

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($json_data),
        'Authorization: Basic ' . base64_encode("anton@vacationintervalsmanagement.com:VimaServiceLayer##2023-2024##!?")
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo json_encode(['status' => 'error', 'message' => 'Error en la solicitud cURL: ' . curl_error($ch)]);
    }

    curl_close($ch);
    return $response;
}
?>


