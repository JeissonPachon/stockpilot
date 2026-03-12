<?php require_once __DIR__ . '/../controllers/ckard.php'; ?>
<div class="conte module-panel module-kardex">
    <div class="d-flex justify-content-between align-items-center mb-3 kardex-toolbar">
        <h2><i class="fa-solid fa-boxes-stacked"></i> Kardex</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="home.php">Inicio</a></li>
                <li class="breadcrumb-item active" aria-current="page">Kardex</li>
            </ol>
        </nav>
    </div>

    <?php if(isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-<?= $_SESSION['tipo_mensaje'] ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['mensaje'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
    <?php endif; ?>

    <!-- Formulario -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fa-solid fa-calendar-plus"></i> <?= isset($datOne[0]) ? 'Editar Periodo' : 'Nuevo Periodo' ?></h5>
        </div>
        <div class="card-body">
            <form id="frmins" action="home.php?pg=1007" method="POST" class="row g-3">
                <input type="hidden" name="ope" value="save">
                <input type="hidden" name="idkar" value="<?php if (!empty($datOne)) echo $datOne[0]['idkar']; ?>">
                
                <div class="col-md-4">
                    <label for="anio" class="form-label"><i class="fa-solid fa-calendar"></i> Año <span class="text-danger">*</span></label>
                    <input type="number" name="anio" id="anio" class="form-control" required 
                        value="<?php if(!empty($datOne)) echo $datOne[0]['anio']; else echo date('Y'); ?>">
                </div>
                <div class="col-md-4">
                    <label for="mes" class="form-label"><i class="fa-solid fa-calendar-days"></i> Mes <span class="text-danger">*</span></label>
                    <select name="mes" id="mes" class="form-select" required>
                        <?php 
                        $meses = [1=>'Enero', 2=>'Febrero', 3=>'Marzo', 4=>'Abril', 5=>'Mayo', 6=>'Junio', 
                                  7=>'Julio', 8=>'Agosto', 9=>'Septiembre', 10=>'Octubre', 11=>'Noviembre', 12=>'Diciembre'];
                        $mesActual = !empty($datOne) ? $datOne[0]['mes'] : date('n');
                        foreach($meses as $num => $nom): ?>
                            <option value="<?= $num ?>" <?= ($mesActual == $num) ? 'selected' : '' ?>><?= $nom ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="cerrado" class="form-label"><i class="fa-solid fa-lock"></i> Estado</label>
                    <select name="cerrado" id="cerrado" class="form-select">
                        <option value="0" <?php if ($datOne && $datOne[0]['cerrado'] == 0) echo 'selected'; ?>>Abierto</option>
                        <option value="1" <?php if ($datOne && $datOne[0]['cerrado'] == 1) echo 'selected'; ?>>Cerrado</option>
                    </select>
                </div>
                
                <div class="col-12 d-flex justify-content-end gap-2">
                    <button class="btn btn-success" type="submit">
                        <i class="fa-solid fa-save"></i> <?= isset($datOne[0]) ? 'Actualizar' : 'Guardar' ?>
                    </button>
                    <?php if(isset($datOne[0])): ?>
                        <a href="home.php?pg=1007" class="btn btn-secondary">
                            <i class="fa-solid fa-times"></i> Cancelar
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-secondary"><i class="fa-solid fa-list"></i> Listado de Periodos</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tableKardex">
                    <thead class="table-light">
                        <tr>
                            <th>Periodo</th>
                            <th>Estado</th>
                            <th>Movimientos</th>
                            <th>Balance</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($dtKardex) {
                            foreach ($dtKardex as $row) { ?>
                                <tr>
                                    <td class="fw-bold">
                                        <?= $meses[$row['mes']] ?> <?= $row['anio'] ?>
                                    </td>
                                    <td>
<?php if($row['cerrado']): ?>
    <span class="badge bg-danger-subtle text-danger border border-danger"><i class="fa-solid fa-lock"></i> Cerrado</span>
<?php else: ?>
    <span class="badge bg-success-subtle text-success border border-success"><i class="fa-solid fa-lock-open"></i> Abierto</span>
<?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-secondary"><?= $row['total_movs']; ?></span></td>
                                    <td>
                                        <span class="<?= $row['balance'] < 0 ? 'text-danger fw-bold' : 'text-success fw-bold' ?>">
                                            <?= number_format($row['balance'], 2) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons" role="group">
                                            <button onclick="verMovimientos(<?= $row['idkar']; ?>)" class="btn btn-outline-info btn-sm" title="Ver Movimientos">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <a href="home.php?pg=1007&ope=edi&idkar=<?= $row['idkar']; ?>" class="btn btn-outline-warning btn-sm" title="Editar">
                                                <i class="fa-solid fa-edit"></i>
                                            </a>
                                            <?php if ($row['total_movs'] == 0) { ?>
                                                <a href="home.php?pg=1007&idkar=<?= $row['idkar']; ?>&ope=eli" 
                                                   class="btn btn-outline-danger btn-sm" 
                                                   onclick="return confirm('¿Seguro que desea eliminar este Kardex?')" title="Eliminar">
                                                   <i class="fa-solid fa-trash"></i>
                                                </a>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                        <?php } } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver movimientos -->
<div class="modal fade" id="modalMovimientos" tabindex="-1" aria-labelledby="modalMovimientosLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalMovimientosLabel"><i class="fa-solid fa-right-left"></i> Movimientos del Kardex</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body bg-light" id="modalMovimientosBody">
        <!-- Aquí se cargará la info por AJAX -->
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    if($('#tableKardex').length) {
        $('#tableKardex').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            order: [[0, 'desc']],
            pageLength: 10,
            responsive: true
        });
    }
});

function verMovimientos(idkar) {
    var modal = new bootstrap.Modal(document.getElementById('modalMovimientos'));
    modal.show();
    
    $.get('views/vkard_mov_modal.php', {idkar: idkar}, function(data) {
        $('#modalMovimientosBody').html(data);
    }).fail(function() {
        $('#modalMovimientosBody').html('<div class="alert alert-danger">Error al cargar los movimientos</div>');
    });
}

$(document).on('submit', '#formAddMov', function(e){
    e.preventDefault();
    var formData = $(this).serialize();
    
    $.post('views/vkard_mov_modal.php', formData, function(data){
        $('#modalMovimientosBody').html(data);
    }).fail(function() {
        alert('Error al guardar el movimiento');
    });
});
</script>
