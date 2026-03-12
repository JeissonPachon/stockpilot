<?php
// Mantener log de errores, sin romper respuestas HTML/JSON en produccion.
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Incluir modelos con rutas absolutas desde la raíz del proyecto
require_once __DIR__ . "/../models/msosal.php"; 
require_once __DIR__ . "/../models/mdetsal.php";
require_once __DIR__ . "/../models/mubi.php";
require_once __DIR__ . "/../models/musu.php";
require_once __DIR__ . "/../models/memp.php";
require_once __DIR__ . "/../models/mprod.php";
require_once __DIR__ . "/../models/mlote.php";
require_once __DIR__ . "/../models/minv.php";
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
    $idubi_ajax = isset($_GET['idubi']) ? intval($_GET['idubi']) : 0;
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
    if ($idubi_ajax > 0) {
        $sql_ajax .= " AND l.idubi = :idubi";
    }
    if ($sidper_ajax != 1 && $sidemp_ajax !== null) {
        $sql_ajax .= " AND p.idemp = :idemp";
    }
    $sql_ajax .= " ORDER BY (l.fecven IS NULL), l.fecven ASC, l.fecing ASC, l.idlote ASC";
    $stm_ajax = $cn_ajax->prepare($sql_ajax);
    $stm_ajax->bindParam(':idprod', $idprod_ajax);
    if ($idubi_ajax > 0) {
        $stm_ajax->bindParam(':idubi', $idubi_ajax);
    }
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
$minv    = new MInv();

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

if (!function_exists('getEstadoSalida')) {
    function getEstadoSalida($idsal, $idemp) {
        $cn = (new conexion())->get_conexion();
        $sql = "SELECT estsal FROM solsalida WHERE idsal = :idsal AND idemp = :idemp LIMIT 1";
        $stm = $cn->prepare($sql);
        $stm->execute([':idsal' => $idsal, ':idemp' => $idemp]);
        $row = $stm->fetch(PDO::FETCH_ASSOC);
        return $row['estsal'] ?? null;
    }
}

