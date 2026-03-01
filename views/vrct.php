<div class="inis">
    <h2>🔒 Restablecer Contraseña</h2>
    
    <?php
    // 1. Recepción y validación inicial de datos
    $keyolv = $_POST['ko'] ?? NULL;
    $emausu_post = $_POST['m1'] ?? NULL; 
    
    // 2. Manejo de mensajes de error/éxito
    $msg = $_GET['msg'] ?? NULL;
    
    if (empty($keyolv) || empty($emausu_post)) {
    ?>
        <div class="form-group col-md-12 derr">
            <i class="fa-solid fa-triangle-exclamation"></i> Acceso denegado o enlace inválido.
        </div>
        <div class="forgot-password col-md-12">
            <a href="index.php?pg=olvido">Solicitar nuevo enlace</a>
        </div>
    <?php
    } else {
    ?>
    
    <form name="frm_cambio_pas" id="frm_cambio_pas" action="controllers/crct.php" method="POST">
        <input type="hidden" name="recaptchaResponse" id="recaptchaResponse">

        <div class="row">
            <div class="form-group col-md-12">
                <label for="pas1"><i class="fa-solid fa-lock"></i> Nueva Contraseña</label>
                <input type="password" name="pas1" id="pas1" class="form-control" placeholder="Ingresa tu nueva contraseña" required>
            </div>
            
            <div class="form-group col-md-12">
                <label for="pas2"><i class="fa-solid fa-lock"></i> Repetir Contraseña</label>
                <input type="password" name="pas2" id="pas2" class="form-control" placeholder="Repite la nueva contraseña" required>
            </div>
            
            <input type="hidden" name="keyolv" value="<?php echo htmlspecialchars($keyolv); ?>">
            <input type="hidden" name="emausu" value="<?php echo htmlspecialchars($emausu_post); ?>">
            
            <?php 
            if ($msg == "match") {
            ?>
                <div class="form-group col-md-12 derr">
                    <i class="fa-solid fa-triangle-exclamation"></i> **¡Error!** Las contraseñas no coinciden.
                </div>
            <?php } elseif ($msg == "expired") { ?>
                <div class="form-group col-md-12 derr">
                    <i class="fa-solid fa-triangle-exclamation"></i> **Error de tiempo.** El enlace ha expirado.
                </div>
            <?php } elseif ($msg == "recaptcha_fail") { ?>
                <div class="form-group col-md-12 derr">
                    <i class="fa-solid fa-robot"></i> **Seguridad:** Falló la verificación reCAPTCHA.
                </div>
            <?php } elseif ($msg == "invalid" || $msg == "notfound") { ?>
                <div class="form-group col-md-12 derr">
                    <i class="fa-solid fa-triangle-exclamation"></i> **Enlace inválido.** El código ya fue usado o no existe.
                </div>
            <?php } ?>
            
            <div class="form-group col-md-12">
                <input type="submit" value="Cambiar Contraseña" class="form-control btn btn-primary">
            </div>
            
            <div class="forgot-password col-md-12">
                <a href="index.php">Volver a Iniciar Sesión</a>
            </div>
        </div>
    </form>

    <script>
    document.getElementById('frm_cambio_pas').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        grecaptcha.ready(function() {
            grecaptcha.execute('6LerVXwsAAAAAHShT8zytfLCwrjRxd0D-Z-lo8Jq', {action: 'reset_pass'}).then(function(token) {
                document.getElementById('recaptchaResponse').value = token;
                form.submit();
            });
        });
    });
    </script>
    
    <?php } ?>
</div>