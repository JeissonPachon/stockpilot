<?php
// ===============================================
// Archivo: controllers/CRegEmp.php (CORREGIDO CON reCAPTCHA)
// Objetivo: Procesar el registro de la empresa y la vinculación Usuario-Empresa (Paso 2)
// ===============================================

if (session_status() == PHP_SESSION_NONE) { 
    session_start(); 
}

require_once('../models/memp.php'); 
require_once('../models/conexion.php'); 

$pg = 'regemp'; 

// ⬇️ DEFINICIÓN DE CLAVES PARA RECAPTCHA
define('RECAPTCHA_SECRET_KEY', '6LerVXwsAAAAAO1IVu4NPPU6LkWuc0evHbgnqsbm');
define('RECAPTCHA_SCORE_MINIMO', 0.1);

function fetchUserDetails($idusu) {
    $sql = "SELECT u.nomusu, u.apeusu, p.nomper, u.idper 
            FROM usuario AS u
            INNER JOIN perfil AS p ON u.idper = p.idper
            WHERE u.idusu = :idusu
            LIMIT 1";

    $modelo = new Conexion();
    $conexion = $modelo->get_conexion();
    $result = $conexion->prepare($sql);
    $result->bindParam(':idusu', $idusu, PDO::PARAM_INT);
    $result->execute();
    return $result->fetch(PDO::FETCH_ASSOC); 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- ⬇️ INICIO VALIDACIÓN RECAPTCHA V3 ⬇️ ---
    $recaptcha_token = $_POST['recaptchaResponse'] ?? NULL;
    $idusu_token = $_POST['idusu_token'] ?? NULL; // Lo necesitamos para la redirección si falla

    if (!$recaptcha_token) {
        header("Location: ../index.php?pg=$pg&idusu_token=$idusu_token&err=campos_vacios");
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
        header("Location: ../index.php?pg=$pg&idusu_token=$idusu_token&err=recaptcha_fail");
        exit;
    }
    // --- ⬆️ FIN VALIDACIÓN RECAPTCHA V3 ⬆️ ---


    $memp = new Memp();

    // 1. Recepción de datos del POST
    $nomemp     = $_POST['nomemp'] ?? NULL;
    $razemp     = $_POST['razemp'] ?? NULL;
    $nitemp     = $_POST['nitemp'] ?? NULL;
    $diremp     = $_POST['diremp'] ?? NULL;
    $telemp     = $_POST['telemp'] ?? NULL;
    $emaemp     = $_POST['emaemp'] ?? NULL;
    
    $logo_nombre_final = 'logo.png'; 
    $target_dir = "../img/logos/"; // Ajustado el path para subir desde controllers/

    if (isset($_FILES['logoemp']) && $_FILES['logoemp']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['logoemp']['name'];
        $file_tmp_name = $_FILES['logoemp']['tmp_name'];
        $file_size = $_FILES['logoemp']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_exts = array("jpg", "jpeg", "png");

        if (in_array($file_ext, $allowed_exts) && $file_size <= 5000000) {
            $new_file_name = uniqid('logo_', true) . '.' . $file_ext;
            $target_file = $target_dir . $new_file_name;

            if (move_uploaded_file($file_tmp_name, $target_file)) {
                $logo_nombre_final = $new_file_name;
            }
        }
    }

    $idusu_crea = (int) $idusu_token; 

    if (empty($nomemp) || empty($razemp) || empty($nitemp) || empty($diremp) || empty($emaemp) || $idusu_crea <= 0) {
        header("Location: ../index.php?pg=$pg&idusu_token=$idusu_token&err=campos_vacios");
        exit;
    }

    $memp->setNomemp($nomemp);
    $memp->setRazemp($razemp);
    $memp->setNitemp($nitemp);
    $memp->setDiremp($diremp);
    $memp->setTelemp($telemp);
    $memp->setEmaemp($emaemp);
    $memp->setLogo($logo_nombre_final); 
    $memp->setIdusu($idusu_crea); 
    
    $memp->setAct(1);
    $memp->setEstado(1);
    $fec_crea = date('Y-m-d H:i:s');
    $memp->setFec_crea($fec_crea);
    $memp->setFec_actu($fec_crea);

    $id_nueva_empresa = $memp->insertNewEmpresa(); 

    if ($id_nueva_empresa > 0) {
        if ($memp->linkUsuEmp($idusu_crea, $id_nueva_empresa)) {
            $user_details = fetchUserDetails($idusu_crea); 
            session_regenerate_id(true); 

            $_SESSION['idusu'] = $idusu_crea;
            $_SESSION['idemp'] = $id_nueva_empresa;
            
            $idper_creador = $user_details['idper'] ?? 2; 
            $_SESSION['idper'] = $idper_creador; 
            
            if ($user_details) {
                $_SESSION['nomusu'] = $user_details['nomusu']; 
                $_SESSION['apeusu'] = $user_details['apeusu']; 
                $_SESSION['nomper'] = $user_details['nomper']; 
            } else {
                $_SESSION['nomusu'] = 'Usuario';
                $_SESSION['apeusu'] = 'Nuevo';
                $_SESSION['nomper'] = 'Administrador';
            }
            
            $_SESSION['aut'] = "askjhd654-+"; 
            session_write_close();
            
            header("Location: ../home.php"); 
            exit;
            
        } else {
            header("Location: ../index.php?pg=$pg&idusu_token=$idusu_token&err=db_error_link");
            exit;
        }
    } else {
        header("Location: ../index.php?pg=$pg&idusu_token=$idusu_token&err=db_error_emp");
        exit;
    }
    
} else {
    header("Location: ../index.php?pg=registro");
    exit;
}