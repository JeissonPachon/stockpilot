<?php require_once __DIR__ . '/../controllers/csoent.php'; ?>

<?php
$estadoEntrada = $cabEntrada['estsol'] ?? 'Pendiente';
$entradaAprobada = ($estadoEntrada === 'Aprobada');
?>

<div class="container-fluid px-4 mt-4 module-panel module-soent">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fa-solid fa-dolly text-primary me-2"></i>Detalle de Entrada
            </h1>
            <div class="mt-2">
                <?php if ($entradaAprobada): ?>
                    <span class="badge bg-success-subtle text-success border border-success">Estado: Aprobada</span>
                <?php else: ?>
                    <span class="badge bg-light text-dark border border-secondary-subtle">Estado: Pendiente</span>
                <?php endif; ?>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 bg-transparent ps-0">
                    <li class="breadcrumb-item"><a href="home.php" class="text-decoration-none text-muted">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="home.php?pg=1015" class="text-decoration-none text-muted">Entradas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detalle</li>
                </ol>
            </nav>
        </div>
        <?php if (isset($detalles) && count($detalles) > 0): ?>
            <?php if ($entradaAprobada): ?>
                <button type="button" class="btn btn-primary btn-lg shadow-sm" disabled>
                    <i class="fa-solid fa-check-circle me-2"></i>Aprobar Entrada
                </button>
            <?php else: ?>
                <a href="home.php?pg=1015&idsol=<?= $idsol ?>&aprobar=1" 
                   class="btn btn-primary btn-lg shadow-sm"
                   onclick="return confirm('¿Aprobar esta solicitud y crear movimientos en el Kardex?\n\nEsto agregará automáticamente los productos al inventario.')">
                    <i class="fa-solid fa-check-circle me-2"></i>Aprobar Entrada
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Mensajes de Alerta -->
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-<?= $_SESSION['tipo_mensaje'] ?> alert-dismissible fade show shadow-sm border-0 border-start border-5 border-<?= $_SESSION['tipo_mensaje'] ?>" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-<?= $_SESSION['tipo_mensaje'] == 'success' ? 'check-circle' : 'exclamation-circle' ?> fa-lg me-3"></i>
                <div>
                    <strong><?= $_SESSION['tipo_mensaje'] == 'success' ? '¡Éxito!' : 'Atención' ?></strong>
                    <div class="small"><?= $_SESSION['mensaje'] ?></div>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php 
        unset($_SESSION['mensaje']);
        unset($_SESSION['tipo_mensaje']);
        ?>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Columna Izquierda: Formulario de Registro -->
        <div class="col-lg-4">
            <div class="card shadow border-0 rounded-3 h-100">
                <div class="card-header bg-dark text-white py-3 rounded-top-3">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fa-solid fa-plus-circle me-2"></i>Agregar Producto
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="home.php?pg=1015&idsol=<?= htmlspecialchars($idsol) ?>" method="POST" id="formDetalle">
                        <input type="hidden" name="ope" value="save">
                        
                        <div class="mb-4">
                            <label for="nomprod_display" class="form-label fw-bold text-secondary small text-uppercase">Producto</label>
                            <div class="input-group">
                                <input type="hidden" name="idprod" id="idprod">
                                <input type="text" class="form-control form-control-lg bg-white" id="nomprod_display" placeholder="Click en la lupa para buscar ->" readonly required style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#modalProductos" <?php echo $entradaAprobada ? 'disabled' : ''; ?>>
                                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#modalProductos" title="Buscar en catálogo" <?php echo $entradaAprobada ? 'disabled' : ''; ?>>
                                    <i class="fa-solid fa-search"></i>
                                </button>
                            </div>
                            <div class="mt-2">
                                <select name="idprod_select" id="idprod_select" class="form-select" <?php echo $entradaAprobada ? 'disabled' : ''; ?>>
                                    <option value="">Seleccione producto (lista)</option>
                                    <?php foreach (($productos ?? []) as $p): ?>
                                        <option value="<?= $p['idprod'] ?>"><?= htmlspecialchars($p['nomprod']) ?> (<?= htmlspecialchars($p['codprod'] ?? '') ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Si no abre el modal, selecciona aqui.</small>
                            </div>
                        </div>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <label for="cantdet" class="form-label fw-bold text-secondary small text-uppercase">Cantidad</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-secondary-subtle"><i class="fa-solid fa-hashtag text-muted"></i></span>
                                    <input type="number" name="cantdet" id="cantdet" class="form-control border-secondary-subtle" min="1" step="1" placeholder="0" required <?php echo $entradaAprobada ? 'disabled' : ''; ?>>
                                </div>
                            </div>
                            <div class="col-6">
                                <label for="vundet" class="form-label fw-bold text-secondary small text-uppercase">Costo Unit.</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-secondary-subtle"><i class="fa-solid fa-dollar-sign text-muted"></i></span>
                                    <input type="number" name="vundet" id="vundet" class="form-control border-secondary-subtle" min="0" step="0.01" placeholder="0.00" required <?php echo $entradaAprobada ? 'disabled' : ''; ?>>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4 p-3 bg-light rounded-3 border border-dashed">
                            <label class="form-label fw-bold text-secondary small text-uppercase mb-1">Total Estimado</label>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-muted small">Cantidad x Costo</span>
                                <h3 class="mb-0 text-primary fw-bold" id="total_display">$0.00</h3>
                            </div>
                            <input type="hidden" id="total_preview">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg shadow-sm" <?php echo $entradaAprobada ? 'disabled' : ''; ?>>
                                <i class="fa-solid fa-save me-2"></i>Guardar Registro
                            </button>
                            <button type="reset" class="btn btn-light text-muted border" <?php echo $entradaAprobada ? 'disabled' : ''; ?>>
                                <i class="fa-solid fa-eraser me-2"></i>Limpiar
                            </button>
                        </div>
                        <?php if ($entradaAprobada): ?>
                            <div class="mt-3 small text-muted">
                                Esta solicitud ya fue aprobada. Para cambios, cree una nueva solicitud de entrada.
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Columna Derecha: Tabla de Detalles -->
        <div class="col-lg-8">
            <div class="card shadow border-0 rounded-3 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="card-title mb-0 fw-bold text-secondary">
                        <i class="fa-solid fa-list-check me-2"></i>Items Registrados
                    </h5>
                    <?php if (isset($detalles) && count($detalles) > 0): ?>
                        <span class="badge bg-light text-dark border border-secondary-subtle px-3 py-2 rounded-pill">
                            <?= count($detalles) ?> productos
                        </span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if (isset($detalles) && is_array($detalles) && count($detalles) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tableDetalles">
                                <thead class="bg-light text-secondary text-uppercase small fw-bold">
                                    <tr>
                                        <th class="ps-4 py-3">Producto</th>
                                        <th class="text-center py-3">Cantidad</th>
                                        <th class="text-end py-3">Costo Unit.</th>
                                        <th class="text-end py-3">Subtotal</th>
                                        <th class="text-center py-3 pe-4">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $gran_total = 0;
                                    foreach ($detalles as $d): 
                                        $gran_total += $d['totdet'];
                                    ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light text-secondary rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                    <i class="fa-solid fa-box"></i>
                                                </div>
                                                <?= htmlspecialchars($d['nomprod']) ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                                                <?= htmlspecialchars($d['cantdet']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end text-muted">$<?= number_format($d['vundet'], 2, ',', '.') ?></td>
                                        <td class="text-end fw-bold text-primary">$<?= number_format($d['totdet'], 2, ',', '.') ?></td>
                                        <td class="text-center pe-4">
                                            <?php if ($entradaAprobada): ?>
                                                <button type="button" class="btn btn-outline-danger btn-sm rounded-circle shadow-sm" disabled title="Solicitud aprobada: no editable">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            <?php else: ?>
                                                <a href="home.php?pg=1015&idsol=<?= $idsol ?>&delete=<?= $d['iddet'] ?>" 
                                                   class="btn btn-outline-danger btn-sm rounded-circle shadow-sm" 
                                                   onclick="return confirm('¿Está seguro de eliminar este registro?')"
                                                   data-bs-toggle="tooltip" title="Eliminar">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="bg-light border-top">
                                    <tr>
                                        <td colspan="3" class="text-end py-3 pe-4 fw-bold text-secondary text-uppercase">Total General:</td>
                                        <td class="text-end py-3 fw-bold text-primary fs-5">$<?= number_format($gran_total, 2, ',', '.') ?></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fa-solid fa-clipboard-check fa-4x text-gray-300"></i>
                            </div>
                            <h5 class="text-muted fw-bold">Sin registros aún</h5>
                            <p class="text-muted small mb-0">Utilice el formulario de la izquierda para<br>agregar productos a esta entrada.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Productos -->
<div class="modal fade" id="modalProductos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white border-0 rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-boxes-stacked me-2"></i>Catálogo de Productos</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tableProductosModal">
                        <thead class="bg-light sticky-top">
                            <tr>
                                <th class="ps-4 py-3">Código</th>
                                <th class="py-3">Nombre</th>
                                <th class="py-3">Categoría</th>
                                <th class="text-end pe-4 py-3">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($productos) && is_array($productos)): ?>
                                <?php foreach ($productos as $p): ?>
                                    <tr>
                                        <td class="ps-4 font-monospace text-muted"><?= htmlspecialchars($p['codprod'] ?? 'N/A') ?></td>
                                        <td class="fw-bold text-dark"><?= htmlspecialchars($p['nomprod']) ?></td>
                                        <td><span class="badge bg-secondary-subtle text-secondary rounded-pill"><?= htmlspecialchars($p['nomcat'] ?? 'General') ?></span></td>
                                        <td class="text-end pe-4">
                                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 selecting-prod" 
                                                    data-id="<?= $p['idprod'] ?>" 
                                                    data-nombre="<?= htmlspecialchars($p['nomprod']) ?>"
                                                    data-bs-dismiss="modal"
                                                    <?php echo $entradaAprobada ? 'disabled' : ''; ?>>
                                                Seleccionar <i class="fa-solid fa-arrow-right ms-1"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 rounded-bottom-4 py-2">
                <small class="text-muted me-auto"><i class="fa-solid fa-info-circle me-1"></i>Haga clic en "Seleccionar" para cargar el producto.</small>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- DataTables & Scripts -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Cálculo de totales
    const cantidadInput = document.getElementById('cantdet');
    const valorInput = document.getElementById('vundet');
    const totalDisplay = document.getElementById('total_display');

    const dtLang = {
        decimal: "",
        emptyTable: "No hay datos disponibles",
        info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
        infoEmpty: "Mostrando 0 a 0 de 0 registros",
        infoFiltered: "(filtrado de _MAX_ registros totales)",
        infoPostFix: "",
        thousands: ",",
        lengthMenu: "Mostrar _MENU_ registros",
        loadingRecords: "Cargando...",
        processing: "Procesando...",
        search: "Buscar:",
        zeroRecords: "No se encontraron registros coincidentes",
        paginate: {
            first: "Primero",
            last: "Ultimo",
            next: "Siguiente",
            previous: "Anterior"
        }
    };
    
    function calcularTotal() {
        const cantidad = parseFloat(cantidadInput.value) || 0;
        const valor = parseFloat(valorInput.value) || 0;
        const total = cantidad * valor;
        
        // Formato moneda
        const formatter = new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 2
        });
        
        totalDisplay.textContent = formatter.format(total);
        
        // Animación simple
        totalDisplay.classList.remove('text-primary');
        void totalDisplay.offsetWidth; // Trigger reflow
        totalDisplay.classList.add('text-primary');
    }
    
    if(cantidadInput && valorInput) {
        cantidadInput.addEventListener('input', calcularTotal);
        valorInput.addEventListener('input', calcularTotal);
    }
    
    const form = document.getElementById('formDetalle');
    if(form) {
        form.addEventListener('reset', function() {
            setTimeout(() => { 
                totalDisplay.textContent = '$0.00'; 
                $('#nomprod_display').val('');
                $('#idprod').val('');
            }, 10);
        });
    }
    
    // Inicializar DataTables
    if($('#tableDetalles').length) {
        $('#tableDetalles').DataTable({
            language: dtLang,
            pageLength: 10,
            responsive: true,
            dom: '<"p-2 pb-3"f>rtip', // Buscador habilitado
            order: []
        });
    }

    if($('#tableProductosModal').length) {
        $('#tableProductosModal').DataTable({
            language: dtLang,
            pageLength: 5,
            lengthMenu: [5, 10, 25],
            dom: '<"p-3"f>rt<"p-3 d-flex justify-content-between align-items-center"ip>'
        });
    }
    
    // Seleccionar producto del modal
    $(document).on('click', '.selecting-prod', function() {
        const id = $(this).data('id');
        const nombre = $(this).data('nombre');
        
        $('#idprod').val(id);
        $('#nomprod_display').val(nombre);
        $('#idprod_select').val(id);
        
        // Enfocar cantidad
        setTimeout(() => { $('#cantdet').focus(); }, 300);
    });

    // Seleccion desde lista
    $('#idprod_select').on('change', function () {
        const id = $(this).val();
        if (!id) {
            $('#idprod').val('');
            return;
        }
        const nombre = $('#idprod_select option:selected').text();
        $('#idprod').val(id);
        $('#nomprod_display').val(nombre);
        setTimeout(() => { $('#cantdet').focus(); }, 150);
    });
});
</script>

<style>
/* Estilos personalizados adicionales */
.text-gray-800 { color: #2d3748; }
.text-gray-300 { color: #e2e8f0; }
.card { transition: transform 0.2s; }
.card:hover { transform: translateY(-2px); }
.table-hover tbody tr:hover { background-color: rgba(52, 73, 94, 0.06); }

/* Responsive especifico del modulo */
@media (max-width: 992px) {
    .module-soent .d-flex.justify-content-between.align-items-center.mb-4 {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 0.75rem;
    }

    .module-soent .btn-lg {
        width: 100%;
        font-size: 0.95rem;
        padding: 0.65rem 0.9rem;
    }
}

@media (max-width: 768px) {
    .module-soent .card-body {
        padding: 1rem !important;
    }

    .module-soent .table {
        min-width: 760px;
    }

    .module-soent .h3 {
        font-size: 1.2rem;
    }

    .module-soent .input-group .btn {
        padding-left: 0.8rem;
        padding-right: 0.8rem;
    }
}
</style>