<?php
// ===============================================
// Archivo: models/control.php
// Objetivo: Autenticación de usuario y creación de la sesión completa,
//           incluyendo ID y Nombre de la Empresa.
// ===============================================

require_once('conexion.php');
require_once('../controllers/misfun.php'); 
require_once('../models/maud.php');

$usu = isset($_POST['usu']) ? $_POST['usu'] : NULL; // Email o usuario
$pas = isset($_POST['pas']) ? $_POST['pas'] : NULL;

if ($usu && $pas) {
    // Llamamos directamente a la función de autenticación
    validar($usu, $pas); 
} else {
    // Si falta usuario o contraseña
    echo '<script>window.location="../index.php?err=campos_vacios";</script>'; 
}

function validar($usu, $pas) {
    $res = verdat($usu, $pas);
    
    if ($res) {
        $usuario_data = $res[0];

        // 🎯 VALIDACIÓN DE ESTADO 🎯
        if ($usuario_data['usu_act'] == 0) {
            $maud = new MAud();
            $maud->registrarLogin(
                $usuario_data['idemp'] ?? null, 
                $usuario_data['idusu'], 
                $usu, 
                0, 
                $_SERVER['REMOTE_ADDR'], 
                $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido'
            );
            echo '<script>window.location="../index.php?err=inactivo_usu";</script>';
            return;
        }

        if ($usuario_data['idper'] != 1 && $usuario_data['emp_act'] == 0) {
            $maud = new MAud();
            $maud->registrarLogin(
                $usuario_data['idemp'] ?? null, 
                $usuario_data['idusu'], 
                $usu, 
                0, 
                $_SERVER['REMOTE_ADDR'], 
                $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido'
            );
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

        $maud = new MAud();
        $maud->registrarLogin(
            $usuario_data['idemp'] ?? null, 
            $usuario_data['idusu'], 
            $usu, 
            1, 
            $_SERVER['REMOTE_ADDR'], 
            $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido'
        );

        echo '<script>window.location="../home.php";</script>';
    } else {
        // Error de credenciales
        $maud = new MAud();
        $maud->registrarLogin(
            null, 
            null, 
            $usu, 
            0, 
            $_SERVER['REMOTE_ADDR'], 
            $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido'
        );
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

    $modelo = new conexion(); // FIX: Linux es case-sensitive, debe ser minúscula
    $conexion = $modelo->get_conexion();
    $result = $conexion->prepare($sql);
    $result->bindParam(':emausu', $usu);
    $result->bindParam(':pasusu', $pas);
    $result->execute();
    return $result->fetchAll(PDO::FETCH_ASSOC);
}
?>