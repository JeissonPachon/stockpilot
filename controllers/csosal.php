<?php
// Habilitar errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir modelos con rutas absolutas desde la raíz del proyecto
require_once __DIR__ . "/../models/msosal.php"; 
require_once __DIR__ . "/../models/mdetsal.php";
require_once __DIR__ . "/../models/mubi.php";
require_once __DIR__ . "/../models/musu.php";
require_once __DIR__ . "/../models/memp.php";
require_once __DIR__ . "/../models/mprod.php";
require_once __DIR__ . "/../models/mlote.php";
require_once __DIR__ . "/../models/conexion.php";

// ===============================================================
//  ENDPOINT AJAX: Cargar lotes disponibles para un producto
//  Solo responde cuando se llama directamente via GET con ?idprod=
// ===============================================================
if (isset($_GET['idprod']) && !isset($_GET['pg'])) {
    // Iniciar sesión si aún no está activa (llamada AJAX directa, sin pasar por home.php)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    // Desactivar display_errors para no corromper la respuesta JSON con warnings de PHP
    ini_set('display_errors', 0);

    $idprod_ajax = intval($_GET['idprod']);
    $sidemp_ajax = $_SESSION['idemp'] ?? null;
    $sidper_ajax = $_SESSION['idper'] ?? null;
    $cn_ajax = (new conexion())->get_conexion();

    // Obtener datos del producto — verificar que pertenece a la empresa del usuario
    $sqlProd = "SELECT costouni, precioven FROM producto WHERE idprod = :idprod";
    if ($sidper_ajax != 1 && $sidemp_ajax !== null) {
        $sqlProd .= " AND idemp = :idemp";
    }
    $stmProd = $cn_ajax->prepare($sqlProd);
    $stmProd->bindParam(':idprod', $idprod_ajax);
    if ($sidper_ajax != 1 && $sidemp_ajax !== null) {
        $stmProd->bindParam(':idemp', $sidemp_ajax);
    }
    $stmProd->execute();
    $prod_data = $stmProd->fetch(PDO::FETCH_ASSOC);

    // Obtener lotes disponibles — filtrar por empresa via JOIN con producto
    $sql_ajax = "SELECT l.idlote, l.codlot, l.cantact, l.costuni
                 FROM lote l
                 INNER JOIN producto p ON l.idprod = p.idprod
                 WHERE l.idprod = :idprod AND l.cantact > 0";
    if ($sidper_ajax != 1 && $sidemp_ajax !== null) {
        $sql_ajax .= " AND p.idemp = :idemp";
    }
    $sql_ajax .= " ORDER BY l.fecven ASC";
    $stm_ajax = $cn_ajax->prepare($sql_ajax);
    $stm_ajax->bindParam(':idprod', $idprod_ajax);
    if ($sidper_ajax != 1 && $sidemp_ajax !== null) {
        $stm_ajax->bindParam(':idemp', $sidemp_ajax);
    }
    $stm_ajax->execute();
    $lotes_ajax = $stm_ajax->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode([
        'costouni'  => $prod_data['costouni']  ?? 0,
        'precioven' => $prod_data['precioven'] ?? 0,
        'lotes'     => $lotes_ajax,
    ]);
    exit();
}

// Instanciar modelos
$msosal  = new Msosal();
$mdetsal = new Mdetsal();
$mubi    = new Mubi();
$musu    = new Musu();
$memp    = new Memp();
$mprod   = new Mprod();
$mlote   = new Mlote();

// Capturar parámetros
$idsal  = isset($_REQUEST['idsal'])  ? $_REQUEST['idsal']  : NULL;
$fecsal = date('Y-m-d H:i:s'); // Forzar fecha y hora actual
$tpsal  = isset($_POST['tpsal'])     ? $_POST['tpsal']     : NULL;
$idemp  = isset($_POST['idemp'])     ? $_POST['idemp']     : NULL;
$idusu  = isset($_POST['idusu'])     ? $_POST['idusu']     : NULL;
$idubi  = isset($_POST['idubi'])     ? $_POST['idubi']     : NULL;
$refdoc = isset($_POST['refdoc'])    ? $_POST['refdoc']    : NULL;
$estsal = isset($_POST['estsal'])    ? $_POST['estsal']    : 'Pendiente';
$ope    = isset($_REQUEST['ope'])    ? $_REQUEST['ope']    : NULL;

// Variables para detalle
$iddet   = isset($_REQUEST['iddet'])   ? $_REQUEST['iddet']   : NULL;
$idprod  = isset($_POST['idprod'])     ? $_POST['idprod']     : NULL;
$cantdet = isset($_POST['cantdet'])    ? $_POST['cantdet']    : NULL;
$vundet  = isset($_POST['vundet'])     ? $_POST['vundet']     : NULL;
$idlote  = isset($_POST['idlote'])     ? $_POST['idlote']     : NULL;
$delete  = isset($_REQUEST['delete'])  ? $_REQUEST['delete']  : NULL;

$dtOne = NULL;
$detalles = [];
$cab = [];

