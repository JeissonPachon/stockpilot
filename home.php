<?php 
// 🔑 CORRECCIÓN CRÍTICA: Procesar el controlador Cemp.php ANTES de la validación de seguridad (seg.php).

// 1. Procesar la empresa (cemp.php) si es una operación de 'save'
// NOTA: Usamos $_REQUEST ya que puede venir de GET (para editar) o POST (para guardar).
// CORRECCIÓN: Solo ejecutar Cemp.php si estamos en la página de empresas (pg=1001 o no definido)
$pg = isset($_REQUEST['pg']) ? $_REQUEST['pg'] : NULL;
if (isset($_REQUEST['ope']) && $_REQUEST['ope'] == 'save' && isset($_REQUEST['idemp']) && (!$pg || $pg == 1001)) {
    // Si la operación es guardar, ejecuta Cemp.php inmediatamente.
    // Cemp.php tiene la lógica de redirección con exit();
    require_once("controllers/Cemp.php"); 
    // Si Cemp.php redirigió con éxito, el script aquí se detiene con exit().
    // Si no, sigue a la validación de seguridad para cargar la vista de edición.
}

// 2. Ejecutar la Validación de Seguridad (seg.php)
// Si llegamos a este punto, no se hizo un POST/save o ya se procesó.
require_once("models/seg.php");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StockPilot</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>

    <script src="https://cdn.datatables.net/2.3.0/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.3.0/js/dataTables.bootstrap5.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.0/css/dataTables.bootstrap5.css">

    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" type="text/css" href="css/menu.css">
</head>

<body>
    <?php
    // Obtención de $pg (Esta es la única definición necesaria en este punto)
    $pg = isset($_REQUEST["pg"]) ? $_REQUEST["pg"]:NULL; 
        require_once("models/conexion.php");
    ?>

    <?php require_once("views/vmen.php"); ?>
    <div id="main-content-wrapper">
    <header>
        <?php
        require_once("views/cabecera.php");
        // require_once("views/cabecera.php");
        // require_once("views/vpf.php"); // Eliminada
        require_once("controllers/misfun.php");
        ?>
    </header>
    <section>
        <?php
          // *** SE ELIMINÓ LA LÍNEA REDUNDANTE DE $pg AQUÍ ***
             if(!$pg OR $pg==1001)
                require_once("views/vemp.php"); // 1001: Empresas
             elseif($pg==1002)
                require_once("views/vprod.php"); // 1002: Productos
             elseif($pg==1003)
                require_once("views/vprov.php"); // 1003: Proveedores
             elseif($pg==1004)
                require_once("views/vusemp.php"); // 1004: Empleados
                 
             // *** INICIO DE CONDICIONES AGREGADAS (1005 a 1019) ***
             elseif($pg==1005)
                require_once("views/vcat.php"); // 1005: Categorías
             elseif($pg==1006)
                require_once("views/vaud.php"); // 1006: Auditoría
             elseif($pg==1007)
                require_once("views/vkard.php"); // 1007: Kardex
             elseif($pg==1008)
                require_once("views/vlote.php"); // 1008: Lotes
             elseif($pg==1009)
                require_once("views/vinv.php"); // 1009: Inventario
             elseif($pg==1010)
                require_once("views/vmovim.php"); // 1010: Movimientos
             elseif($pg==1011)
                require_once("views/vdom.php"); // 1011: Dominios
             elseif($pg==1012)
                require_once("views/vval.php"); // 1012: Valores
             elseif($pg==1013)
                require_once("views/vsosal.php"); // 1013: Solicitud Salida
             elseif($pg==1014)
                require_once("views/vdetsal.php"); // 1014: Detalle salida
             elseif($pg==1015)
                require_once("views/vsoent.php"); // 1015: Solicitud entrada
             elseif($pg==1016)
                require_once("views/vmodi.php"); // 1016: Modulo
             elseif($pg==1017)
                require_once("views/vubi.php"); // 1017: Ubicacion
             elseif($pg==1018)
                require_once("views/vusu.php"); // 1018: Usuarios
             elseif($pg==1019)
                require_once("views/vpag.php"); // 1019: Pagina
             // *** FIN DE CONDICIONES AGREGADAS ***
                 
             elseif($pg==1020)
                require_once("views/vper.php"); // 1020: Perfil (Vuelve a aparecer la que tenías)
             else
                echo "Pagina No Disponible Para Este Usuario";
         ?>
    </section>
    <footer>
        <?php
        require_once("views/pie.php");
        ?>
    </footer>
</div>
</body>
    <script src="js/code.js"></script>
</html>
