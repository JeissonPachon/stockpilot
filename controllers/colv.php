<?php
// ----------------------------------------------------
// 1. INCLUSIONES
// ----------------------------------------------------
require_once ('../vendor/autoload.php'); 
require_once ('../models/conexion.php');
require_once ('../models/molv.php');
require_once ("cmail.php"); 

// ⬇️ CONFIGURACIÓN RECAPTCHA
define('RECAPTCHA_SECRET_KEY', '6LerVXwsAAAAAO1IVu4NPPU6LkWuc0evHbgnqsbm');
define('RECAPTCHA_SCORE_MINIMO', 0.1);

$molv = new Molv();

// ----------------------------------------------------
// 2. RECEPCIÓN Y CONFIGURACIÓN
// ----------------------------------------------------
$emausu = $_POST["emausu"] ?? NULL;
$recaptcha_token = $_POST['recaptchaResponse'] ?? NULL; // ⬅️ Recibimos el token
date_default_timezone_set('America/Bogota'); 

if ($emausu) {

    // --- ⬇️ INICIO VALIDACIÓN RECAPTCHA V3 ⬇️ ---
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
        echo "<script>alert('Error de verificación de seguridad (reCAPTCHA). Intente de nuevo.'); window.location.href='../index.php?pg=olvido';</script>";
        exit;
    }
    // --- ⬆️ FIN VALIDACIÓN RECAPTCHA V3 ⬆️ ---
    
    // 3. BUSCAR USUARIO POR CORREO
    $molv->setEmausu($emausu);
    $dtAll = $molv->getOneEma(); 
    
    if ($dtAll) {
        
        // 4. GENERAR TOKEN Y FECHA
        $key_para_email = genPass(15); 
        $key_para_bd = sha1(md5($key_para_email)); 
        $fecsol = date('Y-m-d H:i:s');
        
        // 5. ACTUALIZAR MODELO Y BD
        $molv->setFecsol($fecsol);
        $molv->setKeyolv($key_para_bd); 
        $molv->setIdusu($dtAll['idusu']); 
        
        $molv->updUsu(); 
        
        // 6. PREPARAR Y ENVIAR CORREO
        $titu = "Cambiar clave de ingreso en StockPilot"; 
        $mens = plaOlvCon($dtAll['nomusu'], $emausu, $key_para_email); 
        
        envmail($emausu, $titu, $mens); 
        
        // 7. MENSAJE DE ÉXITO
        echo "<script>alert('Revise el e-mail ". $emausu. " y siga los pasos para recordar su contraseña.');</script>";
        
    } else {
        // 8. MENSAJE DE ERROR (CORREO NO REGISTRADO)
        echo "<script>alert('Este e-mail no se encuentra registrado en nuestro sistema. Por favor verifíquelo nuevamente.');</script>";
    }
    
    // 9. REDIRECCIÓN FINAL
    echo "<script>window.location.href='../index.php';</script>"; 
}

// ----------------------------------------------------
// 10. FUNCIÓN PARA GENERAR EL TOKEN (genPass)
// ----------------------------------------------------
function genPass($len){
    $key = "";
    $pattern = "1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM"; 
    $max = strlen($pattern)-1;
    
    for($i=0; $i<$len; $i++){
        $key .= substr($pattern, mt_rand(0,$max), 1); 
    }
    
    return $key;
}
?>