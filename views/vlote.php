<?php 
require_once 'controllers/clote.php'; 
?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fa-solid fa-boxes-stacked text-primary"></i> Gestión de Lotes</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalLote">
            <i class="fa-solid fa-plus"></i> Nuevo Lote
        </button>
    </div>

    <!-- TABLA DE LOTES -->
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Producto</th>
                    <th>Código Lote</th>
                    <th>F. Ingreso</th>
                    <th>F. Vencimiento</th>
                    <th>Cant. Inicial</th>
                    <th>Cant. Actual</th>
                    <th>Costo Uni.</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($dtAll && count($dtAll) > 0): ?>
                    <?php foreach ($dtAll as $l): ?>
                    <tr>
                        <td><?= $l['idlote'] ?></td>
                        <td><?= htmlspecialchars($l['nomprod'] ?? 'Sin producto') ?></td>
                        <td><?= htmlspecialchars($l['codlot']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($l['fecing'])) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($l['fecven'])) ?></td>
                        <td><?= number_format($l['cantini'], 2) ?></td>
                        <td><?= number_format($l['cantact'], 2) ?></td>
                        <td><?= number_format($l['cstuni'], 2) ?></td>
                        <td>
                            <a href="home.php?pg=1008&ope=eDi&idlote=<?= $l['idlote'] ?>" class="btn btn-warning btn-sm">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <a href="home.php?pg=1008&ope=eLi&idlote=<?= $l['idlote'] ?>" 
                               class="btn btn-danger btn-sm" 
                               onclick="return confirm('¿Seguro que deseas eliminar este lote?')">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted">No hay lotes registrados</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ============= MODAL ============= -->
<div class="modal fade" id="modalLote" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form action="home.php?pg=1008" method="POST">
                <input type="hidden" name="ope" value="SaVe">
                <input type="hidden" name="idlote" value="<?= $dtOne['idlote'] ?? '' ?>">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-box"></i>
                        <?= isset($dtOne) ? 'Editar Lote #'.$dtOne['idlote'] : 'Nuevo Lote' ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row">

                        <!-- PRODUCTO -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Producto *</label>
                            <select name="idprod" class="form-select" required>
                                <option value="">Seleccione producto...</option>
                                <?php foreach ($productos as $p): ?>
                                    <option value="<?= $p['idprod'] ?>"
                                        <?= (isset($dtOne) && $dtOne['idprod'] == $p['idprod']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['nomprod']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- CODIGO LOTE -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Código del Lote *</label>
                            <input type="text" name="codlot" class="form-control"
                                value="<?= $dtOne['codlot'] ?? '' ?>" required>
                        </div>

                        <!-- FECHA INGRESO -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Fecha Ingreso *</label>
                            <input type="datetime-local" name="fecing" class="form-control"
                                value="<?= isset($dtOne)
                                    ? date('Y-m-d\TH:i', strtotime($dtOne['fecing']))
                                    : date('Y-m-d\TH:i') ?>" required>
                        </div>

                        <!-- FECHA VENCIMIENTO +1 MES -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Fecha Vencimiento *</label>
                            <input type="datetime-local" name="fecven" class="form-control"
                                value="<?= isset($dtOne)
                                    ? date('Y-m-d\TH:i', strtotime($dtOne['fecven']))
                                    : date('Y-m-d\TH:i', strtotime('+1 month')) ?>" required>
                        </div>

                        <!-- CANT INICIAL -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Cantidad Inicial *</label>
                            <input type="number" step="0.01" name="cantini" class="form-control"
                                value="<?= $dtOne['cantini'] ?? '' ?>" required>
                        </div>

                        <!-- CANT ACTUAL -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Cantidad Actual</label>
                            <input type="number" step="0.01" name="cantact" class="form-control"
                                value="<?= $dtOne['cantact'] ?? ($dtOne['cantini'] ?? '') ?>">
                            <small class="text-muted">Al crear nuevo = cantidad inicial</small>
                        </div>

                        <!-- COSTO -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Costo Unitario</label>
                            <input type="number" step="0.01" name="cstuni" class="form-control"
                                value="<?= $dtOne['cstuni'] ?? '0.00' ?>">
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-save"></i> Guardar Lote
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
