<?php
// ===============================================
// Archivo: controllers/CRegUsu.php
// Objetivo: Procesar la solicitud de registro de usuario (Paso 1)
// 
// Flujo CORREGIDO: Guarda el usuario y redirige al Paso 2 (Registro de Empresa) 
// en index.php, sin iniciar sesión todavía.
// ===============================================

require_once('../models/conexion.php'); 
require_once('../models/musu.php'); 
require_once('misfun.php'); 

// ⬇️ DEFINICIÓN DE CLAVES PARA RECAPTCHA
define('RECAPTCHA_SECRET_KEY', '6LerVXwsAAAAAO1IVu4NPPU6LkWuc0evHbgnqsbm');
define('RECAPTCHA_SCORE_MINIMO', 0.1);

$pg = 'registro'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- ⬇️ INICIO VALIDACIÓN RECAPTCHA V3 ⬇️ ---
    $recaptcha_token = $_POST['recaptchaResponse'] ?? NULL;

    if (!$recaptcha_token) {
        header("Location: ../index.php?pg=$pg&err=campos_vacios");
        exit;
    }

    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => RECAPTCHA_SECRET_KEY,
        'response' => $recaptcha_token,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ];

    $context  = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    $response_keys = json_decode($response, true);

    if (!$response_keys["success"] || $response_keys["score"] < RECAPTCHA_SCORE_MINIMO) {
        header("Location: ../index.php?pg=$pg&err=recaptcha_fail");
        exit;
    }
    // --- ⬆️ FIN VALIDACIÓN RECAPTCHA V3 ⬆️ ---

    $muser = new Musu();

    // Recibir datos del POST
    $nomusu     = $_POST['nomusu'] ?? NULL;
    $apeusu     = $_POST['apeusu'] ?? NULL;
    $tdousu     = $_POST['tdousu'] ?? NULL;
    $ndousu     = $_POST['ndousu'] ?? NULL;
    $celusu     = $_POST['celusu'] ?? NULL;
    $emausu     = $_POST['emausu'] ?? NULL;
    $pasusu     = $_POST['pasusu'] ?? NULL; 
    $pasusu2    = $_POST['pasusu2'] ?? NULL; 
    
    $idper      = 2; 
    $act        = 1; 
    $fec_crea   = date('Y-m-d H:i:s');
    $fec_actu   = date('Y-m-d H:i:s');
    
    // 1. Validar campos vacíos
    if (empty($nomusu) || empty($emausu) || empty($pasusu) || empty($pasusu2) || empty($tdousu) || empty($ndousu)) {
        header("Location: ../index.php?pg=$pg&err=campos_vacios");
        exit;
    }

    // 2. Validar que las contraseñas coincidan
    if ($pasusu !== $pasusu2) {
        header("Location: ../index.php?pg=$pg&err=pass_mismatch");
        exit;
    }
    
    // 3. Hashear la contraseña
    $pas_hash = generar_hash_contrasena($pasusu); 

    // 4. Asignar los valores para verificación de duplicados
    $muser->setEmausu($emausu);
    $muser->setNdousu($ndousu); 

    // 5. Verificar si el usuario ya existe
    $existe = $muser->checkIfExists(); 

    if ($existe) {
        header("Location: ../index.php?pg=$pg&err=user_exists");
        exit;
    }

    // 6. Asignar el resto de valores al Modelo
    $muser->setNomusu($nomusu);
    $muser->setApeusu($apeusu);
    $muser->setTdousu($tdousu);
    $muser->setNdousu($ndousu); 
    $muser->setCelusu($celusu);
    $muser->setPasusu($pas_hash); 
    $muser->setIdper($idper);     
    $muser->setAct($act);
    $muser->setFec_crea($fec_crea);
    $muser->setFec_actu($fec_actu);

    // 7. Guardar el usuario y capturar el ID
    $id_nuevo_usuario = $muser->save(); 

    if ($id_nuevo_usuario) {
        $id_para_url = $id_nuevo_usuario; 
        header("Location: ../index.php?pg=regemp&idusu_token=$id_para_url"); 
        exit;
    } else {
        header("Location: ../index.php?pg=$pg&err=db_error");
        exit;
    }
    
} else {
    header("Location: ../index.php?pg=$pg");
    exit;
}
?>