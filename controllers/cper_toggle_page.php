<?php
header('Content-Type: application/json');
require_once('../models/conexion.php');

$response = ['success' => false, 'message' => 'Error', 'access' => null];

$idper = isset($_POST['idper']) ? intval($_POST['idper']) : 0;
$idpag = isset($_POST['idpag']) ? intval($_POST['idpag']) : 0;

if ($idper <= 0 || $idpag <= 0) {
    $response['message'] = 'Parámetros inválidos';
    echo json_encode($response);
    exit;
}

try {
    $modelo = new Conexion();
    $conexion = $modelo->get_conexion();

    // Verificar si existe
    $checkSql = "SELECT COUNT(*) AS cnt FROM pxp WHERE idper = :idper AND idpag = :idpag";
    $stmt = $conexion->prepare($checkSql);
    $stmt->bindParam(':idper', $idper);
    $stmt->bindParam(':idpag', $idpag);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $exists = ($row && $row['cnt'] > 0);

    if ($exists) {
        // eliminar
        $delSql = "DELETE FROM pxp WHERE idper = :idper AND idpag = :idpag";
        $d = $conexion->prepare($delSql);
        $d->bindParam(':idper', $idper);
        $d->bindParam(':idpag', $idpag);
        $d->execute();
        $response['success'] = true;
        $response['message'] = 'Acceso revocado';
        $response['access'] = 0;
    } else {
        // insertar
        $insSql = "INSERT INTO pxp (idper, idpag) VALUES (:idper, :idpag)";
        $i = $conexion->prepare($insSql);
        $i->bindParam(':idper', $idper);
        $i->bindParam(':idpag', $idpag);
        $i->execute();
        $response['success'] = true;
        $response['message'] = 'Acceso concedido';
        $response['access'] = 1;
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
?>