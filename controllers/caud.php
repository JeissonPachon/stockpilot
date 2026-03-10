<?php
require_once(__DIR__ . '/../models/maud.php');
require_once(__DIR__ . '/../models/conexion.php');

if (session_status() === PHP_SESSION_NONE) session_start();

$idemp_sesion = isset($_SESSION['idemp']) ? $_SESSION['idemp'] : null;
$idusu_sesion = isset($_SESSION['idusu']) ? $_SESSION['idusu'] : null;

$maud = new MAud();

// ─── Parámetros de request ─────────────────────────────────────────────────
$ope      = isset($_REQUEST['ope'])    ? trim($_REQUEST['ope'])   : null;
$idaud    = isset($_REQUEST['idaud'])  ? $_REQUEST['idaud']       : null;
$idusu    = isset($_POST['idusu'])     ? $_POST['idusu']          : null;
$tabla    = isset($_POST['tabla'])     ? $_POST['tabla']          : null;
$accion   = isset($_POST['accion'])    ? $_POST['accion']         : null;
$idreg    = isset($_POST['idreg'])     ? $_POST['idreg']          : null;
$datos_ant= isset($_POST['datos_ant']) ? $_POST['datos_ant']      : null;
$datos_nue= isset($_POST['datos_nue']) ? $_POST['datos_nue']      : null;
$fecha    = isset($_POST['fecha'])     ? $_POST['fecha']          : null;
$ip       = isset($_POST['ip'])        ? $_POST['ip']             : null;

$mes_rep  = (isset($_REQUEST['mes'])  && $_REQUEST['mes']  !== '') ? (int)$_REQUEST['mes']  : null;
$anio_rep = (isset($_REQUEST['anio']) && $_REQUEST['anio'] !== '') ? (int)$_REQUEST['anio'] : null;

// ─── AJAX: Polling de nuevos eventos ──────────────────────────────────────
if ($ope === 'ajax_nuevos' && $idemp_sesion) {
    header('Content-Type: application/json');
    $desde = isset($_GET['desde']) ? $_GET['desde'] : date('Y-m-d H:i:s');
    $count = $maud->getCountDesde($idemp_sesion, $desde);
    echo json_encode(['nuevos' => $count, 'ahora' => date('Y-m-d H:i:s')]);
    exit;
}

// ─── AJAX: Verificar IPs sospechosas ──────────────────────────────────────
if ($ope === 'ajax_amenazas' && $idemp_sesion) {
    header('Content-Type: application/json');
    $ips = $maud->getIPsSospechosas($idemp_sesion, 3, 60);
    echo json_encode(['amenazas' => count($ips), 'lista' => $ips]);
    exit;
}

// ─── Vaciar historial de logins ────────────────────────────────────────────
if ($ope == 'clear_logins' && $idemp_sesion) {
    $maud->vaciarLogins($idemp_sesion);
    header("Location: ../home.php?pg=1006");
    exit;
}

// ─── CRUD básico ───────────────────────────────────────────────────────────
$maud->setIdaud($idaud);

if ($ope == 'save') {
    $maud->setIdemp($idemp_sesion);
    $maud->setIdusu($idusu);
    $maud->setTabla($tabla);
    $maud->setAccion($accion);
    $maud->setIdreg($idreg);
    $maud->setDatos_ant($datos_ant);
    $maud->setDatos_nue($datos_nue);
    $maud->setFecha($fecha);
    $maud->setIp($ip);
    if (!$idaud) $maud->save(); else $maud->edit();
}

if ($ope == 'eli' && $idaud) $maud->del();

$datOne = null;
if ($ope == 'edi' && $idaud) $datOne = $maud->getOne();

// ─── Datos para la vista ───────────────────────────────────────────────────
$datAll = $maud->getAll($idemp_sesion);
?>