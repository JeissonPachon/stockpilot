<?php
require_once('controllers/ccat.php');

// ✅ Obtener perfil para controlar botones
$idper = isset($_SESSION['idper']) ? $_SESSION['idper'] : 0;
$puedeCrear = ($idper == 1 || $idper == 2); // SuperAdmin o Admin
$puedeEditar = ($idper == 1 || $idper == 2); // SuperAdmin o Admin
$puedeEliminar = ($idper == 1 || $idper == 2); // SuperAdmin o Admin
?>
<div class="">

    <h2 class="mb-3 text-primary">
        <i class="fas fa-folder"></i> Categorías
    </h2>

    <!-- Formulario de Categoría -->
    <?php if($puedeCrear || isset($datOne)){ ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white">
            <?= isset($datOne) ? "Editar Categoría" : "Nueva Categoría"; ?>
        </div>
        <div class="card-body">
            <form action="home.php?pg=<?= $pg ?>" method="POST" class="row g-3">

                <div class="col-md-6">
                    <label for="nomcat" class="form-label">Nombre de la Categoría</label>
                    <input type="text" name="nomcat" id="nomcat" class="form-control"
                        value="<?= $datOne[0]['nomcat'] ?? '' ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="descat" class="form-label">Descripción</label>
                    <input type="text" name="descat" id="descat" class="form-control"
                        value="<?= $datOne[0]['descat'] ?? '' ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="fec_crea" class="form-label">Fecha de creación</label>
                    <input type="date" name="fec_crea" id="fec_crea" class="form-control"
                        value="<?= $datOne[0]['fec_crea'] ?? date('Y-m-d') ?>" required>
                </div>

                <div class="col-md-6 d-flex align-items-end">
                    <input type="hidden" name="idcat" value="<?= $datOne[0]['idcat'] ?? '' ?>">
                    <input type="hidden" name="ope" value="save">
                    <button type="submit" class="btn btn-dark w-100">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Guardar
                    </button>
                </div>

            </form>
        </div>
    </div>
    <?php } ?>

    <!-- Tabla de Categorías -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <i class="fa-solid fa-list me-1"></i> Listado de Categorías
        </div>
        <div class="card-body">
            <div class="table-responsive">
            <table id="table" class="table table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Fecha creación</th>
                        <?php if($idper == 1){ ?>
                            <th>Empresa</th> <!-- ✅ Columna solo para SuperAdmin -->
                        <?php } ?>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($dtAll) { foreach ($dtAll as $dt) { ?>
                        <tr>
                            <td><?= $dt['idcat']; ?></td>
                            <td><?= $dt['nomcat']; ?></td>
                            <td><?= $dt['descat']; ?></td>
                            <td><?= $dt['fec_crea']; ?></td>
                            
                            <?php if($idper == 1){ ?>
                                <!-- ✅ MOSTRAR NOMBRE DE EMPRESA -->
                                <td>
                                    <?php if($dt['nomemp']){ ?>
                                        <span class="badge bg-info">
                                            <?= $dt['nomemp']; ?>
                                        </span>
                                    <?php } else { ?>
                                        <span class="badge bg-secondary">Sin Empresa</span>
                                    <?php } ?>
                                </td>
                            <?php } ?>
                            
                            <td class="text-center">
                                <!-- ✅ Botón Editar (Solo Admin y SuperAdmin) -->
                                <?php if($puedeEditar){ ?>
                                <a href="home.php?pg=<?= $pg; ?>&idcat=<?= $dt['idcat']; ?>&ope=eDi"
                                   class="btn btn-sm btn-outline-warning me-2" title="Editar">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <?php } ?>

                                <!-- ✅ Botón Eliminar (SuperAdmin y Admin/Empresa) -->
                                <?php if($puedeEliminar){ ?>
                                <a href="javascript:void(0);"
                                   onclick="confirmarEliminacion('home.php?pg=<?= $pg; ?>&idcat=<?= $dt['idcat']; ?>&ope=eLi')"
                                   class="btn btn-sm btn-outline-danger me-2"
                                   title="Eliminar">
                                    <i class="fa-solid fa-trash-can"></i>
                                </a>
                                <?php } ?>

                                <!-- Botón Inventario (Todos pueden ver) -->
                                <a href="home.php?pg=1009&idcat=<?= $dt['idcat']; ?>"
                                   class="btn btn-sm btn-outline-primary" title="Ver Inventario">
                                    <i class="fa-solid fa-boxes-stacked"></i>
                                </a>
                            </td>
                        </tr>
                    <?php }} else { ?>
                        <tr>
                            <td colspan="<?= $idper == 1 ? 6 : 5 ?>" class="text-center text-muted">No hay categorías registradas</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

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
            text: 'La nueva categoría se ha registrado correctamente.',
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
            text: 'La categoría ha sido eliminada correctamente.',
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
