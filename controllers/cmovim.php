<?php
require_once('models/mmovim.php');
require_once('models/mkard.php');
require_once('models/conexion.php');

$mmov = new Mmov();
$mkard = new Mkard();

$idemp_session = isset($_SESSION['idemp']) ? $_SESSION['idemp'] : null;
$idper_session = isset($_SESSION['idper']) ? $_SESSION['idper'] : null;

// Periodo seleccionado
$idkar = isset($_REQUEST['idkar']) ? (int)$_REQUEST['idkar'] : 0;

// Kardex disponibles (incluye cerrados)
$datKardex = [];
try {
    $modelo = new Conexion();
    $conexion = $modelo->get_conexion();

    if ($idper_session == 1 && !$idemp_session) {
        $sql_kar = "SELECT idkar, anio, mes, idemp, cerrado FROM kardex ORDER BY anio DESC, mes DESC";
        $result = $conexion->prepare($sql_kar);
        $result->execute();
    } else {
        $sql_kar = "SELECT idkar, anio, mes, idemp, cerrado FROM kardex WHERE idemp = :idemp ORDER BY anio DESC, mes DESC";
        $result = $conexion->prepare($sql_kar);
        $result->bindParam(':idemp', $idemp_session);
        $result->execute();
    }
    $datKardex = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $datKardex = [];
}

// Movimientos del periodo seleccionado
$datAll = [];
if ($idkar) {
    $datAll = $mmov->getByKardex($idkar, $idper_session == 1 ? null : $idemp_session);
}

// Resumen del periodo
$resumenMov = [
    'entradas' => 0,
    'salidas' => 0,
    'totalEntradas' => 0.0,
    'totalSalidas' => 0.0,
    'saldoCant' => 0.0,
    'saldoVal' => 0.0,
];

if (!empty($datAll)) {
    foreach ($datAll as $row) {
        $cant = (float)($row['cantmov'] ?? 0);
        $val = (float)($row['valmov'] ?? 0);
        $total = $cant * $val;
        if ((int)($row['tipmov'] ?? 0) === 1) {
            $resumenMov['entradas'] += $cant;
            $resumenMov['totalEntradas'] += $total;
        } else {
            $resumenMov['salidas'] += $cant;
            $resumenMov['totalSalidas'] += $total;
        }
    }
    $resumenMov['saldoCant'] = $resumenMov['entradas'] - $resumenMov['salidas'];
    $resumenMov['saldoVal'] = $resumenMov['totalEntradas'] - $resumenMov['totalSalidas'];
}
?>