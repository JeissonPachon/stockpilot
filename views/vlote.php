<?php include_once('controllers/clote.php'); ?>

<div class="container-fluid py-4 px-4">

    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-<?php echo $_SESSION['tipo_mensaje']; ?> alert-dismissible fade show">
            <?php echo $_SESSION['mensaje']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
    <?php endif; ?>

    <!-- CABECERA -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="fas fa-boxes-stacked text-primary"></i> Gestión de Lotes</h2>
            <small class="text-muted">Control de lotes por producto y almacén</small>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalLote">
            <i class="fas fa-plus"></i> Nuevo Lote (Manual)
        </button>
    </div>

    <!-- TARJETAS RESUMEN -->
    <?php
        $total_lotes    = count($dtAll);
        $lotes_activos  = count(array_filter($dtAll, fn($l) => $l['cantact'] > 0));
        $lotes_vencidos = count(array_filter($dtAll, fn($l) => $l['estado'] === 'Vencido'));
        $lotes_alerta   = count(array_filter($dtAll, fn($l) => $l['estado'] === 'Por vencer'));
    ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center h-100" style="border-left:4px solid #0d6efd!important">
                <div class="card-body">
                    <div class="fs-1 fw-bold text-primary"><?php echo $total_lotes; ?></div>
                    <div class="text-muted small">Total de Lotes</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center h-100" style="border-left:4px solid #198754!important">
                <div class="card-body">
                    <div class="fs-1 fw-bold text-success"><?php echo $lotes_activos; ?></div>
                    <div class="text-muted small">Lotes con Stock</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center h-100" style="border-left:4px solid #ffc107!important">
                <div class="card-body">
                    <div class="fs-1 fw-bold text-warning"><?php echo $lotes_alerta; ?></div>
                    <div class="text-muted small">Por Vencer (&lt;30 días)</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center h-100" style="border-left:4px solid #dc3545!important">
                <div class="card-body">
                    <div class="fs-1 fw-bold text-danger"><?php echo $lotes_vencidos; ?></div>
                    <div class="text-muted small">Lotes Vencidos</div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-table text-primary"></i> Listado de Lotes</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaLotes">
                    <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th>Código Lote</th>
                            <th><i class="fas fa-warehouse text-muted"></i> Ubicación</th>
                            <th class="text-center">F. Ingreso</th>
                            <th class="text-center">F. Vencimiento</th>
                            <th class="text-center">Cant. Inicial</th>
                            <th class="text-center">Cant. Actual</th>
                            <th class="text-center">Costo U.</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($dtAll)): ?>
                            <?php foreach ($dtAll as $l):
                                $badgeColor = match($l['estado']) {
                                    'Vencido'        => 'danger',
                                    'Por vencer'     => 'warning',
                                    'Vigente'        => 'success',
                                    default          => 'secondary'
                                };
                                $rowClass = match($l['estado']) {
                                    'Vencido'    => 'table-danger',
                                    'Por vencer' => 'table-warning',
                                    default      => ''
                                };
                            ?>
                            <tr class="<?php echo $rowClass; ?>">
                                <td>
                                    <strong><?php echo htmlspecialchars($l['nomprod']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($l['codprod']); ?></small>
                                </td>
                                <td><code><?php echo htmlspecialchars($l['codlot']); ?></code></td>
                                <td>
                                    <?php if ($l['nomubi']): ?>
                                        <i class="fas fa-warehouse text-primary"></i>
                                        <?php echo htmlspecialchars($l['nomubi']); ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($l['codubi'] ?? ''); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic">Sin asignar</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <small><?php echo $l['fecing'] ? date('d/m/Y', strtotime($l['fecing'])) : '-'; ?></small>
                                </td>
                                <td class="text-center">
                                    <?php if ($l['fecven']): ?>
                                        <small><?php echo date('d/m/Y', strtotime($l['fecven'])); ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">N/A</small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?php echo number_format($l['cantini'], 2); ?></td>
                                <td class="text-center">
                                    <span class="fw-bold <?php echo $l['cantact'] <= 0 ? 'text-danger' : 'text-success'; ?>">
                                        <?php echo number_format($l['cantact'], 2); ?>
                                    </span>
                                </td>
                                <td class="text-center">$<?php echo number_format($l['costuni'], 4); ?></td>
                                <td class="text-center">
                                    <span class="badge bg-<?php echo $badgeColor; ?>">
                                        <?php echo $l['estado']; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="home.php?pg=<?php echo $pg; ?>&idlote=<?php echo $l['idlote']; ?>&ope=edi"
                                           class="btn btn-outline-warning" title="Editar">
                                            <i class="fas fa-pen-to-square"></i>
                                        </a>
                                        <a href="home.php?pg=<?php echo $pg; ?>&idlote=<?php echo $l['idlote']; ?>&ope=eli"
                                           class="btn btn-outline-danger" title="Eliminar"
                                           onclick="return confirm('¿Eliminar este lote permanentemente?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    No hay lotes registrados
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL NUEVO / EDITAR LOTE -->
<div class="modal fade" id="modalLote" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="home.php?pg=<?php echo $pg; ?>" method="POST">
                <input type="hidden" name="ope"    value="save">
                <input type="hidden" name="idlote" value="<?php echo $dtOne['idlote'] ?? ''; ?>">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-box"></i>
                        <?php echo $dtOne ? 'Editar Lote #'.$dtOne['idlote'] : 'Registrar Lote Manual'; ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <!-- PRODUCTO -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Producto <span class="text-danger">*</span></label>
                            <select name="idprod" id="selectProdLote" class="form-select" required>
                                <option value="" data-costouni="">Seleccione producto...</option>
                                <?php foreach ($productos as $p): ?>
                                    <option value="<?php echo $p['idprod']; ?>"
                                        data-costouni="<?php echo $p['costouni'] ?? ''; ?>"
                                        <?php echo (isset($dtOne) && $dtOne['idprod'] == $p['idprod']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($p['nomprod']); ?> (<?php echo $p['codprod']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- CODIGO LOTE -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Código del Lote <span class="text-danger">*</span></label>
                            <input type="text" name="codlot" class="form-control"
                                   value="<?php echo htmlspecialchars($dtOne['codlot'] ?? ''); ?>"
                                   placeholder="Ej: LOT-2024-001" required>
                        </div>

                        <!-- UBICACIÓN -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><i class="fas fa-warehouse"></i> Ubicación / Almacén</label>
                            <select name="idubi" class="form-select">
                                <option value="">Sin ubicación asignada</option>
                                <?php foreach ($ubicaciones as $u): ?>
                                    <option value="<?php echo $u['idubi']; ?>"
                                        <?php echo (isset($dtOne) && $dtOne['idubi'] == $u['idubi']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($u['nomubi']); ?> (<?php echo $u['codubi']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- COSTO UNITARIO -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Costo Unitario
                                <span id="badgeCostoSync" class="badge bg-info-subtle text-info border border-info-subtle ms-1 d-none" style="font-size:.7rem">
                                    <i class="fas fa-link"></i> Sincronizado del producto
                                </span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.0001" name="costuni" id="inputCostuniLote" class="form-control"
                                       value="<?php echo $dtOne['costuni'] ?? '0'; ?>"
                                       placeholder="0.0000">
                            </div>
                            <small class="text-muted">Se rellena automáticamente al seleccionar producto. Puede editarlo.</small>
                        </div>

                        <!-- FECHA INGRESO -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha Ingreso <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="fecing" class="form-control"
                                   value="<?php echo isset($dtOne) && $dtOne['fecing']
                                       ? date('Y-m-d\TH:i', strtotime($dtOne['fecing']))
                                       : date('Y-m-d\TH:i'); ?>" required>
                        </div>

                        <!-- FECHA VENCIMIENTO -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha Vencimiento</label>
                            <input type="date" name="fecven" class="form-control"
                                   value="<?php echo $dtOne['fecven'] ?? ''; ?>">
                            <small class="text-muted">Dejar vacío si no vence</small>
                        </div>

                        <!-- CANT INICIAL -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Cantidad Inicial <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="cantini" class="form-control"
                                   value="<?php echo $dtOne['cantini'] ?? ''; ?>"
                                   placeholder="0.00" required>
                        </div>

                        <!-- CANT ACTUAL -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Cantidad Actual</label>
                            <input type="number" step="0.01" name="cantact" class="form-control"
                                   value="<?php echo $dtOne['cantact'] ?? $dtOne['cantini'] ?? ''; ?>"
                                   placeholder="Igual a cant. inicial si es nuevo">
                            <small class="text-muted">Se actualiza automáticamente con entradas/salidas</small>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-xmark"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Lote
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ── Sincronización Producto → Costo Unitario ──────────────────
document.addEventListener('DOMContentLoaded', function () {
    const selProd    = document.getElementById('selectProdLote');
    const inpCosto   = document.getElementById('inputCostuniLote');
    const badge      = document.getElementById('badgeCostoSync');

    function sincronizarCosto() {
        const opt      = selProd.options[selProd.selectedIndex];
        const costouni = opt ? opt.dataset.costouni : '';
        if (costouni !== '' && costouni !== null && costouni !== undefined) {
            inpCosto.value = parseFloat(costouni).toFixed(4);
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
    }

    selProd.addEventListener('change', sincronizarCosto);

    // Si ya hay producto seleccionado al cargar (modo edición) no sobreescribir
    // solo mostrar el badge si coinciden
    <?php if (!$dtOne): ?>
    // Modo NUEVO: sincronizar al abrir si hay selección inicial
    if (selProd.value) sincronizarCosto();
    <?php else: ?>
    // Modo EDICIÓN: abrir modal
    new bootstrap.Modal(document.getElementById('modalLote')).show();
    // Mostrar badge si el costo en el lote coincide con el del producto
    const optAct = selProd.options[selProd.selectedIndex];
    if (optAct && optAct.dataset.costouni &&
        parseFloat(optAct.dataset.costouni).toFixed(4) === parseFloat(inpCosto.value).toFixed(4)) {
        badge.classList.remove('d-none');
    }
    <?php endif; ?>
});
</script>