if (!function_exists('syncInventarioFromMovim')) {
    function syncInventarioFromMovim($conexion, $idemp, $idprod, $idubi) {
        $sqlSaldo = "SELECT COALESCE(SUM(CASE WHEN m.tipmov = 1 THEN m.cantmov WHEN m.tipmov = 2 THEN -m.cantmov ELSE 0 END), 0) AS saldo
                     FROM movim m
                     INNER JOIN kardex k ON m.idkar = k.idkar
                     WHERE k.idemp = :idemp AND m.idprod = :idprod AND m.idubi = :idubi";
        $stmSaldo = $conexion->prepare($sqlSaldo);
        $stmSaldo->execute([
            ':idemp' => $idemp,
            ':idprod' => $idprod,
            ':idubi' => $idubi
        ]);
        $saldo = (float)$stmSaldo->fetchColumn();

        $sqlInv = "INSERT INTO inventario (idemp, idprod, idubi, cant, fec_crea, fec_actu)
                   VALUES (:idemp, :idprod, :idubi, :cant, NOW(), NOW())
                   ON DUPLICATE KEY UPDATE cant = VALUES(cant), fec_actu = NOW()";
        $stmInv = $conexion->prepare($sqlInv);
        $stmInv->execute([
            ':idemp' => $idemp,
            ':idprod' => $idprod,
            ':idubi' => $idubi,
            ':cant' => $saldo
        ]);
    }
}

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
    $estadoSalida = getEstadoSalida((int)$idsal, (int)($_SESSION['idemp'] ?? 0));
    if ($estadoSalida === 'Procesada') {
        $_SESSION['mensaje'] = "La salida ya esta procesada y no permite cambios.";
        $_SESSION['tipo_mensaje'] = "warning";
        header("Location: home.php?pg=" . ($pg ?? 1013) . "&idsal=" . $idsal);
        exit();
    }

    // No permitir cambio de almacen cuando ya existen detalles vinculados.
    $cnHead = (new conexion())->get_conexion();
    $sqlCabChk = "SELECT idubi FROM solsalida WHERE idsal = :idsal AND idemp = :idemp LIMIT 1";
    $stmCabChk = $cnHead->prepare($sqlCabChk);
    $idempSesion = (int)($_SESSION['idemp'] ?? 0);
    $stmCabChk->execute([':idsal' => $idsal, ':idemp' => $idempSesion]);
    $cabActual = $stmCabChk->fetch(PDO::FETCH_ASSOC);

    if ($cabActual) {
        $sqlCntDet = "SELECT COUNT(*) FROM detsalida WHERE idsal = :idsal AND idemp = :idemp";
        $stmCntDet = $cnHead->prepare($sqlCntDet);
        $stmCntDet->execute([':idsal' => $idsal, ':idemp' => $idempSesion]);
        $cntDet = (int)$stmCntDet->fetchColumn();

        if ($cntDet > 0 && (int)$idubi !== (int)$cabActual['idubi']) {
            $_SESSION['mensaje'] = "No se puede cambiar el almacen cuando la salida ya tiene detalles.";
            $_SESSION['tipo_mensaje'] = "warning";
            header("Location: home.php?pg=" . ($pg ?? 1013) . "&idsal=" . $idsal);
            exit();
        }
    }

    // Edición de salida
    $msosal->setIdsal($idsal);
    $msosal->setFecsal($fecsal);
    $msosal->setTpsal($tpsal);
    $msosal->setIdemp($idemp);
    $msosal->setIdusu($idusu);
    $msosal->setIdubi($idubi);
    $msosal->setRefdoc($refdoc);
    // El estado no se permite manipular desde formulario; solo Fin lo cambia a Procesada.
    $msosal->setEstsal($estadoSalida ?: 'Pendiente');
    
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
    // Toda salida nueva inicia en Pendiente.
    $msosal->setEstsal('Pendiente');
    
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
    $estadoSalida = getEstadoSalida((int)$idsal, (int)($_SESSION['idemp'] ?? 0));
    if ($estadoSalida === 'Procesada') {
        $_SESSION['mensaje'] = "La salida ya esta procesada y no se puede eliminar.";
        $_SESSION['tipo_mensaje'] = "warning";
        header("Location: home.php?pg=" . ($pg ?? 1013) . "&idsal=" . $idsal);
        exit();
    }

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
    $idsal = (int)$idsal;
    $idprod = (int)$idprod;
    $cantdet = (float)$cantdet;
    $idlote = $idlote ? (int)$idlote : null;

    if ($idsal <= 0 || $idprod <= 0 || $cantdet <= 0) {
        $_SESSION['mensaje'] = "Datos invalidos para agregar detalle de salida.";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: home.php?pg=" . ($pg ?? 1013) . "&idsal=" . $idsal);
        exit();
    }

    $estadoSalida = getEstadoSalida($idsal, (int)($_SESSION['idemp'] ?? 0));
    if ($estadoSalida === 'Procesada') {
        $_SESSION['mensaje'] = "La salida ya esta procesada y no permite agregar productos.";
        $_SESSION['tipo_mensaje'] = "warning";
        header("Location: home.php?pg=" . ($pg ?? 1013) . "&idsal=" . $idsal);
        exit();
    }
    
    $cnSave = (new conexion())->get_conexion();
    try {
        $cnSave->beginTransaction();

        $idempSesion = (int)($_SESSION['idemp'] ?? 0);

        $sqlCabSal = "SELECT idsal, idemp, idubi, tpsal FROM solsalida WHERE idsal = :idsal AND idemp = :idemp LIMIT 1 FOR UPDATE";
        $stmCabSal = $cnSave->prepare($sqlCabSal);
        $stmCabSal->execute([':idsal' => $idsal, ':idemp' => $idempSesion]);
        $cabSal = $stmCabSal->fetch(PDO::FETCH_ASSOC);

        if (!$cabSal) {
            throw new Exception("La salida no existe para la empresa activa.");
        }

        $idubiSalida = (int)($cabSal['idubi'] ?? 0);
        if ($idubiSalida <= 0) {
            throw new Exception("La salida no tiene una ubicacion valida.");
        }

        // Validar stock total disponible antes de asignar
        $sqlStockTotal = "SELECT COALESCE(SUM(l.cantact), 0) AS stock_total
                          FROM lote l
                          INNER JOIN producto p ON l.idprod = p.idprod
                          WHERE l.idprod = :idprod AND p.idemp = :idemp AND l.idubi = :idubi";
        $stmStockTotal = $cnSave->prepare($sqlStockTotal);
        $stmStockTotal->execute([
            ':idprod' => $idprod,
            ':idemp' => $idempSesion,
            ':idubi' => $idubiSalida
        ]);
        $stockTotal = (float)$stmStockTotal->fetchColumn();
        if ($stockTotal <= 0) {
            throw new Exception("No hay stock disponible para este producto en la ubicacion seleccionada.");
        }
        if ($cantdet > $stockTotal) {
            throw new Exception("Stock insuficiente. Disponible: " . $stockTotal . ", solicitado: " . $cantdet);
        }

        $mprod->setIdprod($idprod);
        $datProd = $mprod->getOne();
        $precioVenta = (float)($datProd['precioven'] ?? 0);

        $tpsal_actual = $_POST['tpsal_actual'] ?? ($cabSal['tpsal'] ?? 'Venta');

        $asignaciones = [];

        if ($idlote) {
            $sqlLoteSel = "SELECT l.idlote, l.cantact, l.costuni
                           FROM lote l
                           INNER JOIN producto p ON l.idprod = p.idprod
                           WHERE l.idlote = :idlote AND l.idprod = :idprod AND p.idemp = :idemp AND l.idubi = :idubi
                           LIMIT 1";
            $stmLoteSel = $cnSave->prepare($sqlLoteSel);
            $stmLoteSel->execute([
                ':idlote' => $idlote,
                ':idprod' => $idprod,
                ':idemp' => $idempSesion,
                ':idubi' => $idubiSalida
            ]);
            $loteSel = $stmLoteSel->fetch(PDO::FETCH_ASSOC);

            if (!$loteSel) {
                throw new Exception("El lote seleccionado no es valido para este producto/empresa/ubicacion.");
            }

            $stockDisponible = (float)$loteSel['cantact'];
            if ($cantdet > $stockDisponible) {
                throw new Exception("La cantidad solicitada ($cantdet) supera el stock disponible en el lote seleccionado ($stockDisponible).");
            }

            $asignaciones[] = [
                'idlote' => (int)$loteSel['idlote'],
                'cant' => $cantdet,
                'costuni' => (float)$loteSel['costuni'],
                'origen' => 'MANUAL'
            ];
        } else {
            // FIFO automatico: asigna desde lotes por vencimiento y luego por fecha de ingreso.
            $sqlFifo = "SELECT l.idlote, l.cantact, l.costuni, l.fecven, l.fecing
                        FROM lote l
                        INNER JOIN producto p ON l.idprod = p.idprod
                        WHERE l.idprod = :idprod AND p.idemp = :idemp AND l.idubi = :idubi AND l.cantact > 0
                        ORDER BY (l.fecven IS NULL), l.fecven ASC, l.fecing ASC, l.idlote ASC";
            $stmFifo = $cnSave->prepare($sqlFifo);
            $stmFifo->execute([
                ':idprod' => $idprod,
                ':idemp' => $idempSesion,
                ':idubi' => $idubiSalida
            ]);
            $lotesFifo = $stmFifo->fetchAll(PDO::FETCH_ASSOC);

            if (empty($lotesFifo)) {
                throw new Exception("No hay lotes con stock disponible para este producto en la ubicacion de la salida.");
            }

            $restante = $cantdet;
            foreach ($lotesFifo as $loteFifo) {
                if ($restante <= 0) {
                    break;
                }
                $disp = (float)$loteFifo['cantact'];
                if ($disp <= 0) {
                    continue;
                }
                $usar = ($restante <= $disp) ? $restante : $disp;
                $asignaciones[] = [
                    'idlote' => (int)$loteFifo['idlote'],
                    'cant' => $usar,
                    'costuni' => (float)$loteFifo['costuni'],
                    'origen' => 'FIFO'
                ];
                $restante -= $usar;
            }

            if ($restante > 0) {
                throw new Exception("Stock insuficiente para completar la cantidad solicitada. Faltante: " . $restante);
            }
        }

        $sqlInsDet = "INSERT INTO detsalida (idemp, idsal, idprod, cantdet, vundet, idlote, origen)
                  VALUES (:idemp, :idsal, :idprod, :cantdet, :vundet, :idlote, :origen)";
        $stmInsDet = $cnSave->prepare($sqlInsDet);

        $sqlUpdLote = "UPDATE lote SET cantact = cantact - :cant WHERE idlote = :idlote AND cantact >= :cant";
        $stmUpdLote = $cnSave->prepare($sqlUpdLote);

        foreach ($asignaciones as $asg) {
            $costoUniLote = (float)$asg['costuni'];

            if ($tpsal_actual === 'Venta') {
                $vundetGuardar = $precioVenta > 0 ? $precioVenta : $costoUniLote;
            } else {
                $vundetGuardar = $costoUniLote > 0 ? $costoUniLote : $precioVenta;
            }

            if ($vundetGuardar <= 0) {
                throw new Exception("No se pudo determinar el valor unitario del producto/lote.");
            }

            $stmInsDet->execute([
                ':idemp' => $idempSesion,
                ':idsal' => $idsal,
                ':idprod' => $idprod,
                ':cantdet' => (float)$asg['cant'],
                ':vundet' => $vundetGuardar,
                ':idlote' => (int)$asg['idlote'],
                ':origen' => (string)$asg['origen']
            ]);

            $stmUpdLote->execute([
                ':cant' => (float)$asg['cant'],
                ':idlote' => (int)$asg['idlote']
            ]);

            if ($stmUpdLote->rowCount() === 0) {
                throw new Exception("No se pudo descontar stock del lote " . $asg['idlote'] . ".");
            }
        }

        $cnSave->commit();

        // Sincronizar inventario desde lotes luego de los descuentos
        $minv->syncInventarioFromLotes($idempSesion, $idprod, $idubiSalida);

        $_SESSION['mensaje'] = $idlote ? "Producto agregado a la salida" : "Producto agregado con asignacion FIFO automatica";
        $_SESSION['tipo_mensaje'] = "success";
    } catch (Exception $e) {
        if ($cnSave->inTransaction()) {
            $cnSave->rollBack();
        }
        $_SESSION['mensaje'] = "Error al agregar el producto: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
    }
    // Redirigir para preservar idsal en la URL y evitar doble envío del formulario
    header("Location: home.php?pg=" . ($pg ?? 1013) . "&idsal=" . $idsal . "&focus=det");
    exit();
}

