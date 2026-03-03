<?php
require_once('controllers/cusu.php');
?>

<h2><i class="fas fa-user"></i> Gestión de Usuarios</h2>

<form action="home.php?pg=<?= $pg ?>" method="POST" enctype="multipart/form-data">
    <div class="row">

        <input type="hidden" name="idusu" value="<?php if($datOne && isset($datOne['idusu'])) echo $datOne['idusu']; ?>">
        <input type="hidden" name="ope" value="save">

        <div class="form-group col-md-6">
            <label for="nomusu">Nombre</label>
            <input type="text" name="nomusu" id="nomusu" class="form-control"
                value="<?php if($datOne) echo $datOne['nomusu']; ?>" required>
        </div>

        <div class="form-group col-md-6">
            <label for="apeusu">Apellido</label>
            <input type="text" name="apeusu" id="apeusu" class="form-control"
                value="<?php if($datOne) echo $datOne['apeusu']; ?>" required>
        </div>

        <div class="form-group col-md-6">
            <label for="tdousu">Tipo de documento</label>
            <input type="text" name="tdousu" id="tdousu" class="form-control"
                placeholder="CC, TI, CE, etc."
                value="<?php if($datOne) echo $datOne['tdousu']; ?>">
        </div>

        <div class="form-group col-md-6">
            <label for="ndousu">Número de documento</label>
            <input type="text" name="ndousu" id="ndousu" class="form-control"
                value="<?php if($datOne) echo $datOne['ndousu']; ?>">
        </div>

        <div class="form-group col-md-6">
            <label for="celusu">Celular</label>
            <input type="text" name="celusu" id="celusu" class="form-control"
                value="<?php if($datOne) echo $datOne['celusu']; ?>">
        </div>

        <div class="form-group col-md-6">
            <label for="emausu">Correo electrónico</label>
            <input type="email" name="emausu" id="emausu" class="form-control"
                value="<?php if($datOne) echo $datOne['emausu']; ?>" required>
        </div>

        <div class="form-group col-md-6">
            <label for="pasusu">Contraseña</label>
            <input type="password" name="pasusu" id="pasusu" class="form-control"
                placeholder="********" <?php if(!$datOne) echo "required"; ?>>
        </div>

        <div class="form-group col-md-6">
            <label for="imgusu">Foto / Imagen</label>
            <div class="input-group">
                <input type="file" name="imgusu" id="imgusu" class="form-control" accept="image/*">
                <small class="form-text text-muted d-block mt-1">Formatos: JPG, PNG, GIF (máx. 5MB)</small>
            </div>
            <?php if($datOne && !empty($datOne['imgusu'])): ?>
                <div class="mt-2">
                    <small class="text-muted">Imagen actual:</small><br>
                    <img src="<?= htmlspecialchars($datOne['imgusu']); ?>" alt="Foto usuario" style="max-width: 100px; max-height: 100px;" class="mt-1">
                </div>
            <?php endif; ?>
        </div>

        <div class="form-group col-md-6">
            <label for="idper">Perfil</label>
            <select name="idper" id="idper" class="form-control">
                <option value="">Seleccione...</option>
                <?php 
                $perfiles = $musu->getPerfiles();
                if($perfiles){
                    foreach($perfiles as $p){
                        $selected = ($datOne && $datOne['idper'] == $p['idper']) ? "selected" : "";
                        echo "<option value='{$p['idper']}' $selected>{$p['nomper']}</option>";
                    }
                }
                ?>
            </select>
        </div>

        <?php if (isset($_SESSION['idper']) && $_SESSION['idper'] == 1): ?>
            <div class="form-group col-md-6">
                <label for="idemp">Empresa (opcional)</label>
                <select name="idemp" id="idemp" class="form-control">
                    <option value="">Sin empresa</option>
                    <?php 
                    // Asumiendo que $empresas está disponible desde cusu.php
                    if (isset($empresas) && !empty($empresas)) {
                        foreach ($empresas as $e) {
                            $selected = ($datOne && isset($datOne['idemp']) && $datOne['idemp'] == $e['idemp']) ? "selected" : "";
                            echo "<option value='{$e['idemp']}' $selected>{$e['nomemp']}</option>";
                        }
                    }
                    ?>
                </select>
            </div>
        <?php endif; ?>

        <div class="form-group col-md-6">
            <label for="act">Estado</label>
            <select name="act" id="act" class="form-control">
                <option value="1" <?php if($datOne && $datOne['act'] == 1) echo "selected"; ?>>Activo</option>
                <option value="0" <?php if($datOne && $datOne['act'] == 0) echo "selected"; ?>>Inactivo</option>
            </select>
        </div>

        <div class="form-group col-md-12 mt-3">
            <input type="submit" class="btn btn-dark form-control" value="Guardar Usuario">
        </div>

    </div>
