<?php 
require_once('models/mmovim.php');
require_once('models/mprod.php');
require_once('models/mubi.php');
require_once('models/mkard.php');
require_once('models/maud.php');

file_put_contents(__DIR__ . '/../debug_log.txt', "cmovim.php loaded at " . date('Y-m-d H:i:s') . "\nRequest: " . json_encode($_REQUEST) . "\n", FILE_APPEND);

$mmov = new Mmov();
$mprod = new Mprod();
$mubi = new Mubi();
$mkard = new Mkard();

$idemp_session = isset($_SESSION['idemp']) ? $_SESSION['idemp'] : NULL;
$idusu_session = isset($_SESSION['idusu']) ? $_SESSION['idusu'] : NULL;

file_put_contents(__DIR__ . '/../debug_log.txt', "cmovim.php loaded at " . date('Y-m-d H:i:s') . "\nRequest: " . json_encode($_REQUEST) . "\nSession IDEMP: " . var_export($idemp_session, true) . "\n", FILE_APPEND);
$idper_session = isset($_SESSION['idper']) ? $_SESSION['idper'] : NULL;

// Parámetros del formulario
$idmov    = isset($_REQUEST['idmov']) ? $_REQUEST['idmov'] : NULL;
$idemp    = isset($_POST['idemp']) ? $_POST['idemp'] : $idemp_session;
$idkar    = isset($_POST['idkar']) ? $_POST['idkar'] : NULL;
$idprod   = isset($_POST['idprod']) ? $_POST['idprod'] : NULL;
$idubi    = isset($_POST['idubi']) ? $_POST['idubi'] : NULL;
$fecmov   = isset($_POST['fecmov']) ? $_POST['fecmov'] : date('Y-m-d');
$tipmov   = isset($_POST['tipmov']) ? $_POST['tipmov'] : NULL;
$cantmov  = isset($_POST['cantmov']) ? $_POST['cantmov'] : NULL;
$valmov   = isset($_POST['valmov']) ? $_POST['valmov'] : NULL;
$costprom = isset($_POST['costprom']) ? $_POST['costprom'] : NULL;
$docref   = isset($_POST['docref']) ? $_POST['docref'] : NULL;
$obs      = isset($_POST['obs']) ? $_POST['obs'] : NULL;

$ope = isset($_REQUEST['ope']) ? $_REQUEST['ope'] : NULL;
$datOne = NULL;

// Obtener listas para selectores FILTRADAS POR EMPRESA
$datProductos = [];
$datUbicaciones = [];
$datKardex = [];

if($idemp_session){
    // Filtrar productos por empresa
    $sql_prod = "SELECT idprod, codprod, nomprod FROM producto WHERE idemp = :idemp AND act = 1 ORDER BY nomprod";
    $modelo = new Conexion();
    $conexion = $modelo->get_conexion();
    $result = $conexion->prepare($sql_prod);
    $result->bindParam(':idemp', $idemp_session);
    $result->execute();
    $datProductos = $result->fetchAll(PDO::FETCH_ASSOC);
    
    // Filtrar ubicaciones por empresa
    $sql_ubi = "SELECT idubi, nomubi, codubi FROM ubicacion WHERE idemp = :idemp AND act = 1 ORDER BY nomubi";
    $result2 = $conexion->prepare($sql_ubi);
    $result2->bindParam(':idemp', $idemp_session);
    $result2->execute();
    $datUbicaciones = $result2->fetchAll(PDO::FETCH_ASSOC);
    
    // Filtrar kardex por empresa (solo los no cerrados)
    $sql_kar = "SELECT idkar, anio, mes FROM kardex WHERE idemp = :idemp AND cerrado = 0 ORDER BY anio DESC, mes DESC";
    $result3 = $conexion->prepare($sql_kar);
    $result3->bindParam(':idemp', $idemp_session);
    $result3->execute();
    $datKardex = $result3->fetchAll(PDO::FETCH_ASSOC);
}


$mmov->setIdmov($idmov);

