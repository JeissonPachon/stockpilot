<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StockPilot | Gestión de Inventario</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />

    <link rel="stylesheet" type="text/css" href="css/style.css">

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 

    <script src="https://www.google.com/recaptcha/api.js?render=6LerVXwsAAAAAHShT8zytfLCwrjRxd0D-Z-lo8Jq"></script>
</head>
<body>
    <?php 
        require_once ("models/conexion.php");
        
        // Lógica de carga de vistas
        $pg = $_GET['pg'] ?? 'inicio';
        $vista = 'views/vinis.php'; 

        if ($pg === 'olvido') {
            $vista = 'views/volv.php';
        } elseif ($pg === 'reset') {
            $vista = 'views/vrct.php';
        } elseif ($pg === 'registro') {
            $vista = 'views/vregusu.php';
        } elseif ($pg === 'regemp') {
            $vista = 'views/vregemp.php'; 
        }
    ?>

    <main class="login-wrapper">
        <div class="login-side-visual d-none d-lg-flex">
            <div class="overlay-content">
                <div class="brand">
                    <i class="fas fa-boxes-packing"></i>
                    <span>StockPilot</span>
                </div>
                
                <div class="visual-text">
                    <h1>Tu inventario, <br>bajo control total.</h1>
                    <p>Gestiona stock, proveedores y almacenes en una sola plataforma ágil y moderna.</p>
                </div>

                <div class="visual-footer">
                    <p>© 2026 StockPilot Colombia. Todos los derechos reservados.</p>
                </div>
            </div>
        </div>

        <div class="login-side-form">
            <div class="form-scroll-area">
                <div class="form-container-inner">
                    <div class="mobile-brand d-lg-none">
                        <i class="fas fa-boxes-packing text-primary"></i>
                        <span>StockPilot</span>
                    </div>

                    <?php include($vista); ?>
                    
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const urlParams = new URLSearchParams(window.location.search);
            const err = urlParams.get('err');
            let title = '', text = '', show_alert = true;

            switch(err) {
                case 'inactivo_usu':
                    title = '¡Acceso Denegado! 🚫';
                    text = 'Tu cuenta de usuario ha sido desactivada.';
                    break;
                case 'inactivo_emp':
                    title = '¡Acceso Denegado! 🏢❌';
                    text = 'La empresa ha sido desactivada.';
                    break;
                case 'ok':
                    title = '¡Error de Credenciales! 🔑';
                    text = 'Correo o contraseña incorrectos.';
                    break;
                // ⬇️ 2. ALERTA PARA FALLO DE RECAPTCHA
                case 'recaptcha_fail':
                    title = 'Seguridad reCAPTCHA 🤖';
                    text = 'No se pudo verificar que seas un humano. Inténtalo de nuevo.';
                    break;
                case 'campos_vacios':
                    title = 'Campos Incompletos 📝';
                    text = 'Por favor, llena todos los datos e intenta de nuevo.';
                    break;
                default:
                    show_alert = false;
                    break;
            }

            if (show_alert) {
                Swal.fire({
                    icon: 'error',
                    title: title,
                    text: text,
                    confirmButtonColor: '#3b5998',
                    confirmButtonText: 'Aceptar'
                });
            }
        });
    </script>
    <script src="js/code.js"></script>
</body>
</html>