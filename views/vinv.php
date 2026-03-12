<?php require_once('controllers/cinv.php'); 

// Perfil para controlar acciones
$idper       = isset($_SESSION['idper']) ? $_SESSION['idper'] : 0;
$puedeEditar = ($idper == 1 || $idper == 2);
$puedeEliminar = ($idper == 1); // Solo superadmin elimina filas de inventario

// Estadísticas para tarjetas de resumen
$totalProductos = $datAll ? count($datAll) : 0;
$totalAgotados  = 0;
$totalSinLotes  = 0;
foreach (($datAll ?: []) as $r) {
    if ((float)$r['cant'] <= 0) $totalAgotados++;
    $k = $r['idprod'] . '_' . (int)($r['idubi'] ?? 0);
    if (empty($lotesIndexados[$k])) $totalSinLotes++;
}
?>

<div class="module-panel module-inv">

<h2 class="mb-3 text-primary">
    <i class="fa-solid fa-boxes-stacked"></i> Inventario
    <small class="fs-6 text-muted ms-2">Stock actual por producto y ubicación</small>
</h2>

<div class="alert alert-info py-2">
    <i class="fa-solid fa-circle-info me-1"></i>
    El inventario se actualiza automaticamente con entradas y salidas. Use el chevron para ver los lotes.
</div>

<?php if (isset($_SESSION['mensaje'])): ?>
    <div class="alert alert-<?= htmlspecialchars($_SESSION['tipo_mensaje'] ?? 'info') ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['mensaje']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
<?php endif; ?>

<!-- Tarjetas de resumen -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="card text-center border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-primary"><?= $totalProductos ?></div>
                <div class="text-muted small">Registros de stock</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card text-center border-0 shadow-sm h-100 <?= $totalAgotados > 0 ? 'border-danger border' : '' ?>">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold <?= $totalAgotados > 0 ? 'text-danger' : 'text-success' ?>"><?= $totalAgotados ?></div>
                <div class="text-muted small">Agotados (cant = 0)</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card text-center border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-warning"><?= $totalSinLotes ?></div>
                <div class="text-muted small">Sin lotes asociados</div>
            </div>
        </div>
    </div>
</div>

