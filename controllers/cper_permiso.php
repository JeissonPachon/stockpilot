<?php
// Este archivo maneja el cambio de permisos de forma separada sin incluir la vista
require_once('../models/conexion.php');
require_once('../models/mper.php');

$idper = isset($_GET['idper']) ? $_GET['idper'] : NULL;
$permiso = isset($_GET['permiso']) ? $_GET['permiso'] : NULL;
$valor = isset($_GET['valor']) ? $_GET['valor'] : NULL;

if ($permiso && $valor !== NULL && $idper) {
    $mper = new Mper();
    $mper->setIdper($idper);
    $datOne = $mper->getOne();
    
    if ($datOne) {
        $mper->setNomper($datOne[0]['nomper']);
        $mper->setVer($permiso === 'ver' ? $valor : $datOne[0]['ver']);
        $mper->setCrear($permiso === 'crear' ? $valor : $datOne[0]['crear']);
        $mper->setEditar($permiso === 'editar' ? $valor : $datOne[0]['editar']);
        $mper->setEliminar($permiso === 'eliminar' ? $valor : $datOne[0]['eliminar']);
        $mper->setAct($datOne[0]['act']);
        $mper->edit();
    }
}

// Redirigir a la página de perfiles
header("Location: ../home.php?pg=per");
exit;
?>