// ELIMINAR DETALLE
if ($delete && $idsal) {
    $delete = (int)$delete;
    $idsal = (int)$idsal;

    $estadoSalida = getEstadoSalida($idsal, (int)($_SESSION['idemp'] ?? 0));
    if ($estadoSalida === 'Procesada') {
        $_SESSION['mensaje'] = "La salida ya esta procesada y no permite eliminar productos.";
        $_SESSION['tipo_mensaje'] = "warning";
        header("Location: home.php?pg=" . ($pg ?? 1013) . "&idsal=" . $idsal);
        exit();
    }

    // Obtener datos del detalle antes de eliminar para restaurar stock
    $sqlDet = "SELECT idlote, cantdet, idprod, idemp FROM detsalida WHERE iddsal = :iddsal AND idsal = :idsal AND idemp = :idemp";
    $cn = (new conexion())->get_conexion();
    $stmDet = $cn->prepare($sqlDet);
    $stmDet->bindParam(":iddsal", $delete);
    $stmDet->bindParam(":idsal", $idsal);
    $idempSesion = $_SESSION['idemp'] ?? 0;
    $stmDet->bindParam(":idemp", $idempSesion);
    $stmDet->execute();
    $datDet = $stmDet->fetch(PDO::FETCH_ASSOC);

    $mdetsal->setIddet($delete);
    if($mdetsal->del()){
        // RESTAURAR STOCK AL LOTE
        if ($datDet && $datDet['idlote']) {
            $mlote->updateStock($datDet['idlote'], $datDet['cantdet']);
        }
        // Sincronizar inventario con el stock actualizado
        if ($datDet && $datDet['idprod']) {
            $stmUbi = $cn->prepare("SELECT idubi FROM solsalida WHERE idsal = :idsal LIMIT 1");
            $stmUbi->execute([':idsal' => $idsal]);
            $idubiSalida = (int)$stmUbi->fetchColumn();
            $minv->syncInventarioFromLotes((int)$idempSesion, (int)$datDet['idprod'], $idubiSalida);
        }
        $_SESSION['mensaje'] = "Producto eliminado de la salida";
        $_SESSION['tipo_mensaje'] = "success";
    } else {
        $_SESSION['mensaje'] = "Error al eliminar el producto";
        $_SESSION['tipo_mensaje'] = "danger";
    }
    header("Location: home.php?pg=" . ($pg ?? 1013) . "&idsal=" . $idsal);
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

    if (!$cab) {
        $_SESSION['mensaje'] = "La salida indicada no existe o no está disponible.";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: home.php?pg=" . ($pg ?? 1013));
        exit();
    }

    if (($cab['estsal'] ?? '') === 'Procesada') {
        $_SESSION['mensaje'] = "Esta salida ya fue procesada previamente. No se generaron movimientos duplicados.";
        $_SESSION['tipo_mensaje'] = "warning";
        header("Location: home.php?pg=" . ($pg ?? 1013) . "&idsal=" . $idsal);
        exit();
    }

    if (empty($detallesKardex)) {
        $_SESSION['mensaje'] = "No hay detalles para procesar en la salida.";
        $_SESSION['tipo_mensaje'] = "warning";
        header("Location: home.php?pg=" . ($pg ?? 1013) . "&idsal=" . $idsal);
        exit();
    }

    $cn = (new conexion())->get_conexion();

    try {
        $cn->beginTransaction();

        $idempCab = (int)($cab['idemp'] ?? ($sidemp ?? 0));
        if ($idempCab <= 0) {
            throw new Exception("No se pudo determinar la empresa de la salida.");
        }

        $anioMov = (int)date('Y');
        $mesMov = (int)date('n');

        // Obtener o crear el kardex del periodo para la empresa.
        $sqlKar = "SELECT idkar, cerrado FROM kardex WHERE anio = :anio AND mes = :mes AND idemp = :idemp LIMIT 1";
        $stmKar = $cn->prepare($sqlKar);
        $stmKar->execute([
            ':anio' => $anioMov,
            ':mes' => $mesMov,
            ':idemp' => $idempCab
        ]);
        $kar = $stmKar->fetch(PDO::FETCH_ASSOC);

        if ($kar && (int)$kar['cerrado'] === 1) {
            throw new Exception("El kardex del periodo actual esta cerrado.");
        }

        if ($kar) {
            $idkar = (int)$kar['idkar'];
        } else {
            $sqlInsKar = "INSERT INTO kardex (anio, mes, cerrado, idemp) VALUES (:anio, :mes, 0, :idemp)";
            $stmInsKar = $cn->prepare($sqlInsKar);
            $stmInsKar->execute([
                ':anio' => $anioMov,
                ':mes' => $mesMov,
                ':idemp' => $idempCab
            ]);
            $idkar = (int)$cn->lastInsertId();
        }

        $fecMov = !empty($cab['fecsal']) ? $cab['fecsal'] : date('Y-m-d H:i:s');
        $idusuMov = (int)($_SESSION['idusu'] ?? ($cab['idusu'] ?? 1));
        $docRef = !empty($cab['refdoc']) ? $cab['refdoc'] : ('SAL-' . $idsal);

        $sqlMov = "INSERT INTO movim (idkar, idprod, idubi, fecmov, tipmov, cantmov, valmov, costprom, docref, obs, idusu, fec_crea, fec_actu)
                   VALUES (:idkar, :idprod, :idubi, :fecmov, 2, :cantmov, :valmov, :costprom, :docref, :obs, :idusu, NOW(), NOW())";
        $stmMov = $cn->prepare($sqlMov);
        $syncPairs = [];

        foreach ($detallesKardex as $det) {
            $cantMov = (float)$det['cantdet'];
            $cosUni = (float)$det['vundet'];
            $valMov = $cantMov * $cosUni;

            $stmMov->execute([
                ':idkar' => $idkar,
                ':idprod' => (int)$det['idprod'],
                ':idubi' => (int)($cab['idubi'] ?? 1),
                ':fecmov' => $fecMov,
                ':cantmov' => $cantMov,
                ':valmov' => $valMov,
                ':costprom' => $cosUni,
                ':docref' => $docRef,
                ':obs' => 'Salida #'.$idsal,
                ':idusu' => $idusuMov
            ]);

            $pairKey = ((int)$det['idprod']) . '|' . ((int)($cab['idubi'] ?? 1));
            $syncPairs[$pairKey] = [
                'idprod' => (int)$det['idprod'],
                'idubi' => (int)($cab['idubi'] ?? 1)
            ];
        }

        foreach ($syncPairs as $pair) {
            $prodId = (int)$pair['idprod'];
            $ubiId = (int)$pair['idubi'];
            syncInventarioFromMovim($cn, $idempCab, $prodId, $ubiId);
            // Mantener inventario alineado con lotes
            $minv->syncInventarioFromLotes($idempCab, $prodId, $ubiId, $cn);
        }

        // 2. Actualizar estado de la salida
        $sqlUpd = "UPDATE solsalida SET estsal = 'Procesada' WHERE idsal = :idsal AND estsal <> 'Procesada'";
        $stmUpd = $cn->prepare($sqlUpd);
        $stmUpd->execute([':idsal' => $idsal]);

        $cn->commit();

        $_SESSION['mensaje'] = "Salida procesada y movimientos registrados correctamente";
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