</form>

<hr>

<table id="table" class="table table-striped table-bordered">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Nombre completo</th>
            <th>Documento</th>
            <th>Correo</th>
            <th>Celular</th>
            <th>Perfil</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if($datAll){ foreach ($datAll as $dt){ ?>
            <tr>
                <td><?= $dt['idusu']; ?></td>
                <td><?= $dt['nomusu'] . ' ' . $dt['apeusu']; ?></td>
                <td><?= $dt['tdousu'] . ' ' . $dt['ndousu']; ?></td>
                <td><?= $dt['emausu']; ?></td>
                <td><?= $dt['celusu']; ?></td>
                <td><?= $dt['nomper']; ?></td>
                <td><?= $dt['act'] ? 'Activo' : 'Inactivo'; ?></td>
                <td>
                    <?php 
                        // Lógica para el botón de Activar/Desactivar
                        $current_status = $dt['act'];
                        $new_status = $current_status == 1 ? 0 : 1; // Cambia el estado opuesto
                        $btn_class = $current_status == 1 ? 'btn-outline-danger' : 'btn-outline-success';
                        $btn_icon = $current_status == 1 ? 'fa-user-lock' : 'fa-user-check';
                        $btn_title = $current_status == 1 ? 'Desactivar Usuario' : 'Activar Usuario';
                    ?>
                    <a href="controllers/cstatus.php?action=user&id=<?= $dt['idusu']; ?>&estado=<?= $new_status; ?>" 
                        class="btn btn-sm <?= $btn_class; ?> me-1" title="<?= $btn_title; ?>">
                        <i class="fa-solid <?= $btn_icon; ?>"></i>
                    </a>
                    
                    <a href="home.php?pg=<?= $pg; ?>&idusu=<?= $dt['idusu']; ?>&ope=edi" title="Editar">
                        <i class="fa-solid fa-pen-to-square fa-2x text-primary"></i>
                    </a>
                    <a href="javascript:void(0);" onclick="confirmarEliminacion(
                        'controllers/cdelete.php?action=user&id=<?= $dt['idusu']; ?>'
                    )" title="Eliminar">
                        <i class="fa-solid fa-trash-can fa-2x text-danger"></i>
                    </a>
                </td>
            </tr>
        <?php }} else { ?>
            <tr><td colspan="8" class="text-center">No hay usuarios registrados</td></tr>
        <?php } ?>
    </tbody>
</table>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmarEliminacion(url) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Se eliminará el usuario y sus responsabilidades (empresa, ubicaciones, etc.) serán reasignadas al Superadmin (ID 1). Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}

document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    const message = urlParams.get('message'); 
    const error = urlParams.get('error');

    // 1. Manejo de mensajes de controladores (Prioritario)
    if (message) {
        Swal.fire({
            icon: 'success',
            title: '¡Proceso completado!',
            text: decodeURIComponent(message),
            confirmButtonColor: '#198754',
            confirmButtonText: 'Aceptar'
        });
    } else if (error) {
        Swal.fire({
            icon: 'error',
            title: '¡Error!',
            text: decodeURIComponent(error),
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Aceptar'
        });
    }
    // 2. Lógica de mensajes CUD original (PRESERVADA)
    else {
        // Mensaje original (Guardar)
        if (msg === 'saved') {
            Swal.fire({
                icon: 'success',
                title: '¡Guardado exitoso!',
                text: 'El usuario se ha registrado correctamente.',
                confirmButtonColor: '#198754',
                confirmButtonText: 'Aceptar'
            });
        }
        // Mensaje original (Actualizar)
        if (msg === 'updated') {
            Swal.fire({
                icon: 'info',
                title: '¡Actualización exitosa!',
                text: 'Los datos se han actualizado correctamente.',
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'Aceptar'
            });
        }
        
        // Mensaje original (Eliminar)
        if (msg === 'deleted') {
            Swal.fire({
                icon: 'warning',
                title: '¡Eliminación exitosa!',
                text: 'El usuario ha sido eliminado correctamente.',
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Aceptar'
            });
        }
    }
});
</script>
