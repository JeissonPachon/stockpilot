<div class="inis">
    <h2>Inicio de Sesión</h2>
    <form name="frm1" id="frm1" action="models/control.php" method="POST">
        <div class="row">
            <div class="form-group col-md-12">
                <label for="usu"><i class="fa-solid fa-user"></i> Usuario</label>
                <input type="text" name="usu" id="usu" class="form-control" placeholder="Ingresa tu usuario" required>
            </div>
            <div class="form-group col-md-12">
                <label for="pas"><i class="fa-solid fa-key"></i> Contraseña</label>
                <input type="password" name="pas" id="pas" class="form-control" placeholder="Ingresa tu contraseña" required>
            </div>
            
            <input type="hidden" name="recaptchaResponse" id="recaptchaResponse">
            
            <?php 
            $err = isset($_GET['err']) ? $_GET['err'] : NULL;
            if($err == "ok" || $err == "recaptcha_fail" || $err == "campos_vacios"){ 
            ?>
            <div class="form-group col-md-12 derr">
                <i class="fa-solid fa-triangle-exclamation"></i> 
                <?php 
                if ($err == "recaptcha_fail") {
                    echo "Falló la verificación de seguridad (reCAPTCHA).";
                } elseif ($err == "campos_vacios") {
                    echo "Faltan campos obligatorios."; 
                } else {
                    echo "¡Datos Incorrectos!";
                }
                ?>
            </div>
            <?php } ?>
            
            <div class="form-group col-md-12">
                <button type="submit" class="form-control btn btn-primary">Ingresar</button>
                <small class="form-text text-muted" style="text-align: center; display: block; margin-top: 10px;">
                    Protegido por reCAPTCHA
                </small>
            </div>
            
            <div class="forgot-password col-md-12">
                <a href="index.php?pg=olvido">¿Olvidaste tu contraseña?</a>
            </div>
            
            <div class="register-text col-md-12">
                <p>¿Aún no trabajas con nosotros? <a href="index.php?pg=registro" class="register-link">Regístrate</a></p>
            </div>
        </div>
    </form>
    
    <script src="https://www.google.com/recaptcha/api.js?render=6LerVXwsAAAAAHShT8zytfLCwrjRxd0D-Z-lo8Jq"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('frm1');

        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Detiene el envío para obtener el token primero

            grecaptcha.ready(function() {
                grecaptcha.execute('6LerVXwsAAAAAHShT8zytfLCwrjRxd0D-Z-lo8Jq', {action: 'login'}).then(function(token) {
                    // Se asigna el token al input oculto
                    document.getElementById('recaptchaResponse').value = token;
                    // Ahora sí se envía el formulario
                    form.submit();
                });
            });
        });
    });
    </script>
</div>