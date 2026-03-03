<?php
header('Content-Type: application/json');
require_once('../models/mpag.php');

$response = ['success' => false, 'message' => 'Error'];

$idpag = isset($_REQUEST['idpag']) ? intval($_REQUEST['idpag']) : 0;

if ($idpag <= 0) {
    $response['message'] = 'Página inválida';
    echo json_encode($response);
    exit;
}

try {
    $mpag = new Mpag();
    $mpag->setIdpag($idpag);
    $mpag->del();
    
    $response['success'] = true;
    $response['message'] = 'Página eliminada correctamente';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
?>