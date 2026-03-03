<?php
header('Content-Type: application/json');
require_once('../models/conexion.php');

$response = ['success' => false, 'message' => 'Error', 'pages' => []];

$idper = isset($_GET['idper']) ? intval($_GET['idper']) : 0;
if ($idper <= 0) {
    $response['message'] = 'Perfil inválido';
    echo json_encode($response);
    exit;
}

try {
    $sql = "SELECT p.idpag, p.nompag, p.ruta, p.icono, p.orden, p.act,
                   (SELECT COUNT(*) FROM pxp WHERE idper = :idper AND idpag = p.idpag) AS access
            FROM pagina p
            WHERE p.act = 1
            ORDER BY p.orden ASC";
    $modelo = new Conexion();
    $conexion = $modelo->get_conexion();
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':idper', $idper);
    $stmt->execute();
    $pages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['message'] = 'OK';
    $response['pages'] = $pages;
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
?>