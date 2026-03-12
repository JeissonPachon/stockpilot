<?php
require_once('controllers/cmovim.php');

$idkarSelected = isset($_REQUEST['idkar']) ? (int)$_REQUEST['idkar'] : 0;
$periodoLabel = '';
if (!empty($datKardex)) {
    foreach ($datKardex as $k) {
        if ((int)$k['idkar'] === $idkarSelected) {
            $periodoLabel = $k['anio'] . ' - Mes ' . $k['mes'];
            break;
        }
    }
}
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h2><i class="fas fa-calendar-alt me-2"></i>Movimientos por Periodo</h2>
            <p class="text-muted mb-0">Selecciona un mes para ver los movimientos registrados.</p>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-filter me-2"></i>Filtro de Periodo
        </div>
        <div class="card-body">
            <form method="GET" action="home.php" class="row g-3 align-items-end">
                <input type="hidden" name="pg" value="<?= $pg ?>">
                <div class="col-md-6">
                    <label class="form-label">Periodo (Kardex)</label>
                    <select name="idkar" class="form-select" required>
                        <option value="">Seleccione un periodo</option>
                        <?php foreach (($datKardex ?: []) as $k): ?>
                            <option value="<?= $k['idkar'] ?>" <?= $idkarSelected === (int)$k['idkar'] ? 'selected' : '' ?>>
                                <?= $k['anio'] ?> - Mes <?= $k['mes'] ?><?= ((int)($k['cerrado'] ?? 0) === 1) ? ' (Cerrado)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Ver movimientos
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex align-items-center gap-2">
            <i class="fas fa-list"></i> Movimientos <?= $periodoLabel ? '— ' . htmlspecialchars($periodoLabel) : '' ?>
        </div>
        <div class="card-body">
            <?php if (!$idkarSelected): ?>
                <div class="alert alert-info mb-0">
                    Selecciona un periodo para ver los movimientos.
                </div>
            <?php else: ?>
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="text-success fw-bold fs-4"><?= number_format($resumenMov['entradas'], 2, ',', '.') ?></div>
                                <div class="text-muted small">Entradas (cant)</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="text-danger fw-bold fs-4"><?= number_format($resumenMov['salidas'], 2, ',', '.') ?></div>
                                <div class="text-muted small">Salidas (cant)</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="fw-bold fs-4">$<?= number_format($resumenMov['totalEntradas'], 2, ',', '.') ?></div>
                                <div class="text-muted small">Total Entradas</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="fw-bold fs-4">$<?= number_format($resumenMov['totalSalidas'], 2, ',', '.') ?></div>
                                <div class="text-muted small">Total Salidas</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="alert alert-light border mb-3">
                    <strong>Saldo del periodo:</strong>
                    Cantidad: <span class="fw-bold <?= $resumenMov['saldoCant'] >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($resumenMov['saldoCant'], 2, ',', '.') ?></span>
                    | Valor: <span class="fw-bold <?= $resumenMov['saldoVal'] >= 0 ? 'text-success' : 'text-danger' ?>">$<?= number_format($resumenMov['saldoVal'], 2, ',', '.') ?></span>
                </div>
                <div class="table-responsive">
                    <table id="tableMovimientos" class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Producto</th>
                                <th>Ubicacion</th>
                                <th>Tipo</th>
                                <th>Cantidad</th>
                                <th>Valor Unit.</th>
                                <th>Total</th>
                                <th>Doc. Ref</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($datAll)): foreach ($datAll as $row):
                                $total = (float)$row['cantmov'] * (float)$row['valmov'];
                            ?>
                            <tr>
                                <td><?= $row['idmov'] ?></td>
                                <td><?= date('d/m/Y', strtotime($row['fecmov'])) ?></td>
                                <td><?= htmlspecialchars($row['nomprod'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['nomubi'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if ((int)$row['tipmov'] === 1): ?>
                                        <span class="badge bg-success">Entrada</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Salida</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= number_format((float)$row['cantmov'], 2, ',', '.') ?></td>
                                <td>$<?= number_format((float)$row['valmov'], 2, ',', '.') ?></td>
                                <td><strong>$<?= number_format($total, 2, ',', '.') ?></strong></td>
                                <td><?= htmlspecialchars($row['docref'] ?? '-') ?></td>
                                <td><?= htmlspecialchars(trim(($row['nomusu'] ?? '') . ' ' . ($row['apeusu'] ?? ''))) ?></td>
                            </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted">No hay movimientos para este periodo</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
if (typeof $.fn.DataTable !== 'undefined' && document.getElementById('tableMovimientos')) {
    $('#tableMovimientos').DataTable({
        language: {
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
                last: "Último",
                next: "Siguiente",
                previous: "Anterior"
            }
        },
        pageLength: 25,
        order: [[0, 'desc']]
    });
}
</script>