// ===============================================================
//  CARGAR DATOS BÁSICOS (Filtrados por empresa)
// ===============================================================
$sidemp = $_SESSION['idemp'] ?? null;
$sidper = $_SESSION['idper'] ?? null;

$ubi  = $mubi->getAll($sidemp, $sidper);      // Ubicaciones (almacenes)
$emp  = $memp->getAll();                      // Empresas (sin filtro, usualmente para admin)
$usu  = $musu->getAll($sidemp, $sidper);      // Usuarios
$productos = $mprod->getAll($sidemp, $sidper); // Productos
$almacenes = $mubi->getAll($sidemp, $sidper); // Almacenes (usando ubicaciones)

// ===============================================================
//  OPERACIONES SOBRE SALIDA (CABECERA)
// ===============================================================

if ($ope == "SaVe" && $idsal) {
    // Edición de salida
    $msosal->setIdsal($idsal);
    $msosal->setFecsal($fecsal);
    $msosal->setTpsal($tpsal);
    $msosal->setIdemp($idemp);
    $msosal->setIdusu($idusu);
    $msosal->setIdubi($idubi);
    $msosal->setRefdoc($refdoc);
    $msosal->setEstsal($estsal);
    
    if($msosal->edit()){
        $_SESSION['mensaje'] = "Salida actualizada correctamente";
        $_SESSION['tipo_mensaje'] = "success";
    } else {
        $_SESSION['mensaje'] = "Error al actualizar la salida";
        $_SESSION['tipo_mensaje'] = "danger";
    }

} elseif ($ope == "SaVe" && !$idsal) {
    // Nueva salida
    $msosal->setFecsal($fecsal);
    $msosal->setTpsal($tpsal);
    $msosal->setIdemp($idemp);
    $msosal->setIdusu($idusu);
    $msosal->setIdubi($idubi);
    $msosal->setRefdoc($refdoc);
    $msosal->setEstsal($estsal);
    
    $newId = $msosal->save();
    if($newId){
        $idsal = $newId;
        $_SESSION['mensaje'] = "Salida creada correctamente";
        $_SESSION['tipo_mensaje'] = "success";
    } else {
        $_SESSION['mensaje'] = "Error al crear la salida";
        $_SESSION['tipo_mensaje'] = "danger";
    }
}

// ===============================================================
//  ELIMINAR SALIDA
// ===============================================================

if ($ope == "eLi" && $idsal) {
    $msosal->setIdsal($idsal);
    if($msosal->del()){
        $_SESSION['mensaje'] = "Salida eliminada correctamente";
        $_SESSION['tipo_mensaje'] = "success";
    } else {
        $_SESSION['mensaje'] = "Error al eliminar la salida";
        $_SESSION['tipo_mensaje'] = "danger";
    }
}

// ===============================================================
//  EDITAR → cargar un registro de salida
// ===============================================================

if ($ope == "eDi" && $idsal) {
    $msosal->setIdsal($idsal);
    $dtOne = $msosal->getOne();
}

// ===============================================================
//  OPERACIONES SOBRE DETALLE DE SALIDA
// ===============================================================

// GUARDAR DETALLE
if ($ope == "save" && $idsal && $idprod && $cantdet) {
    // Calcular total
    $totdet = $cantdet * ($vundet ?? 0);
    
    // VALIDACIÓN DE STOCK POR LOTE
    $errorValidacion = false;
    if ($idlote) {
        $mlote = new Mlote();
        $mlote->setIdlote($idlote);
        $datLote = $mlote->getOne();
        
        if ($datLote) {
            $stockDisponible = $datLote['cantact'];
            if ($cantdet > $stockDisponible) {
                $_SESSION['mensaje'] = "Error: La cantidad solicitada ($cantdet) supera el stock disponible en el lote seleccionado ($stockDisponible).";
                $_SESSION['tipo_mensaje'] = "danger";
                $errorValidacion = true;
            }
        }
    }

    if (!$errorValidacion) {
        // Obtener el costo unitario del lote seleccionado (fuente de verdad de costo)
        $costoUniLote = isset($datLote['costuni']) ? (float)$datLote['costuni'] : 0;

        // Obtener precio de venta del producto (para tipo Venta)
        $mprod->setIdprod($idprod);
        $datProd = $mprod->getOne();
        $precioVenta = (float)($datProd['precioven'] ?? 0);

        // El precio que se guarda en el detalle: precio venta si es Venta, costo lote si es otro tipo
        $tpsal_actual = $_POST['tpsal_actual'] ?? null; // Opcional: si se pasa el tipo
        $vundetGuardar = $costoUniLote > 0 ? $costoUniLote : $precioVenta;

        // Recalcular total con el precio correcto
        $totdet = (float)$cantdet * $vundetGuardar;

        $mdetsal->setIdemp($idemp ?? $_SESSION['idemp'] ?? 1);
        $mdetsal->setIdsal($idsal);
        $mdetsal->setIdprod($idprod);
        $mdetsal->setCantdet($cantdet);
        $mdetsal->setVundet($vundetGuardar);
        $mdetsal->setIdlote($idlote ?? NULL);
        
        if($mdetsal->save()){
            // DESCONTAR STOCK DEL LOTE
            if ($idlote) {
                $mlote->updateStock($idlote, -$cantdet);
            }
            $_SESSION['mensaje'] = "Producto agregado a la salida";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al agregar el producto";
            $_SESSION['tipo_mensaje'] = "danger";
        }
    }
    // Redirigir para preservar idsal en la URL y evitar doble envío del formulario
    echo "<script>window.location.href='home.php?pg=" . ($pg ?? 1013) . "&idsal=" . $idsal . "';</script>";
    exit();
}

