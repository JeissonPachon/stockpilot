<div class="inis-registro">
    <h2>Registro de Usuario</h2>
    <h3>Paso 1 de 2: Datos Personales</h3>
    
    <?php 
    $err = $_GET['err'] ?? null;
    $msg = $_GET['msg'] ?? null; 
    ?>

    <?php if ($msg == 'registered') { ?>
        <div class="form-group col-md-12 alert alert-success mt-3">
            <i class="fa-solid fa-check-circle"></i> 
            ¡Registro exitoso! Ya puedes iniciar sesión.
        </div>
    <?php } ?>
    
    <?php if ($err) { ?>
        <div class="form-group col-md-12 derr mt-3" style="color: red; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px;">
            <i class="fa-solid fa-triangle-exclamation"></i> 
            <?php 
                if ($err == "user_exists") echo "El correo electrónico o el número de documento ya están registrados.";
                elseif ($err == "campos_vacios") echo "Faltan campos obligatorios o falló la seguridad.";
                elseif ($err == "pass_mismatch") echo "Las contraseñas no coinciden.";
                elseif ($err == "recaptcha_fail") echo "Falló la verificación de seguridad reCAPTCHA. Intente de nuevo.";
                elseif ($err == "db_error") echo "Error al guardar los datos. Intente de nuevo.";
                else echo "Ocurrió un error desconocido.";
            ?>
        </div>
    <?php } ?>
    
    <form name="frm_registro" id="frm_registro" action="controllers/cregusu.php" method="POST">
        <input type="hidden" name="recaptchaResponse" id="recaptchaResponse">

        <div class="row">
            <div class="form-group col-md-4">
                <label for="nomusu"><i class="fa-solid fa-user"></i> Nombre</label>
                <input type="text" name="nomusu" id="nomusu" class="form-control" placeholder="Tu Nombre" required>
            </div>
            
            <div class="form-group col-md-4">
                <label for="apeusu"><i class="fa-solid fa-user-tag"></i> Apellido</label>
                <input type="text" name="apeusu" id="apeusu" class="form-control" placeholder="Tu Apellido" required>
            </div>

            <div class="form-group col-md-4">
                <label for="tdousu"><i class="fa-solid fa-id-card"></i> Tipo Doc.</label>
                <select name="tdousu" id="tdousu" class="form-control" required>
                    <option value="">Seleccione...</option>
                    <option value="CC">Cédula de Ciudadanía</option>
                    <option value="CE">Cédula de Extranjería</option>
                    <option value="PA">Pasaporte</option>
                </select>
            </div>

            <div class="form-group col-md-4">
                <label for="ndousu"><i class="fa-solid fa-hashtag"></i> Número de Documento</label>
                <input type="text" name="ndousu" id="ndousu" class="form-control" placeholder="Sin puntos ni comas" required>
            </div>

            <div class="form-group col-md-4">
                <label for="celusu"><i class="fa-solid fa-phone"></i> Celular</label>
                <input type="tel" name="celusu" id="celusu" class="form-control" placeholder="Ej: 300 123 4567">
            </div>

            <div class="form-group col-md-4">
                <label for="emausu"><i class="fa-solid fa-envelope"></i> Correo Electrónico</label>
                <input type="email" name="emausu" id="emausu" class="form-control" placeholder="ejemplo@tuempresa.com" required>
            </div>
            
            <div class="form-group col-md-6">
                <label for="pasusu"><i class="fa-solid fa-lock"></i> Contraseña</label>
                <input type="password" name="pasusu" id="pasusu" class="form-control" placeholder="Mínimo 8 caracteres" required>
            </div>

            <div class="form-group col-md-6">
                <label for="pasusu2"><i class="fa-solid fa-lock-open"></i> Confirmar Contraseña</label>
                <input type="password" name="pasusu2" id="pasusu2" class="form-control" placeholder="Repite la contraseña" required>
            </div>

            <div class="form-group col-md-12 mt-4">
                <button type="submit" class="btn btn-primary-custom btn-block w-100">
                    Registrar y Continuar
                </button>
                <small class="form-text text-muted" style="text-align: center; display: block; margin-top: 10px;">
                    Este formulario está protegido por reCAPTCHA.
                </small>
            </div>
            
            <div class="form-group col-md-12 register-text mt-3 text-center">
                <p>¿Ya tienes cuenta? <a href="index.php" class="register-link">Inicia Sesión</a></p>
            </div>
        </div>
    </form>

    <script>
    document.getElementById('frm_registro').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        grecaptcha.ready(function() {
            grecaptcha.execute('6LerVXwsAAAAAHShT8zytfLCwrjRxd0D-Z-lo8Jq', {action: 'registro_usuario'}).then(function(token) {
                document.getElementById('recaptchaResponse').value = token;
                form.submit();
            });
        });
    });
    </script>
</div>