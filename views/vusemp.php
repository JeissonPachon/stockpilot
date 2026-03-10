<?php
//echo "<pre>EMPRESA EN SESIÓN: " . ($_SESSION['idemp'] ?? 'no definida') . "</pre>";
?>


<?php require_once("controllers/cusemp.php"); ?>

<!-- FORMULARIO AGREGAR/EDITAR USUARIO -->
<form action="home.php?pg=<?= $pg; ?>" method="POST">
    <div class="row">
        <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0"><i class="fa-solid fa-user-gear"></i>Gestion de Usuarios</h2>
    </div>
        <div class="form-group col-md-6">
            <label for="nomusu">Nombre</label>
            <input type="text" name="nomusu" id="nomusu" class="form-control" 
                value="<?= isset($datOne['nomusu']) ? $datOne['nomusu'] : ''; ?>" required>
        </div>

        <div class="form-group col-md-6">
            <label for="apeusu">Apellidos</label>
            <input type="text" name="apeusu" id="apeusu" class="form-control" 
                value="<?= isset($datOne['apeusu']) ? $datOne['apeusu'] : ''; ?>" required>
        </div>

        <div class="form-group col-md-6">
            <label for="tdousu">Tipo Documento</label>
            <input type="text" name="tdousu" id="tdousu" class="form-control" 
                value="<?= isset($datOne['tdousu']) ? $datOne['tdousu'] : ''; ?>" required>
        </div>

        <div class="form-group col-md-6">
            <label for="ndousu">Número Documento</label>
            <input type="text" name="ndousu" id="ndousu" class="form-control" 
                value="<?= isset($datOne['ndousu']) ? $datOne['ndousu'] : ''; ?>" required>
        </div>

        <div class="form-group col-md-6">
            <label for="celusu">Teléfono</label>
            <input type="text" name="celusu" id="celusu" class="form-control" 
                value="<?= isset($datOne['celusu']) ? $datOne['celusu'] : ''; ?>">
        </div>

        <div class="form-group col-md-6">
            <label for="emausu">Email</label>
            <input type="email" name="emausu" id="emausu" class="form-control" 
                value="<?= isset($datOne['emausu']) ? $datOne['emausu'] : ''; ?>" required>
        </div>

        <div class="form-group col-md-6">
            <label for="pasusu">Password</label>
            <input type="password" name="pasusu" id="pasusu" class="form-control"
                <?= isset($datOne['idusu']) ? '' : 'required'; ?>>
            <?php if (isset($datOne['idusu'])) { ?>
                <small>Deja el campo vacío si no deseas cambiar la contraseña.</small>
            <?php } ?>
        </div>

        <div class="form-group col-md-6">
            <!-- Campos ocultos -->
            <input type="hidden" name="idemp" value="<?= $_SESSION['idemp']; ?>">
            <input type="hidden" name="idusu" value="<?= isset($datOne['idusu']) ? $datOne['idusu'] : ''; ?>">
            <input type="hidden" name="ope" value="save">
            <br>
            <input type="submit" class="btn btn-primary" 
                value="<?= isset($datOne['idusu']) ? 'Actualizar' : 'Guardar'; ?>">
            <?php if (isset($datOne['idusu'])) { ?>
                <a href="home.php?pg=<?= $pg; ?>" class="btn btn-secondary">Cancelar</a>
            <?php } ?>
        </div>
    </div>
</form>

<hr><br>

<!-- TABLA DE USUARIOS DE LA EMPRESA -->
<div class="table-responsive">
<table id="example" class="table table-striped">
    <thead>
        <tr>
            <th>No.</th>
            <th>Nombre</th>
            <th>Tipo</th>
            <th>Documento</th>
            <th>Teléfono</th>
            <th>Email</th>
            <th>Empresa</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <?php 
        if (!empty($datAll)) { 
            $i = 1;
            foreach ($datAll as $dt) { 
        ?>
        <tr>
            <td><?= $i++; ?></td>
            <td><?= $dt['nomusu'] . ' ' . $dt['apeusu']; ?></td>
            <td><?= $dt['tdousu']; ?></td>
            <td><?= $dt['ndousu']; ?></td>
            <td><?= $dt['celusu']; ?></td>
            <td><?= $dt['emausu']; ?></td>
            <td><?= $dt['nomemp']; ?></td>
            <td style="text-align: right;">
                <a href="home.php?pg=<?= $pg; ?>&idemp=<?= $dt['idemp']; ?>&idusu=<?= $dt['idusu']; ?>&ope=edi" 
                    class="btn btn-sm btn-outline-warning me-2" title="Editar">
                    <i class="fa-solid fa-pen-to-square"></i>
                </a>
                <a href="javascript:void(0);"
                    onclick="confirmarEliminacion('home.php?pg=<?= $pg; ?>&idemp=<?= $dt['idemp']; ?>&idusu=<?= $dt['idusu']; ?>&ope=eli')"
                    class="btn btn-sm btn-outline-danger" title="Eliminar">
                    <i class="fa-solid fa-trash-can"></i>
                </a>

            </td>
        </tr>
        <?php 
            } 
        } 
        ?>
    </tbody>

    <tfoot>
        <tr>
            <th>No.</th>
            <th>Nombre</th>
            <th>Tipo</th>
            <th>Documento</th>
            <th>Teléfono</th>
            <th>Email</th>
            <th>Empresa</th>
            <th></th>
        </tr>
    </tfoot>
</table>
</div>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');

    if (msg === 'saved') {
        Swal.fire({
            icon: 'success',
            title: '¡Guardado exitosamente!',
            text: 'El nuevo Dominio se ha registrado correctamente.',
            confirmButtonColor: '#198754',
            confirmButtonText: 'Aceptar'
        });
    }

    if (msg === 'updated') {
        Swal.fire({
            icon: 'info',
            title: '¡Actualización exitosa!',
            text: 'Los datos se han actualizado correctamente.',
            confirmButtonColor: '#0d6efd',
            confirmButtonText: 'Aceptar'
        });
    }

    if (msg === 'deleted') {
        Swal.fire({
            icon: 'warning',
            title: '¡Eliminación exitosa!',
            text: 'El Dominio ha sido eliminado correctamente.',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Aceptar'
        });
    }
});

// Confirmación antes de eliminar
function confirmarEliminacion(url) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción no se puede deshacer.',
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
</script>
