<?php
include("models/mlote.php"); // Asegurar que mlote existe
require_once("models/mprod.php");  // Corregido: mprod.php en lugar de mproducto.php

$mlote     = new Mlote();
$mprod = new Mprod(); // Corregido: Clase Mprod

$idlote  = isset($_REQUEST['idlote'])  ? $_REQUEST['idlote']  : NULL;
$idprod  = isset($_POST['idprod'])    ? $_POST['idprod']     : NULL;
$codlot  = isset($_POST['codlot'])     ? $_POST['codlot']     : NULL;
$fecing  = isset($_POST['fecing'])     ? $_POST['fecing']     : NULL;
$fecven  = isset($_POST['fecven'])     ? $_POST['fecven']     : NULL;
$cantini = isset($_POST['cantini'])    ? $_POST['cantini']    : NULL;
$cantact = isset($_POST['cantact'])    ? $_POST['cantact']    : NULL;
$cstuni  = isset($_POST['cstuni'])     ? $_POST['cstuni']     : NULL;
$ope     = isset($_REQUEST['ope'])     ? $_REQUEST['ope']     : NULL;

$dtOne   = NULL;

// Cargar productos para el select del modal
$prod = $mprod->getAll();

$mlote->setIdlote($idlote);

// ====================================================================
// GUARDAR O ACTUALIZAR
// ====================================================================
if ($ope == "SaVe" && $idlote) {
    // Es edición
    $mlote->setIdprod($idprod);
    $mlote->setCodlot($codlot);
    $mlote->setFecing($fecing);
    $mlote->setFecven($fecven);
    $mlote->setCantini($cantini);
    $mlote->setCantact($cantact);
    $mlote->setCstuni($cstuni ?? 0);
    $mlote->edit();

} elseif ($ope == "SaVe" && !$idlote) {
    // Es nuevo
    $mlote->setIdprod($idprod);
    $mlote->setCodlot($codlot);
    $mlote->setFecing($fecing);
    $mlote->setFecven($fecven);
    $mlote->setCantini($cantini);
    $mlote->setCantact($cantini);  // al crear, cantact = cantini
    $mlote->setCstuni($cstuni ?? 0);
    $mlote->save();
}

// ====================================================================
// ELIMINAR
// ====================================================================
if ($ope == "eLi" && $idlote) {
    $mlote->del();
}

// ====================================================================
// EDITAR (cargar datos en el modal)
// ====================================================================
if ($ope == "eDi" && $idlote) {
    $dtOne = $mlote->getOne();
}

// ====================================================================
// LISTA GENERAL
// ====================================================================
$dtAll = $mlote->getAll();
?>