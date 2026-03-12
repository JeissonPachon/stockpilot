<?php
require_once('models/minv.php');

$minv = new MInv();

// ✅ Obtener datos de la sesión
$idper = isset($_SESSION['idper']) ? $_SESSION['idper'] : NULL;
$idemp = isset($_SESSION['idemp']) ? $_SESSION['idemp'] : NULL;

// Validar permisos básicos
if(!$idper || ($idper != 1 && !$idemp)){
    echo "<script>alert('No tienes permisos para acceder'); window.location.href='home.php';</script>";
    exit;
}

$idinv = isset($_REQUEST['idinv']) ? $_REQUEST['idinv']:NULL;
$idprod = isset($_POST['idprod']) ? $_POST['idprod']:NULL;
$idubi = isset($_POST['idubi']) ? $_POST['idubi']:NULL;
$cant = isset($_POST['cant']) ? $_POST['cant']:NULL;
$fec_crea = date('Y-m-d H:i:s');
$fec_actu = date('Y-m-d H:i:s');

$ope = isset($_REQUEST['ope']) ? $_REQUEST['ope']:NULL;
$datOne = NULL;

// ✅ RESTRICCIÓN POR PERFIL
// Perfil 3 (Empleado) = SOLO LECTURA
if($idper == 3 && in_array($ope, ['save', 'eli'])){
    echo "<script>alert('No tienes permisos para realizar esta acción'); window.location.href='home.php?pg=$pg';</script>";
    exit;
}

$minv->setIdinv($idinv);

if($ope == "save"){
    $minv->setIdprod($idprod);
    $minv->setIdubi($idubi);
    $minv->setCant($cant);
    $minv->setFec_crea($fec_crea);
    $minv->setFec_actu($fec_actu);
    
    // ✅ ASIGNAR AUTOMÁTICAMENTE EL idemp
    $minv->setIdemp($idemp);
    
    // Guardar o actualizar
    if ($idinv) {
        $minv->upd();
        echo "<script>window.location.href = 'home.php?pg=$pg&msg=updated';</script>";
        exit;
    } else {
        $minv->save();
        echo "<script>window.location.href = 'home.php?pg=$pg&msg=saved';</script>";
        exit;
    }
}

if ($ope == "eli" && $idinv) {
    $minv->setIdinv($idinv);
    $lotesCount = $minv->countLotesByInv($idinv);
    if ($lotesCount > 0) {
        $_SESSION['mensaje'] = "No se puede eliminar: hay lotes asociados a este producto y ubicacion.";
        $_SESSION['tipo_mensaje'] = "warning";
        echo "<script>window.location.href = 'home.php?pg=$pg';</script>";
        exit;
    }

    $minv->del();
    echo "<script>window.location.href = 'home.php?pg=$pg&msg=deleted';</script>";
    exit;
}

if($ope =="edi" && $idinv) {
    $tmp = $minv->getOne();
    $datOne = $tmp ? $tmp : null;
}


$datAll = $minv->getStockResumen();
$datProd = $minv->getAllProd();  // ✅ Productos de la empresa
$datUbi = $minv->getAllUbi();    // ✅ Ubicaciones de la empresa
$lotesIndexados = $minv->getLotesPorInventario(); // ✅ Lotes agrupados por producto+ubicación
?>
