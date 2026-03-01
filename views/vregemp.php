<?php
// ===============================================
// Archivo: views/VRegEmp.php
// Objetivo: Formulario de registro de la empresa (Paso 2)
// ===============================================

$idusu_token = $_GET['idusu_token'] ?? null;
$error = $_GET['err'] ?? null;

if (empty($idusu_token)) {
    header("Location: index.php?pg=registro&err=session_error");
    exit;
}

$razemp_val = '';
$nomemp_val = '';
$nitemp_val = '';
$diremp_val = '';
$telemp_val = '';
$emaemp_val = '';
?>

<div class="inis-registro">
    <h2>Registro del Sistema</h2>
    <h3 class="step-title">Paso 2 de 2: Datos de la Empresa</h3>
    
    <?php if ($error) { ?>
        <div class="form-group col-md-12 derr mt-3">
            <i class="fa-solid fa-triangle-exclamation"></i> 
            <?php 
                if ($error == "campos_vacios") echo "Faltan campos obligatorios o falló la seguridad.";
                elseif ($error == "recaptcha_fail") echo "Error de verificación reCAPTCHA. Intente de nuevo.";
                elseif ($error == "db_error_emp") echo "Error al registrar la empresa. Verifica el NIT.";
                elseif ($error == "session_error") echo "Error de sesión. Vuelva a empezar.";
                else echo "Ocurrió un error desconocido.";
            ?>
        </div>
    <?php } ?>
    
    <form name="frm_regemp" id="frm_regemp" action="controllers/CRegEmp.php" method="POST" enctype="multipart/form-data">
        
        <input type="hidden" name="idusu_token" value="<?php echo htmlspecialchars($idusu_token); ?>">
        
        <input type="hidden" name="recaptchaResponse" id="recaptchaResponse">

        <div class="row">
            <div class="form-group col-md-6">
                <label for="razemp"><i class="fa-solid fa-building"></i> Razón Social</label>
                <input type="text" name="razemp" id="razemp" class="form-control" placeholder="Ej: Innovaciones Globales S.A.S." required>
            </div>
            
            <div class="form-group col-md-6">
                <label for="nomemp"><i class="fa-solid fa-store"></i> Nombre Comercial</label>
                <input type="text" name="nomemp" id="nomemp" class="form-control" placeholder="Ej: Sistema iGlob" required>
            </div>
            
            <div class="form-group col-md-4">
                <label for="nitemp"><i class="fa-solid fa-id-card-clip"></i> NIT / ID Tributario</label>
                <input type="text" name="nitemp" id="nitemp" class="form-control" placeholder="Ej: 9001234567" required>
            </div>

            <div class="form-group col-md-4">
                <label for="emaemp"><i class="fa-solid fa-envelope"></i> Correo de la Empresa</label>
                <input type="email" name="emaemp" id="emaemp" class="form-control" placeholder="contacto@tuempresa.com" required>
            </div>

            <div class="form-group col-md-4">
                <label for="telemp"><i class="fa-solid fa-phone-volume"></i> Teléfono</label>
                <input type="tel" name="telemp" id="telemp" class="form-control" placeholder="Ej: 604 444 5555">
            </div>

            <div class="form-group col-md-12">
                <label for="diremp"><i class="fa-solid fa-location-dot"></i> Dirección Física</label>
                <input type="text" name="diremp" id="diremp" class="form-control" placeholder="Dirección completa" required>
            </div>
            
            <div class="form-group col-md-12">
                <label for="logoemp"><i class="fa-solid fa-image"></i> Logo de la Empresa (Opcional)</label>
                <input type="file" name="logoemp" id="logoemp" class="form-control" accept="image/png, image/jpeg, image/jpg">
            </div>

            <div class="form-group col-md-12 mt-4">
                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="fa-solid fa-save"></i> Finalizar Registro de Empresa
                </button>
            </div>
        </div>
    </form>

    <script>
    document.getElementById('frm_regemp').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        grecaptcha.ready(function() {
            grecaptcha.execute('6LerVXwsAAAAAHShT8zytfLCwrjRxd0D-Z-lo8Jq', {action: 'registro_empresa'}).then(function(token) {
                document.getElementById('recaptchaResponse').value = token;
                form.submit();
            });
        });
    });
    </script>
</div>