// GUARDAR O EDITAR
if($ope == "save"){
    // Validaciones
    if(empty($idkar) || empty($idprod) || empty($idubi) || empty($tipmov) || empty($cantmov) || empty($valmov)){
        $_SESSION['error_msg'] = "Todos los campos obligatorios deben ser completados (Kardex, Producto, Ubicación, Tipo, Cantidad, Valor)";
    } else {
        // Obtener idemp del kardex seleccionado para auditoría
        $sql_kar_emp = "SELECT idemp FROM kardex WHERE idkar = :idkar";
        $modelo_temp = new Conexion();
        $conexion_temp = $modelo_temp->get_conexion();
        $result_kar = $conexion_temp->prepare($sql_kar_emp);
        $result_kar->bindParam(':idkar', $idkar);
        $result_kar->execute();
        $kar_data = $result_kar->fetch(PDO::FETCH_ASSOC);
        $idemp_kardex = $kar_data['idemp'] ?? $idemp_session;
        
        // Setear valores
        $mmov->setIdemp($idemp_kardex);
        $mmov->setIdkar($idkar);
        $mmov->setIdprod($idprod);
        $mmov->setIdubi($idubi);
        $mmov->setFecmov($fecmov);
        $mmov->setTipmov($tipmov);
        $mmov->setCantmov($cantmov);
        $mmov->setValmov($valmov);
        $mmov->setCostprom($costprom);
        $mmov->setDocref($docref);
        $mmov->setObs($obs);
        $mmov->setIdusu($idusu_session);

        if(!$idmov){
            // Guardar nuevo
            $resultado = $mmov->save();
            if($resultado){
                $_SESSION['success_msg'] = "Movimiento registrado exitosamente";
                
                // Registrar en auditoría
                $maud = new MAud();
                $maud->setIdemp($idemp_kardex);
                $maud->setIdusu($idusu_session);
                $maud->setTabla('movim');
                $maud->setAccion(5); // Movimiento
                // $resultado tiene el ID del movimiento u otra cosa; ojalá sea el ID
                $maud->setIdreg($resultado === true ? 0 : $resultado);
                $maud->setDatos_nue(json_encode($_POST));
                $maud->setFecha(date('Y-m-d H:i:s'));
                $maud->setIp($_SERVER['REMOTE_ADDR']);
                $maud->save();
            } else {
                $_SESSION['error_msg'] = "Error al guardar el movimiento";
            }
        } else {
            // Editar existente
            $datAnterior = $mmov->getOne();
            $resultado = $mmov->edit();
            if($resultado){
                $_SESSION['success_msg'] = "Movimiento actualizado exitosamente";
                
                // Registrar en auditoría
                $maud = new MAud();
                $maud->setIdemp($idemp_kardex);
                $maud->setIdusu($idusu_session);
                $maud->setTabla('movim');
                $maud->setAccion(5); // Movimiento
                $maud->setIdreg($idmov);
                $maud->setDatos_ant(json_encode($datAnterior));
                $maud->setDatos_nue(json_encode($_POST));
                $maud->setFecha(date('Y-m-d H:i:s'));
                $maud->setIp($_SERVER['REMOTE_ADDR']);
                $maud->save();
            } else {
                $_SESSION['error_msg'] = "Error al actualizar el movimiento";
            }
        }
    }
}

// ELIMINAR
if($ope == "eli" && $idmov){
    $datAnterior = $mmov->getOne();
    // Obtener idemp del kardex para auditoría
    $idemp_del = $datAnterior['idemp'] ?? $idemp_session;
    
    $resultado = $mmov->del();
    if($resultado){
        $_SESSION['success_msg'] = "Movimiento eliminado exitosamente";
        
        // Registrar en auditoría
        $maud = new MAud();
        $maud->setIdemp($idemp_del);
        $maud->setIdusu($idusu_session);
        $maud->setTabla('movim');
        $maud->setAccion(5); // Movimiento
        $maud->setIdreg($idmov);
        $maud->setDatos_ant(json_encode($datAnterior));
        $maud->setFecha(date('Y-m-d H:i:s'));
        $maud->setIp($_SERVER['REMOTE_ADDR']);
        $maud->save();
    } else {
        $_SESSION['error_msg'] = "Error al eliminar el movimiento";
    }
}

// EDITAR (cargar datos)
if($ope == "edi" && $idmov){
    $datOne = $mmov->getOne();
}

// Obtener todos los movimientos
if($idper_session == 1){
    // Superadmin ve todos
    $datAll = $mmov->getAll();
} else {
    // Usuario normal ve solo de su empresa
    $datAll = $mmov->getAllByEmpresa($idemp_session);
}
?>
