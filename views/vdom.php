<?php
require_once('controllers/cdom.php');

// ✅ Obtener perfil para controlar botones
$idper = isset($_SESSION['idper']) ? $_SESSION['idper'] : 0;
$puedeCrear = ($idper == 1 || $idper == 2); // SuperAdmin o Admin
$puedeEditar = ($idper == 1 || $idper == 2); // SuperAdmin o Admin
$puedeEliminar = ($idper == 1 || $idper == 2); // SuperAdmin o Admin
?>
<div class="">

    <h2 class="mb-3 text-primary">
        <i class="fa-solid fa-globe"></i> Dominios
    </h2>

    <!-- Formulario de Dominio -->
    <?php if($puedeCrear || isset($datOne)){ ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white">
            <?= isset($datOne) ? "Editar Dominio" : "Nuevo Dominio"; ?>
        </div>
        <div class="card-body">
            <form action="home.php?pg=<?= $pg ?>" method="POST" class="row g-3">

                <div class="col-md-6">
                    <label for="nomdom" class="form-label">Nombre del Dominio</label>
                    <input type="text" name="nomdom" id="nomdom" class="form-control" 
                        value="<?= $datOne[0]['nomdom'] ?? '' ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="desdom" class="form-label">Descripción del Dominio</label>
                    <input type="text" name="desdom" id="desdom" class="form-control" 
                        value="<?= $datOne[0]['desdom'] ?? '' ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="fec_crea" class="form-label">Fecha de creación</label>
                    <input type="date" name="fec_crea" id="fec_crea" class="form-control" 
                        value="<?= $datOne[0]['fec_crea'] ?? date('Y-m-d') ?>" required>
                </div>

                <div class="col-md-6 d-flex align-items-end justify-content-end">
                    <input type="hidden" name="iddom" value="<?= $datOne[0]['iddom'] ?? '' ?>">
                    <input type="hidden" name="ope" value="save">
                    <button type="submit" class="form-control btn btn-dark">
                        Guardar
                    </button>
                </div>

            </form>
        </div>
    </div>
    <?php } ?>

    <!-- Tabla de Dominios -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            Listado de Dominios
        </div>
        <div class="card-body">
            <div class="table-responsive">
            <table id="table" class="table table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Fecha de creación</th>
                        <?php if($idper == 1){ ?>
                            <th>Empresa</th> <!-- ✅ Columna solo para SuperAdmin -->
                        <?php } ?>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($datAll){ foreach ($datAll AS $dt){ ?>
                        <tr>
                            <td><?= $dt['iddom']; ?></td>
                            <td><?= $dt['nomdom']; ?></td>
                            <td><?= $dt['desdom']; ?></td>
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
                                <a href="home.php?pg=<?= $pg; ?>&iddom=<?= $dt['iddom']; ?>&ope=edi" 
                                   class="btn btn-sm btn-outline-warning me-2" title="Editar">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <?php } ?>
                                
                                <!-- ✅ Botón Eliminar (Solo SuperAdmin) -->
                                <?php if($puedeEliminar){ ?>
                                <a href="javascript:void(0);"
                                   onclick="confirmarEliminacion('home.php?pg=<?= $pg; ?>&iddom=<?= $dt['iddom']; ?>&ope=eli')"
                                  class="btn btn-sm btn-outline-danger" title="Eliminar">
                                    <i class="fa-solid fa-trash-can"></i>
                                </a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php }} else { ?>
                        <tr>
                            <td colspan="<?= $idper == 1 ? 6 : 5 ?>" class="text-center text-muted">No hay dominios registrados</td>
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
