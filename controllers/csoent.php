<?php
require_once __DIR__ . '/../models/msoent.php';
require_once __DIR__ . '/../models/mprod.php';
require_once __DIR__ . '/../models/conexion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$msoent = new Msoent();
$msoent->setIdemp($_SESSION['idemp']);

$idsol = isset($_GET['idsol']) ? $_GET['idsol'] : null;
$ope   = isset($_POST['ope']) ? $_POST['ope'] : null;

function getEstadoSolicitudEntrada($idsol, $idemp) {
    $cn = (new conexion())->get_conexion();
    $sql = "SELECT estsol FROM solentrada WHERE idsol = :idsol AND idemp = :idemp LIMIT 1";
    $stm = $cn->prepare($sql);
    $stm->execute([':idsol' => $idsol, ':idemp' => $idemp]);
    $row = $stm->fetch(PDO::FETCH_ASSOC);
    return $row['estsol'] ?? null;
}

function getCabeceraSolicitudEntrada($idsol, $idemp) {
    $cn = (new conexion())->get_conexion();
    $sql = "SELECT idsol, idemp, idubi, fecsol, fecent, estsol, idusu, idusu_apr
            FROM solentrada
            WHERE idsol = :idsol AND idemp = :idemp
            LIMIT 1";
    $stm = $cn->prepare($sql);
    $stm->execute([':idsol' => $idsol, ':idemp' => $idemp]);
    return $stm->fetch(PDO::FETCH_ASSOC) ?: null;
}

function redirectTo($url) {
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "<script>window.location.href='" . htmlspecialchars($url, ENT_QUOTES) . "';</script>";
    }
    exit();
}

// Si no hay cabecera, crearla automaticamente (sin redireccion para evitar bucles)
if (!$idsol) {
    $newId = $msoent->createSolicitud($_SESSION['idemp'], $_SESSION['idusu']);
    if ($newId > 0) {
        $idsol = $newId;
    }
}

// Validar que la cabecera exista; si no existe, crearla en memoria
if ($idsol) {
    $cabEntrada = getCabeceraSolicitudEntrada($idsol, $_SESSION['idemp']);
    if (!$cabEntrada) {
        $newId = $msoent->createSolicitud($_SESSION['idemp'], $_SESSION['idusu']);
        if ($newId > 0) {
            $idsol = $newId;
            $cabEntrada = getCabeceraSolicitudEntrada($idsol, $_SESSION['idemp']);
        }
    }
}

// Guardar producto
if ($ope == "save" && $idsol) {
    $estadoSol = getEstadoSolicitudEntrada($idsol, $_SESSION['idemp']);
    if ($estadoSol === 'Aprobada') {
        $_SESSION['mensaje'] = "La solicitud ya está aprobada y no permite más cambios.";
        $_SESSION['tipo_mensaje'] = "warning";
        session_write_close();
        redirectTo("home.php?pg=1015&idsol=" . $idsol);
    }

    $idprod = isset($_POST['idprod']) ? $_POST['idprod'] : null;
    if (!$idprod && isset($_POST['idprod_select'])) {
        $idprod = $_POST['idprod_select'];
    }
    $cantdet = isset($_POST['cantdet']) ? $_POST['cantdet'] : null;
    $vundet = isset($_POST['vundet']) ? $_POST['vundet'] : null;
    
    if ($idprod && $cantdet && $vundet) {
        $data = [
            ":idsol"   => $idsol,
            ":idprod"  => $idprod,
            ":cantdet" => $cantdet,
            ":vundet"  => $vundet,
            ":idemp"   => $_SESSION['idemp']
        ];
        $logMsg = date('Y-m-d H:i:s') . " | Detalle entrada | idsol: $idsol | idprod: $idprod | cantdet: $cantdet | vundet: $vundet | idemp: " . $_SESSION['idemp'] . "\n";
        file_put_contents(__DIR__ . '/../debug_log.txt', $logMsg, FILE_APPEND);
        if ($msoent->save($data)) {
            $_SESSION['mensaje'] = "Se guardó correctamente";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al agregar el producto: " . $msoent->getLastError();
            $_SESSION['tipo_mensaje'] = "danger";
            file_put_contents(__DIR__ . '/../debug_log.txt', date('Y-m-d H:i:s') . " | ERROR: " . $msoent->getLastError() . "\n", FILE_APPEND);
        }
    } else {
        $_SESSION['mensaje'] = "Por favor complete todos los campos";
        $_SESSION['tipo_mensaje'] = "warning";
        $logMsg = date('Y-m-d H:i:s') . " | ERROR campos incompletos | idsol: $idsol | idprod: $idprod | cantdet: $cantdet | vundet: $vundet | idemp: " . $_SESSION['idemp'] . "\n";
        file_put_contents(__DIR__ . '/../debug_log.txt', $logMsg, FILE_APPEND);
    }
    session_write_close();
    redirectTo("home.php?pg=1015&idsol=" . $idsol);
}

