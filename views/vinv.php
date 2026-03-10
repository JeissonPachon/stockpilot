<?php require_once('controllers/cinv.php'); 

// ✅ Obtener perfil para controlar botones
$idper = isset($_SESSION['idper']) ? $_SESSION['idper'] : 0;
$puedeCrear = ($idper == 1 || $idper == 2);
$puedeEditar = ($idper == 1 || $idper == 2);
$puedeEliminar = ($idper == 1 || $idper == 2);
?>

<div class="">

    <h2 class="mb-3 text-primary">
        <i class="fa-solid fa-box"></i> Inventario
    </h2>

    <!-- ✅ BOTÓN GENERAR PDF 
    <div class="mb-3 text-end">
        <a href="controllers/generar_pdf_inventario.php" target="_blank" class="btn btn-danger">
            <i class="fa-solid fa-file-pdf"></i> Generar PDF
        </a>
    </div> -->

    <!-- Formulario de Inventario -->
    <?php if($puedeCrear || isset($datOne)){ ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white">
            <?= isset($datOne) ? "Editar Inventario" : "Nuevo Inventario"; ?>
        </div>
        <div class="card-body">
            <form action="home.php?pg=<?= $pg; ?>" method="POST" class="row g-3">

                <!-- Producto -->
                <div class="col-md-4">
                    <label for="idprod" class="form-label">Producto</label>
                    <select name="idprod" id="idprod" class="form-select" required>
                        <option value="">Seleccione un producto</option>
                        <?php if($datProd){ foreach($datProd as $row){ ?>
                            <option value="<?= $row['idprod']; ?>"
                                <?= ($datOne && $datOne[0]['idprod'] == $row['idprod']) ? 'selected' : ''; ?>>
                                <?= $row['nomprod']; ?> (<?= $row['nomcat']; ?>)
                            </option>
                        <?php }} ?>
                    </select>
                </div>

                <!-- Ubicación -->
                <div class="col-md-4">
                    <label for="idubi" class="form-label">Ubicación</label>
                    <select name="idubi" id="idubi" class="form-select" required>
                        <option value="">Seleccione una ubicación</option>
                        <?php if($datUbi){ foreach($datUbi as $row){ ?>
                            <option value="<?= $row['idubi']; ?>"
                                <?= ($datOne && $datOne[0]['idubi'] == $row['idubi']) ? 'selected' : ''; ?>>
                                <?= $row['nomubi']; ?> (<?= $row['codubi']; ?>)
                            </option>
                        <?php }} ?>
                    </select>
                </div>

                <!-- Cantidad -->
                <div class="col-md-4">
                    <label for="cant" class="form-label">Cantidad</label>
                    <input type="number" name="cant" id="cant" class="form-control" 
                        value="<?= $datOne[0]['cant'] ?? '' ?>" required min="0">
                </div>

                <!-- Botón guardar -->
                <div class="col-md-12 d-flex justify-content-end mt-3">
                    <input type="hidden" name="idinv" value="<?= $datOne[0]['idinv'] ?? '' ?>">
                    <input type="hidden" name="ope" value="save">
                    <button type="submit" class="form-control btn btn-dark">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php } ?>

    <!-- Tabla de Inventario -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            Listado de Inventario
        </div>
        <div class="card-body">
            <table id="table" class="table table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Ubicación</th>
                        <th>Cantidad</th>
                        <?php if($idper == 1){ ?>
                            <th>Empresa</th>
                        <?php } ?>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($datAll){ foreach($datAll as $row){ ?>
                        <tr>
                            <td><?= $row['idinv']; ?></td>
                            <td><?= $row['nomprod']; ?></td>
                            <td><?= $row['nomcat']; ?></td>
                            <td><?= $row['nomubi']; ?></td>
                            <td>
                                <span class="badge bg-primary"><?= $row['cant']; ?></span>
                            </td>
                            
                            <?php if($idper == 1){ ?>
                                <td>
                                    <?php if($row['nomemp']){ ?>
                                        <span class="badge bg-info"><?= $row['nomemp']; ?></span>
                                    <?php } else { ?>
                                        <span class="badge bg-secondary">Sin Empresa</span>
                                    <?php } ?>
                                </td>
                            <?php } ?>
                            
                            <td class="text-center">
                                <!-- Botón Editar -->
                                <?php if($puedeEditar){ ?>
                                <a href="home.php?pg=<?= $pg; ?>&idinv=<?= $row['idinv']; ?>&ope=edi" 
                                   class="btn btn-sm btn-outline-warning me-2" title="Editar">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <?php } ?>
                                
                                <!-- Botón Eliminar -->
                                <?php if($puedeEliminar){ ?>
                                <a href="javascript:void(0);"
                                   onclick="confirmarEliminacion('home.php?pg=<?= $pg; ?>&idinv=<?= $row['idinv']; ?>&ope=eli')"
                                   class="btn btn-sm btn-outline-danger" title="Eliminar">
                                    <i class="fa-solid fa-trash-can"></i>
                                </a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php }} else { ?>
                        <tr>
                            <td colspan="<?= $idper == 1 ? 7 : 6 ?>" class="text-center text-muted">No hay valores registrados</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
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
            text: 'El nuevo Valor se ha registrado correctamente.',
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
            text: 'El Valor ha sido eliminado correctamente.',
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

