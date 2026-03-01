<?php
// ----------------------------------------------------
// 1. INCLUSIONES
// ----------------------------------------------------
require_once ('../models/conexion.php'); 
require_once ('../models/molv.php');
require_once ('../controllers/misfun.php');     

// ⬇️ CONFIGURACIÓN RECAPTCHA
define('RECAPTCHA_SECRET_KEY', '6LerVXwsAAAAAO1IVu4NPPU6LkWuc0evHbgnqsbm');
define('RECAPTCHA_SCORE_MINIMO', 0.1);

// 2. RECEPCIÓN DE DATOS
$ko      = $_POST["keyolv"] ?? NULL;
$emausu  = $_POST["emausu"] ?? NULL;
$pas1    = $_POST["pas1"]      ?? NULL;
$pas2    = $_POST["pas2"]      ?? NULL;
$recaptcha_token = $_POST['recaptchaResponse'] ?? NULL;

function hash_token($key) {
    return sha1(md5($key)); 
}

$molv = new Molv(); 
date_default_timezone_set('America/Bogota'); 

// 4. LÓGICA PRINCIPAL
if ($ko AND $emausu) {

    // --- ⬇️ INICIO VALIDACIÓN RECAPTCHA V3 ⬇️ ---
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = ['secret' => RECAPTCHA_SECRET_KEY, 'response' => $recaptcha_token];
    $options = [
        'http' => ['header' => "Content-type: application/x-www-form-urlencoded\r\n", 'method' => 'POST', 'content' => http_build_query($data)],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
    ];
    $context  = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    $response_keys = json_decode($response, true);

    if (!$response_keys["success"] || $response_keys["score"] < RECAPTCHA_SCORE_MINIMO) {
        echo "<script>alert('Error de seguridad reCAPTCHA.'); window.location.href='../index.php?pg=reset&msg=recaptcha_fail';</script>";
        exit;
    }
    // --- ⬆️ FIN VALIDACIÓN RECAPTCHA V3 ⬆️ ---
    
    $key_hasheado = hash_token($ko); 
    $molv->setKeyolv($key_hasheado);
    $molv->setEmausu($emausu);
    
    $dtAll = $molv->getOneKey();
    
    if ($dtAll) { 
        $idusu_db = $dtAll['idusu'];
        $bloqkey_db = $dtAll['bloqkey'];
        $fecsol_db = $dtAll['fecsol']; 
        
        if ($bloqkey_db == 0) {
            $tiempo_limite_segundos = 24 * 60 * 60; 
            if ((time() - strtotime($fecsol_db)) <= $tiempo_limite_segundos) {
                
                if ($pas1 === $pas2) {
                    $nueva_pas_hash = generar_hash_contrasena($pas1);
                    $molv->setIdusu($idusu_db); 
                    $molv->setPasusu($nueva_pas_hash);
                    $molv->setKeyolv(NULL); 
                    
                    if ($molv->updPasusu()) {
                        echo "<script>alert('Tu contraseña ha sido restablecida con éxito.');</script>";
                        echo "<script>window.location.href='../index.php';</script>";
                    } else {
                        echo "<script>alert('Error al actualizar.'); window.location.href='../index.php?pg=reset&msg=dberror';</script>";
                    }
                } else {
                    echo "<script>alert('Las contraseñas no coinciden.'); window.location.href='../index.php?pg=reset&msg=match';</script>";
                }
            } else {
                echo "<script>alert('Enlace expirado.'); window.location.href='../index.php?pg=reset&msg=expired';</script>";
            }
        } else {
            echo "<script>alert('Enlace ya utilizado.'); window.location.href='../index.php?pg=reset&msg=invalid';</script>";
        }
    } else {
        echo "<script>alert('Enlace inválido.'); window.location.href='../index.php?pg=reset&msg=notfound';</script>";
    }
} else {
    echo "<script>window.location.href='../index.php';</script>"; 
}
?>