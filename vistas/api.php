<?php
// Datos
$token = 'apis-token-9398.CgMfcskFz1G61BmjTE6j4lt8mXwqhxwL';
$dni = '71798440';

// Iniciar llamada a API
$curl = curl_init();

// Buscar dni
curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.apis.net.pe/v2/reniec/dni?numero=' . $dni,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => 0,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 2,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        'Referer: https://apis.net.pe/consulta-dni-api',
        'Authorization: Bearer ' . $token
    ),
));

$response = curl_exec($curl);

curl_close($curl);
// Datos listos para usar
$persona = json_decode($response);
var_dump($persona);