// Eliminar detalle
if (isset($_GET['delete']) && $_GET['delete'] && $idsol) {
    $estadoSol = getEstadoSolicitudEntrada($idsol, $_SESSION['idemp']);
    if ($estadoSol === 'Aprobada') {
        $_SESSION['mensaje'] = "La solicitud ya está aprobada y no permite eliminar detalles.";
        $_SESSION['tipo_mensaje'] = "warning";
        session_write_close();
        redirectTo("home.php?pg=1015&idsol=" . $idsol);
    }

    $iddet = $_GET['delete'];
    if ($msoent->delete($iddet)) {
        $_SESSION['mensaje'] = "Producto eliminado exitosamente";
        $_SESSION['tipo_mensaje'] = "success";
    } else {
        $_SESSION['mensaje'] = "Error al eliminar el producto";
        $_SESSION['tipo_mensaje'] = "danger";
    }
    
    session_write_close();
    redirectTo("home.php?pg=1015&idsol=" . $idsol);
}

// Aprobar solicitud y crear movimientos en Kardex
if (isset($_GET['aprobar']) && $_GET['aprobar'] && $idsol) {
    // Obtener el Kardex activo (mes/año actual)
    require_once __DIR__ . '/../models/mkard.php';
    $mkard = new MKard();
    $mkard->setIdemp($_SESSION['idemp']);
    
    // Buscar Kardex del mes/año actual
    $anio = date('Y');
    $mes = date('n');
    $kardexActual = $mkard->getByPeriodo($anio, $mes);
    
    if (!$kardexActual) {
        $_SESSION['mensaje'] = "No existe un Kardex para el período actual. Por favor créelo primero.";
        $_SESSION['tipo_mensaje'] = "warning";
    } elseif ((int)($kardexActual['cerrado'] ?? 0) === 1) {
        $_SESSION['mensaje'] = "El Kardex del período actual está cerrado. No se puede aprobar la entrada.";
        $_SESSION['tipo_mensaje'] = "warning";
    } else {
        $idkar = $kardexActual['idkar'];

        if ($msoent->aprobarSolicitud($idsol, $idkar, 0, $_SESSION['idusu'])) {
            $_SESSION['mensaje'] = "Solicitud aprobada y movimientos registrados correctamente.";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = $msoent->getLastError() ?: "Error al aprobar la solicitud";
            $_SESSION['tipo_mensaje'] = "danger";
        }
    }
    
    session_write_close();
    redirectTo("home.php?pg=1015&idsol=" . $idsol);
}

// Traer productos para el select
$mprod = new MProd();
$productos = $mprod->getAll($_SESSION['idemp'], $_SESSION['idper']);

// Traer detalles de la solicitud actual
$detalles = [];
if ($idsol) {
    $detalles = $msoent->getAll($idsol);
}

$cabEntrada = $cabEntrada ?? null;
if ($idsol && !$cabEntrada) {
    $cabEntrada = getCabeceraSolicitudEntrada($idsol, $_SESSION['idemp']);
}
?>