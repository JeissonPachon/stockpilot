<?php
require_once('models/mpag.php');
$mpag = new Mpag();
$idpag = isset($_REQUEST['idpag']) ? $_REQUEST['idpag']:NULL;
$nompag = isset($_POST['nompag']) ? $_POST['nompag']:NULL;
$despag = isset($_POST['despag']) ? $_POST['despag']:NULL;
$fec_crea = isset($_POST['fec_crea']) ? $_POST['fec_crea']:NULL;
$fec_actu = isset($_POST['fec_actu']) ? $_POST['fec_actu']:NULL;
$act = isset($_POST['act']) ? $_POST['act']:NULL;
$ope = isset($_REQUEST['ope']) ? $_REQUEST['ope']:NULL;
$datOne = NULL;
$mpag->setIdpag($idpag);

if($ope == "save"){
    $mpag->setNompag($nompag);
    $mpag->setFec_crea($fec_crea);
    $mpag->setFec_actu($fec_actu);
    $mpag->setAct($act);
    if(!$idpag) $mpag->save(); else $mpag->edit();
}

// NO ejecutar eliminación aquí - usar endpoint AJAX solo
// if($ope =="eli" && $idpag) $mpag->del();

if($ope =="edi" && $idpag) $datOne = $mpag->getOne();
$datAll = $mpag->getAll();

?>

