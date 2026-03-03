<?php
require_once('models/musu.php');

// ✅ Si es superadmin, obtener empresas para mostrar en el formulario
$empresas = [];
if (isset($_SESSION['idper']) && $_SESSION['idper'] == 1) {
    require_once('models/memp.php'); // modelo de empresas
    $memp = new Memp();
    $empresas = $memp->getAll(); // debe existir este método
}

$musu = new Musu();

$idusu     = isset($_REQUEST['idusu']) ? $_REQUEST['idusu'] : NULL;
$nomusu    = isset($_POST['nomusu']) ? $_POST['nomusu'] : NULL;
$apeusu    = isset($_POST['apeusu']) ? $_POST['apeusu'] : NULL;
$tdousu    = isset($_POST['tdousu']) ? $_POST['tdousu'] : NULL;
$ndousu    = isset($_POST['ndousu']) ? $_POST['ndousu'] : NULL;
$celusu    = isset($_POST['celusu']) ? $_POST['celusu'] : NULL;
$emausu    = isset($_POST['emausu']) ? $_POST['emausu'] : NULL;
$pasusu    = isset($_POST['pasusu']) ? $_POST['pasusu'] : NULL;
$imgusu    = isset($_POST['imgusu']) ? $_POST['imgusu'] : NULL;
$idper     = isset($_POST['idper']) ? $_POST['idper'] : NULL;
$idemp     = isset($_POST['idemp']) ? $_POST['idemp'] : NULL; // ✅ NUEVO: empresa seleccionada opcionalmente
$tokreset  = isset($_POST['tokreset']) ? $_POST['tokreset'] : NULL;
$fecreset  = isset($_POST['fecreset']) ? $_POST['fecreset'] : NULL;
$ultlogin  = isset($_POST['ultlogin']) ? $_POST['ultlogin'] : NULL;
$fec_crea  = isset($_POST['fec_crea']) ? $_POST['fec_crea'] : date('Y-m-d H:i:s');
$fec_actu  = isset($_POST['fec_actu']) ? $_POST['fec_actu'] : date('Y-m-d H:i:s');
$act       = isset($_POST['act']) ? $_POST['act'] : 1;

// ✅ NUEVO: Procesar carga de imagen
$image_error = null;
if (isset($_FILES['imgusu']) && $_FILES['imgusu']['error'] == 0) {
    $upload_dir = 'img/uploads/usuarios/';
    
    // Crear directorio si no existe
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file_name = $_FILES['imgusu']['name'];
    $file_tmp = $_FILES['imgusu']['tmp_name'];
    $file_size = $_FILES['imgusu']['size'];
    $file_error = $_FILES['imgusu']['error'];
    
    // Validar tamaño (máximo 5MB)
    $max_size = 5 * 1024 * 1024; // 5MB
    if ($file_size > $max_size) {
        $image_error = 'El archivo es muy grande. Máximo 5MB.';
    }
    
    // Validar tipo de archivo
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = mime_content_type($file_tmp);
    if (!in_array($file_type, $allowed_types)) {
        $image_error = 'Formato de imagen no permitido. Use JPG, PNG o GIF.';
    }
    
    if (!$image_error) {
        // Generar nombre único para la imagen
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $unique_filename = 'usuario_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
        $file_path = $upload_dir . $unique_filename;
        
        if (move_uploaded_file($file_tmp, $file_path)) {
            $imgusu = $file_path; // Guardar la ruta relativa
        } else {
            $image_error = 'Error al subir el archivo. Intente de nuevo.';
        }
    }
} else if (isset($_FILES['imgusu']) && $_FILES['imgusu']['error'] != 4) {
    // Error 4 = no se subió archivo (normal si no se selecciona)
    $image_error = 'Error en la carga del archivo.';
}

$ope = isset($_REQUEST['ope']) ? $_REQUEST['ope'] : NULL;
$datOne = NULL;

$musu->setIdusu($idusu);

if($ope == "save"){
    // Validar si hay error en la carga de imagen
    if ($image_error) {
        header("Location: home.php?pg=$pg&error=" . urlencode($image_error));
        exit;
    }
    
    $musu->setNomusu($nomusu);
    $musu->setApeusu($apeusu);
    $musu->setTdousu($tdousu);
    $musu->setNdousu($ndousu);
    $musu->setCelusu($celusu);
    $musu->setEmausu($emausu);
    $musu->setPasusu($pasusu);
    $musu->setImgusu($imgusu);
    $musu->setIdper($idper);
    $musu->setTokreset($tokreset);
    $musu->setFecreset($fecreset);
    $musu->setUltlogin($ultlogin);
    $musu->setFec_crea($fec_crea);
    $musu->setFec_actu($fec_actu);
    $musu->setAct($act);

    if(!$idusu){
        // 🟢 Guardar usuario nuevo
        $idusu = $musu->save();

        // ✅ NUEVO: si el superadmin asignó empresa, crear relación usuario–empresa
        if ($idusu && isset($_SESSION['idper']) && $_SESSION['idper'] == 1 && !empty($idemp)) {
            require_once('models/musemp.php');
            $usemp = new Musemp();
            $usemp->setIdusu($idusu);
            $usemp->setIdemp($idemp);
            $usemp->setFec_crea(date('Y-m-d H:i:s'));
            $usemp->save();
        }

    } else {
        // 🟡 Editar usuario existente
        $musu->edit();
    }
}

/* 🚨 LÓGICA ELIMINADA: 
if($ope == "eli" && $idusu){
    $musu->del();
} 
Ahora lo maneja cdelete.php */

if($ope == "edi" && $idusu){
    $datOne = $musu->getOne();
}

$datAll = $musu->getAll();
?>
