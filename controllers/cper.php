<?php
require_once('models/mper.php');
$mper = new Mper();
$idper = isset($_REQUEST['idper']) ? $_REQUEST['idper'] : NULL;
$nomper = isset($_POST['nomper']) ? $_POST['nomper'] : NULL;
$ver = isset($_POST['ver']) ? $_POST['ver'] : 0;
$crear = isset($_POST['crear']) ? $_POST['crear'] : 0;
$editar = isset($_POST['editar']) ? $_POST['editar'] : 0;
$eliminar = isset($_POST['eliminar']) ? $_POST['eliminar'] : 0;
$act = isset($_POST['act']) ? $_POST['act'] : 1;
$ope = isset($_REQUEST['ope']) ? $_REQUEST['ope'] : NULL;
$datOne = NULL;

$mper->setIdper($idper);
if ($ope == "save") {
    $mper->setNomper($nomper);
    $mper->setVer($ver);
    $mper->setCrear($crear);
    $mper->setEditar($editar);
    $mper->setEliminar($eliminar);
    $mper->setAct($act);
    if (!$idper) $mper->save(); else $mper->edit();
}
if ($ope == "eli" && $idper) $mper->del();
if ($ope == "edi" && $idper) $datOne = $mper->getOne();
$datAll = $mper->getAll();
?>

