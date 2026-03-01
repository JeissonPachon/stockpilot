<?php
// ===============================================
// Archivo: models/control.php
// Objetivo: Autenticación de usuario y creación de la sesión completa,
//           incluyendo ID y Nombre de la Empresa con verificación reCAPTCHA V3.
// ===============================================

require_once('conexion.php');
require_once('../controllers/misfun.php'); 

// ⬇️ DEFINICIÓN DE CLAVES Y SCORE
// Clave Secreta obtenida de Google
define('RECAPTCHA_SECRET_KEY', '6LerVXwsAAAAAO1IVu4NPPU6LkWuc0evHbgnqsbm'); 
define('RECAPTCHA_SCORE_MINIMO', 0.1); // Puntuación mínima para entorno local

$usu = isset($_POST['usu']) ? $_POST['usu'] : NULL; // Email o usuario
$pas = isset($_POST['pas']) ? $_POST['pas'] : NULL;
// Recibir el token de reCAPTCHA desde vinis.php
$recaptcha_token = $_POST['recaptchaResponse'] ?? NULL;

if ($usu && $pas && $recaptcha_token) {
    
    // ⬇️ LÓGICA DE VERIFICACIÓN RECAPTCHA
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret'   => RECAPTCHA_SECRET_KEY,
        'response' => $recaptcha_token,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ],
        // Parche SSL para evitar errores de conexión en XAMPP/Windows
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ];

    $context  = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    $response_keys = json_decode($response, true);
    
    // Verificación de reCAPTCHA:
    // En local a veces el 'action' puede variar, así que validamos principalmente el éxito y el puntaje.
    if (!$response_keys["success"] || $response_keys["score"] < RECAPTCHA_SCORE_MINIMO) {
        // Falló la verificación de seguridad
        echo '<script>window.location="../index.php?err=recaptcha_fail";</script>';
        exit;
    }
    // ⬆️ FIN LÓGICA RECAPTCHA V3
    
    // Si pasa la verificación, llamamos a la función de autenticación
    validar($usu, $pas); 
    
} else {
    // Si falta usuario, contraseña o el token
    echo '<script>window.location="../index.php?err=campos_vacios";</script>'; 
}

function validar($usu, $pas) {
    $res = verdat($usu, $pas);
    
    if ($res) {
        $usuario_data = $res[0];

        // 🎯 VALIDACIÓN DE ESTADO 🎯
        if ($usuario_data['usu_act'] == 0) {
            echo '<script>window.location="../index.php?err=inactivo_usu";</script>';
            return;
        }

        if ($usuario_data['idper'] != 1 && $usuario_data['emp_act'] == 0) {
            echo '<script>window.location="../index.php?err=inactivo_emp";</script>';
            return;
        }

        session_start();
        
        $_SESSION['idusu']      = $usuario_data['idusu'];
        $_SESSION['nomusu']     = $usuario_data['nomusu'];
        $_SESSION['apeusu']     = $usuario_data['apeusu'];
        $_SESSION['emausu']     = $usuario_data['emausu'];
        $_SESSION['idper']      = $usuario_data['idper'];
        $_SESSION['nomper']     = $usuario_data['nomper'];
        $_SESSION['idemp']      = $usuario_data['idemp'] ?? NULL; 
        $_SESSION['nomemp']     = $usuario_data['nomemp'] ?? NULL;
        $_SESSION['aut']        = "askjhd654-+"; 

        echo '<script>window.location="../home.php";</script>';
    } else {
        // Error de credenciales
        echo '<script>window.location="../index.php?err=ok";</script>';
    }
}

function verdat($usu, $con) {
    $pas = generar_hash_contrasena($con);

    $sql = "SELECT u.idusu, u.nomusu, u.apeusu, u.emausu, u.pasusu, 
                    u.imgusu, u.idper, p.nomper, u.act AS usu_act,
                    e.idemp, e.nomemp, e.act AS emp_act
             FROM usuario AS u
             INNER JOIN perfil AS p ON u.idper = p.idper
             LEFT JOIN usuario_empresa AS ue ON ue.idusu = u.idusu
             LEFT JOIN empresa AS e ON e.idemp = ue.idemp
             WHERE u.emausu = :emausu AND u.pasusu = :pasusu
             LIMIT 1";

    $modelo = new Conexion();
    $conexion = $modelo->get_conexion();
    $result = $conexion->prepare($sql);
    $result->bindParam(':emausu', $usu);
    $result->bindParam(':pasusu', $pas);
    $result->execute();
    return $result->fetchAll(PDO::FETCH_ASSOC);
}
?>