// ELIMINAR DETALLE
if ($delete && $idsal) {
    // Obtener datos del detalle antes de eliminar para restaurar stock
    $sqlDet = "SELECT idlote, cantdet, idprod, idemp FROM detsalida WHERE iddsal = :iddsal";
    $cn = (new conexion())->get_conexion();
    $stmDet = $cn->prepare($sqlDet);
    $stmDet->bindParam(":iddsal", $delete);
    $stmDet->execute();
    $datDet = $stmDet->fetch(PDO::FETCH_ASSOC);

    $mdetsal->setIddet($delete);
    if($mdetsal->del()){
        // RESTAURAR STOCK AL LOTE
        if ($datDet && $datDet['idlote']) {
            $mlote->updateStock($datDet['idlote'], $datDet['cantdet']);
        }
        $_SESSION['mensaje'] = "Producto eliminado de la salida";
        $_SESSION['tipo_mensaje'] = "success";
    } else {
        $_SESSION['mensaje'] = "Error al eliminar el producto";
        $_SESSION['tipo_mensaje'] = "danger";
    }
    echo "<script>window.location.href='home.php?pg=" . ($pg ?? 1013) . "&idsal=" . $idsal . "';</script>";
    exit();
}

// ===============================================================
//  FINALIZAR SALIDA
// ===============================================================
if ($ope == "Fin" && $idsal) {
    // 1. Obtener detalles para el Kardex
    $msosal->setIdsal($idsal);
    $cab = $msosal->getOne(); // FIX: Obtener cabecera antes del loop
    $detallesKardex = $msosal->getDetalles();
    $cn = (new conexion())->get_conexion();

    try {
        $cn->beginTransaction();

        foreach ($detallesKardex as $det) {
            // Obtener saldo actual de inventario para este producto/almacen (opcional, para salcan/salval)
            // Sumar solo lotes que pertenezcan a la misma empresa del producto
            $sqlStock = "SELECT SUM(l.cantact) as total_stock FROM lote l INNER JOIN producto p ON l.idprod = p.idprod WHERE l.idprod = :idp AND p.idemp = :idemp";
            $stmS = $cn->prepare($sqlStock);
            $stmS->execute([':idp' => $det['idprod'], ':idemp' => $sidemp]);
            $resStock = $stmS->fetch(PDO::FETCH_ASSOC);
            $salCan = $resStock['total_stock'] ?? 0;

            // Insertar en Kardex
            $sqlK = "INSERT INTO kardex (fecmov, mes, anio, idpro, idlot, idubi, tipmov, docref, cant, cosuni, valmov, salcan, salval, idusu)
                     VALUES (NOW(), MONTH(NOW()), YEAR(NOW()), :idpro, :idlot, :idubi, 2, :docref, :cant, :cosuni, :valmov, :salcan, :salval, :idusu)";
            
            $stmK = $cn->prepare($sqlK);
            $valMov = $det['cantdet'] * $det['vundet'];
            $salVal = $salCan * $det['vundet']; // Estimación de valor de saldo

            $stmK->execute([
                ':idpro'  => $det['idprod'],
                ':idlot'  => $det['idlote'],
                ':idubi'  => $cab['idubi'] ?? 1,
                ':docref' => $cab['refdoc'] ?? 'SAL-'.$idsal,
                ':cant'   => $det['cantdet'],
                ':cosuni' => $det['vundet'],
                ':valmov' => $valMov,
                ':salcan' => $salCan,
                ':salval' => $salVal,
                ':idusu'  => $_SESSION['idusu'] ?? 1
            ]);
        }

        // 2. Actualizar estado de la salida
        $sqlUpd = "UPDATE solsalida SET estsal = 'Procesada' WHERE idsal = :idsal";
        $stmUpd = $cn->prepare($sqlUpd);
        $stmUpd->execute([':idsal' => $idsal]);

        $cn->commit();

        $_SESSION['mensaje'] = "Salida procesada y Kardex actualizado correctamente";
        $_SESSION['tipo_mensaje'] = "success";
        $idsal = null;

    } catch (Exception $e) {
        $cn->rollBack();
        $_SESSION['mensaje'] = "Error al procesar salida: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
    }
}

// ===============================================================
//  CARGAR DATOS PARA LA VISTA
// ===============================================================

// Si hay idsal, cargar cabecera y detalles
if ($idsal) {
    $msosal->setIdsal($idsal);
    $cab = $msosal->getOne();
    $detalles = $msosal->getDetalles();
}

// LISTA GENERAL DE SALIDAS (filtrada por empresa del usuario)
$dtAll = $msosal->getAll($sidemp, $sidper);

// Variable para compatibilidad con vsosal
$idsol = $idsal;
?>