<!-- Formulario ajuste manual (solo superadmin en modo edición) -->
<?php if ($puedeEditar && isset($datOne)): ?>
<div class="card shadow-sm mb-4 border-warning">
    <div class="card-header bg-warning text-dark d-flex align-items-center gap-2">
        <i class="fa-solid fa-triangle-exclamation"></i>
        Ajuste Manual de Inventario
        <small class="ms-auto">El inventario normalmente se actualiza automáticamente desde las entradas y salidas.</small>
    </div>
    <div class="card-body">
        <form action="home.php?pg=<?= $pg; ?>" method="POST" class="row g-3">

            <div class="col-md-4">
                <label class="form-label">Producto</label>
                <select name="idprod" class="form-select" required>
                    <option value="">Seleccione un producto</option>
                    <?php foreach (($datProd ?: []) as $row): ?>
                        <option value="<?= $row['idprod'] ?>"
                            <?= ($datOne[0]['idprod'] == $row['idprod']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['nomprod']) ?> (<?= htmlspecialchars($row['nomcat']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Ubicación</label>
                <select name="idubi" class="form-select" required>
                    <option value="">Seleccione una ubicación</option>
                    <?php foreach (($datUbi ?: []) as $row): ?>
                        <option value="<?= $row['idubi'] ?>"
                            <?= ($datOne[0]['idubi'] == $row['idubi']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['nomubi']) ?> (<?= htmlspecialchars($row['codubi']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Cantidad</label>
                <input type="number" name="cant" class="form-control"
                       value="<?= htmlspecialchars($datOne[0]['cant'] ?? '') ?>" required min="0" step="0.01">
            </div>

            <div class="col-12 d-flex justify-content-end gap-2">
                <a href="home.php?pg=<?= $pg ?>" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-xmark"></i> Cancelar
                </a>
                <input type="hidden" name="idinv" value="<?= $datOne[0]['idinv'] ?? '' ?>">
                <input type="hidden" name="ope" value="save">
                <button type="submit" class="btn btn-warning">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar ajuste
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Tabla principal de inventario -->
<div class="card shadow-sm">
    <div class="card-header bg-primary text-white d-flex align-items-center gap-2">
        <i class="fa-solid fa-table-list"></i> Stock por Producto y Ubicación
    </div>
    <div class="card-body p-0">
        <div class="p-3 border-bottom bg-light d-flex flex-wrap gap-2 align-items-center">
            <div class="input-group" style="max-width: 420px;">
                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" id="inv_search" class="form-control" placeholder="Buscar producto, categoria, ubicacion o empresa...">
            </div>
            <span id="inv_counter" class="text-muted small">Mostrando 0/0</span>
        </div>
        <div class="table-responsive">
        <table id="tableInv" class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th style="width:2.5rem"></th>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Ubicación</th>
                    <th class="text-end">Cant. actual</th>
                    <th class="text-center">Lotes</th>
                    <th class="text-center">Estado</th>
                    <?php if ($idper == 1): ?><th>Empresa</th><?php endif; ?>
                    <?php if ($puedeEditar): ?><th class="text-center">Acciones</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php if ($datAll): foreach ($datAll as $row):
                $idubiRow = isset($row['idubi']) ? (int)$row['idubi'] : 0;
                $k     = $row['idprod'] . '_' . $idubiRow;
                $lotes = $lotesIndexados[$k] ?? [];
                $cant  = (float)$row['cant'];
                $nLotes = count($lotes);
                $lotesVencidos = 0;
                $lotesPorVencer = 0;
                foreach ($lotes as $l) {
                    if ($l['estado_lote'] === 'Vencido')    $lotesVencidos++;
                    if ($l['estado_lote'] === 'Por vencer') $lotesPorVencer++;
                }
                // Estado de stock
                if ($cant <= 0) {
                    $estBadge = '<span class="badge bg-danger">Agotado</span>';
                } else {
                    $estBadge = '<span class="badge bg-success">Disponible</span>';
                }
                $rowId = 'lotes-' . $row['idprod'] . '-' . $idubiRow;
            ?>
                <tr class="<?= $cant <= 0 ? 'table-danger' : '' ?>" style="cursor:default" data-inv-row="1" data-rowid="<?= $rowId ?>"
                    data-search="<?= htmlspecialchars(strtolower(trim(($row['nomprod'] ?? '') . ' ' . ($row['codprod'] ?? '') . ' ' . ($row['nomcat'] ?? '') . ' ' . ($row['nomubi'] ?? 'sin ubicacion') . ' ' . ($row['nomemp'] ?? '')))) ?>">
                    <td class="text-center">
                        <?php if ($nLotes > 0): ?>
                        <button class="btn btn-sm btn-outline-secondary py-0 px-1 toggle-lotes"
                                data-target="#<?= $rowId ?>"
                                title="Ver lotes">
                            <i class="fa-solid fa-chevron-right fa-xs"></i>
                        </button>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($row['nomprod']) ?></strong>
                        <br><small class="text-muted"><?= htmlspecialchars($row['codprod'] ?? '') ?></small>
                    </td>
                    <td><?= htmlspecialchars($row['nomcat']) ?></td>
                    <td><?= htmlspecialchars($row['nomubi'] ?? 'Sin ubicacion') ?></td>
                    <td class="text-end">
                        <span class="fw-bold <?= $cant <= 0 ? 'text-danger' : 'text-dark' ?>">
                            <?= number_format($cant, 2, ',', '.') ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <?php if ($nLotes > 0): ?>
                            <span class="badge bg-primary"><?= $nLotes ?></span>
                            <?php if ($lotesVencidos > 0): ?>
                                <span class="badge bg-danger ms-1" title="Lotes vencidos"><?= $lotesVencidos ?> venc.</span>
                            <?php elseif ($lotesPorVencer > 0): ?>
                                <span class="badge bg-warning text-dark ms-1" title="Próximos a vencer"><?= $lotesPorVencer ?> x/venc.</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?= $estBadge ?></td>
                    <?php if ($idper == 1): ?>
                    <td>
                        <?php if ($row['nomemp']): ?>
                            <span class="badge bg-info text-dark"><?= htmlspecialchars($row['nomemp']) ?></span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Sin empresa</span>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                    <?php if ($puedeEditar): ?>
                    <td class="text-center">
                        <?php if (!empty($row['idinv'])): ?>
                            <a href="home.php?pg=<?= $pg ?>&idinv=<?= $row['idinv'] ?>&ope=edi"
                               class="btn btn-sm btn-outline-warning me-1" title="Ajuste manual">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                        <?php else: ?>
                            <button class="btn btn-sm btn-outline-warning me-1" disabled title="Sin registro en inventario">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                        <?php endif; ?>
                        <?php if ($puedeEliminar): ?>
                        <?php if (empty($row['idinv']) || $nLotes > 0): ?>
                            <button class="btn btn-sm btn-outline-danger" disabled title="No se puede eliminar: hay lotes asociados">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        <?php else: ?>
                            <a href="javascript:void(0);"
                               onclick="confirmarEliminacion('home.php?pg=<?= $pg ?>&idinv=<?= $row['idinv'] ?>&ope=eli')"
                               class="btn btn-sm btn-outline-danger" title="Eliminar">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>

                <?php if ($nLotes > 0): ?>
                <!-- Fila expandible con detalle de lotes -->
                <tr id="<?= $rowId ?>" class="d-none bg-light" data-child-row="1">
                    <td colspan="<?= 7 + ($idper == 1 ? 1 : 0) + ($puedeEditar ? 1 : 0) ?>" class="ps-4 py-0">
                        <div class="py-2">
                            <small class="text-muted fw-semibold mb-1 d-block">
                                <i class="fa-solid fa-tags"></i> Lotes de <?= htmlspecialchars($row['nomprod']) ?> en <?= htmlspecialchars($row['nomubi']) ?>
                            </small>
                            <table class="table table-sm table-bordered mb-0" style="font-size:.85rem">
                                <thead class="table-secondary">
                                    <tr>
                                        <th>Código lote</th>
                                        <th class="text-end">Cant. ini.</th>
                                        <th class="text-end">Cant. act.</th>
                                        <th class="text-end">Costo unit.</th>
                                        <th>F. ingreso</th>
                                        <th>F. vencimiento</th>
                                        <th class="text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($lotes as $l):
                                    $estLoteBadge = match($l['estado_lote']) {
                                        'Vencido'         => '<span class="badge bg-danger">Vencido</span>',
                                        'Por vencer'      => '<span class="badge bg-warning text-dark">Por vencer</span>',
                                        'Sin vencimiento' => '<span class="badge bg-secondary">Sin venc.</span>',
                                        default           => '<span class="badge bg-success">Vigente</span>',
                                    };
                                ?>
                                    <tr class="<?= $l['estado_lote'] === 'Vencido' ? 'table-danger' : ($l['estado_lote'] === 'Por vencer' ? 'table-warning' : '') ?>">
                                        <td><code><?= htmlspecialchars($l['codlot']) ?></code></td>
                                        <td class="text-end"><?= number_format((float)$l['cantini'], 2, ',', '.') ?></td>
                                        <td class="text-end fw-bold"><?= number_format((float)$l['cantact'], 2, ',', '.') ?></td>
                                        <td class="text-end"><?= $l['costuni'] !== null ? '$' . number_format((float)$l['costuni'], 2, ',', '.') : '—' ?></td>
                                        <td><?= $l['fecing'] ?? '—' ?></td>
                                        <td><?= $l['fecven'] ?? '<span class="text-muted">Sin vencimiento</span>' ?></td>
                                        <td class="text-center"><?= $estLoteBadge ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>

            <?php endforeach; else: ?>
                <tr>
                    <td colspan="<?= 7 + ($idper == 1 ? 1 : 0) + ($puedeEditar ? 1 : 0) ?>"
                        class="text-center text-muted py-4">
                        <i class="fa-solid fa-box-open fa-2x mb-2 d-block opacity-25"></i>
                        No hay registros de inventario
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

</div><!-- /.module-panel -->

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Toggle filas de lotes
    document.querySelectorAll('.toggle-lotes').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const target = document.querySelector(btn.dataset.target);
            if (!target) return;
            const open = !target.classList.contains('d-none');
            target.classList.toggle('d-none', open);
            btn.querySelector('i').style.transform = open ? '' : 'rotate(90deg)';
        });
    });

    // Busqueda simple sin DataTables
    const searchInput = document.getElementById('inv_search');
    const counterEl = document.getElementById('inv_counter');
    const rows = Array.from(document.querySelectorAll('tr[data-inv-row="1"]'));

    function updateCounter(visible, total) {
        if (counterEl) {
            counterEl.textContent = `Mostrando ${visible}/${total}`;
        }
    }

    function applyFilter() {
        const q = (searchInput ? searchInput.value : '').toLowerCase().trim();
        let visible = 0;
        rows.forEach(function (row) {
            const text = row.getAttribute('data-search') || '';
            const match = !q || text.indexOf(q) !== -1;
            row.style.display = match ? '' : 'none';
            const childId = row.getAttribute('data-rowid');
            const child = childId ? document.getElementById(childId) : null;
            if (child) {
                child.style.display = match ? '' : 'none';
            }
            if (match) visible += 1;
        });
        updateCounter(visible, rows.length);
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyFilter);
    }
    updateCounter(rows.length, rows.length);

    // Alertas de mensajes de sesión vía URL
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    if (msg === 'saved') {
        Swal.fire({ icon: 'success', title: 'Guardado', text: 'Ajuste de inventario registrado.', confirmButtonColor: '#198754' });
    } else if (msg === 'updated') {
        Swal.fire({ icon: 'info', title: 'Actualizado', confirmButtonColor: '#0d6efd' });
    } else if (msg === 'deleted') {
        Swal.fire({ icon: 'warning', title: 'Eliminado', confirmButtonColor: '#dc3545' });
    }
});

function confirmarEliminacion(url) {
    Swal.fire({
        title: '¿Eliminar registro?',
        text: 'Esta acción solo elimina el registro de inventario, no los movimientos ni lotes.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(r => { if (r.isConfirmed) window.location.href = url; });
}
</script>

