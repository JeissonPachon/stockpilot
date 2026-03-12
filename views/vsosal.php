<?php include("controllers/csosal.php"); ?>
<style>
    .card { border: none; border-radius: 12px; }
    .card-header { font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
    .table-container { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
</style>

<?php
$salidaProcesada = (($cab['estsal'] ?? '') === 'Procesada');
$tieneDetalles = !empty($detalles);
$bloquearAlmacen = $tieneDetalles || $salidaProcesada;
$estadoSalida = $cab['estsal'] ?? 'Pendiente';

$total_salida = 0;
if (!empty($detalles)) {
    foreach ($detalles as $d) {
        $total_salida += $d['totdet'];
    }
}
$detallesCount = !empty($detalles) ? count($detalles) : 0;
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">
                <i class="fas fa-truck-loading text-primary"></i> Nueva Salida de Almacén
                <span class="badge ms-2 <?php echo $salidaProcesada ? 'bg-success' : 'bg-secondary'; ?>">
                    <?php echo htmlspecialchars($estadoSalida); ?>
                </span>
            </h2>

            <?php if (isset($_SESSION['mensaje'])): ?>
                <div class="alert alert-<?php echo $_SESSION['tipo_mensaje']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['mensaje']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['mensaje']); unset($_SESSION['tipo_mensaje']); ?>
            <?php endif; ?>
            
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-file-invoice"></i> Datos Generales del Documento
                </div>
                <div class="card-body">
                    <form id="formSalida" method="POST" action="home.php?pg=<?php echo $pg; ?>">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Tipo de Salida</label>
                                <select class="form-select" name="tpsal" id="tipo_salida" required>
                                    <option value="Venta" <?php echo ($cab && $cab['tpsal'] == 'Venta') ? 'selected' : ''; ?>>Venta</option>
                                    <option value="Traslado" <?php echo ($cab && $cab['tpsal'] == 'Traslado') ? 'selected' : ''; ?>>Traslado entre Almacenes</option>
                                    <option value="Merma" <?php echo ($cab && $cab['tpsal'] == 'Merma') ? 'selected' : ''; ?>>Merma / Desecho</option>
                                    <option value="Ajuste" <?php echo ($cab && $cab['tpsal'] == 'Ajuste') ? 'selected' : ''; ?>>Ajuste de Inventario (-)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Referencia / Factura</label>
                                <input type="text" class="form-control" name="refdoc" value="<?php echo $cab['refdoc'] ?? ''; ?>" placeholder="Ej: FAC-00123" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Almacén (Origen)</label>
                                <select class="form-select" name="idubi" id="idubi_salida" required <?php echo $bloquearAlmacen ? 'disabled' : ''; ?>>
                                    <option selected disabled>Seleccionar almacén...</option>
                                    <?php foreach ($almacenes as $a): ?>
                                        <option value="<?php echo $a['idubi']; ?>" <?php echo ($cab && $cab['idubi'] == $a['idubi']) ? 'selected' : ''; ?>>
                                            <?= $a['nomubi']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($bloquearAlmacen): ?>
                                    <input type="hidden" name="idubi" value="<?php echo htmlspecialchars((string)($cab['idubi'] ?? '')); ?>">
                                    <small class="text-muted">Bloqueado porque la salida ya tiene detalle o esta procesada.</small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fecha de Salida</label>
                                <input type="date" class="form-control bg-light" name="fecsal" value="<?php echo !empty($cab['fecsal']) ? date('Y-m-d', strtotime($cab['fecsal'])) : date('Y-m-d'); ?>" readonly>
                                <small class="text-muted">Fecha fija al día de hoy</small>
                            </div>
                        </div>
                        <input type="hidden" name="ope" value="SaVe">
                        <input type="hidden" name="idsal" value="<?php echo $idsal; ?>">
                        <input type="hidden" name="idemp" value="<?php echo $_SESSION['idemp'] ?? 1; ?>">
                        <input type="hidden" name="idusu" value="<?php echo $_SESSION['idusu'] ?? 1; ?>">
                        <div class="mt-3 text-end">
                            <button type="submit" class="btn btn-primary btn-sm">Guardar Cabecera</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm mb-4 border-start border-info border-4">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fas fa-plus-circle text-info"></i> Agregar Productos a la Lista</h5>
                    <form method="POST" action="home.php?pg=<?php echo $pg; ?>&idsal=<?php echo $idsal; ?>">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Producto</label>
                                <select class="form-select" name="idprod" required onchange="cargarLotes(this.value)">
                                    <option value="" selected disabled>Seleccionar producto...</option>
                                    <?php foreach ($productos as $p): ?>
                                        <option value="<?php echo $p['idprod']; ?>"><?php echo $p['nomprod']; ?> (<?php echo $p['codprod']; ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Cantidad</label>
                                <input type="number" class="form-control" name="cantdet" id="cantdet" min="1" value="1" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Lote (Stock Disponible)</label>
                                <select class="form-select" name="idlote" id="select_lote">
                                    <option value="" selected>Asignacion automatica FIFO</option>
                                </select>
                                <small id="origen_hint" class="text-muted">Origen: FIFO automatico</small>
                                <small id="stock_hint" class="text-muted d-block">Stock disponible: —</small>
                                <div id="stock_alert" class="alert alert-warning py-1 px-2 mt-1 d-none">
                                    Stock insuficiente para la cantidad solicitada.
                                </div>
                            </div>
                            <div class="col-md-2">
                                <input type="hidden" name="ope" value="save">
                                <input type="hidden" name="idsal" value="<?php echo $idsal; ?>">
                                <input type="hidden" name="tpsal_actual" id="tpsal_actual" value="<?php echo htmlspecialchars($cab['tpsal'] ?? 'Venta'); ?>">
                                <button type="submit" id="btn_add_det" class="btn btn-info w-100 text-white" data-base-disabled="<?php echo (!$idsal || $salidaProcesada) ? '1' : '0'; ?>" <?php echo (!$idsal || $salidaProcesada) ? 'disabled' : ''; ?>>
                                    <i class="fas fa-cart-plus"></i> Agregar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-body d-flex flex-wrap align-items-center gap-3">
                    <div class="me-auto">
                        <div class="text-muted small">Resumen de salida</div>
                        <div class="fw-bold">Items: <?= $detallesCount ?> | Total: $<?= number_format((float)$total_salida, 2) ?></div>
                    </div>
                    <?php if ($detallesCount > 0): ?>
                        <span class="badge bg-success">Lista para confirmar</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">Sin productos</span>
                    <?php endif; ?>
                </div>
            </div>

            <div id="detalle_salida" class="table-container shadow-sm">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2 barra-filtro-sticky">
                    <h5 class="mb-0">Lista de Productos a Despachar</h5>
                    <div class="d-flex align-items-center gap-2 resumen-origen-wrap">
                        <span id="contador_origen" class="badge bg-light text-dark border">Filas: 0/0</span>
                        <span id="contador_cantidad" class="badge bg-light text-dark border">Cantidad: 0,00</span>
                        <label for="filtro_origen" class="form-label mb-0 small text-muted">Filtrar origen:</label>
                        <select id="filtro_origen" class="form-select form-select-sm filtro-origen-select" style="min-width: 170px;">
                            <option value="">Todos</option>
                            <option value="FIFO">FIFO auto</option>
                            <option value="MANUAL">Manual</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th class="text-primary">Lote</th>
                                <th>Origen</th>
                                <th>Fecha Venc.</th>
                                <th>Costo Unit.</th>
                                <th>Subtotal</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (!empty($detalles)): 
                                foreach ($detalles as $d): 
                            ?>
                                <tr data-origen="<?php echo htmlspecialchars($d['origen'] ?? 'MANUAL'); ?>" data-totdet="<?php echo htmlspecialchars((string)$d['totdet']); ?>" data-cantdet="<?php echo htmlspecialchars((string)$d['cantdet']); ?>">
                                    <td>
                                        <strong><?php echo htmlspecialchars($d['nomprod']); ?></strong><br>
                                        <small class="text-muted">Lote: <?php echo $d['codlot']; ?></small>
                                    </td>
                                    <td><?php echo $d['cantdet']; ?> Unidades</td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo $d['codlot']; ?></span>
                                        <?php
                                            $cantActLote = isset($d['cantact_lote']) ? (float)$d['cantact_lote'] : null;
                                            $cantDetRow = (float)$d['cantdet'];
                                        ?>
                                        <?php if ($cantActLote !== null): ?>
                                            <?php if ($cantActLote <= 0): ?>
                                                <span class="badge bg-danger ms-1">Lote agotado</span>
                                            <?php elseif ($cantActLote < $cantDetRow): ?>
                                                <span class="badge bg-warning text-dark ms-1">Stock bajo</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (($d['origen'] ?? 'MANUAL') === 'FIFO'): ?>
                                            <span class="badge bg-info text-dark">FIFO auto</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Manual</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($d['fecven'])): ?>
                                            <span class="<?php echo (strtotime($d['fecven']) < time()) ? 'text-danger fw-semibold' : 'text-muted'; ?>">
                                                <?php echo date('d/m/Y', strtotime($d['fecven'])); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Sin venc.</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>$<?php echo number_format($d['vundet'], 2); ?></td>
                                    <td>$<?php echo number_format($d['totdet'], 2); ?></td>
                                    <td class="text-center">
                                        <a href="home.php?pg=<?php echo $pg; ?>&idsal=<?php echo $idsal; ?>&delete=<?php echo $d['iddsal']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Eliminar producto?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php 
                                endforeach; 
                            else: 
                            ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted" id="detalle_empty_state">No hay productos en la lista</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-light fw-bold">
                                <td colspan="6" class="text-end">VALOR TOTAL DE SALIDA (Afectación Kardex):</td>
                                <td colspan="2" class="text-success" id="total_visible_salida" data-total-base="<?php echo htmlspecialchars((string)$total_salida); ?>">$<?php echo number_format($total_salida, 2); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end">
                <?php if ($detallesCount > 0): ?>
                    <div class="alert alert-success me-auto mb-0 d-flex align-items-center">
                        <i class="fas fa-check-circle me-2"></i>
                        Listo para confirmar: <?= $detallesCount ?> items. Total: $<?= number_format((float)$total_salida, 2) ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning me-auto mb-0 d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Agrega al menos un producto para poder confirmar la salida.
                    </div>
                <?php endif; ?>
                <a href="home.php?pg=1013" class="btn btn-outline-secondary me-2 btn-lg">Nueva Salida / Limpiar</a>
                <form method="POST" action="home.php?pg=<?php echo $pg; ?>&idsal=<?php echo $idsal; ?>">
                    <input type="hidden" name="ope" value="Fin">
                    <button type="submit" class="btn btn-success btn-lg" <?php echo (!$idsal || empty($detalles) || (($cab['estsal'] ?? '') === 'Procesada')) ? 'disabled' : ''; ?>>
                        <i class="fas fa-check-double"></i> Confirmar Salida y Actualizar Kardex
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
    // Datos del producto actual (cargados desde el AJAX)
    let _prodData = { costouni: 0, precioven: 0, lotes: [] };

    // Tipo de salida actual
    const tipoSalidaEl = document.getElementById('tipo_salida');

    function getTipoSalida() {
        return tipoSalidaEl ? tipoSalidaEl.value : '';
    }

    // Cuando cambia el tipo de salida, recalcular precio
    if (tipoSalidaEl) {
        tipoSalidaEl.addEventListener('change', function() {
            const tipoHidden = document.getElementById('tpsal_actual');
            if (tipoHidden) {
                tipoHidden.value = this.value;
            }
            actualizarPrecio();
        });
    }

    function cargarLotes(idprod) {
        const selectLote = document.getElementById('select_lote');
        const origenHint = document.getElementById('origen_hint');
        const stockHint = document.getElementById('stock_hint');
        const stockAlert = document.getElementById('stock_alert');
        const btnAdd = document.getElementById('btn_add_det');
        const idubiEl = document.getElementById('idubi_salida');
        const idubi = idubiEl ? idubiEl.value : '';
        selectLote.innerHTML = '<option value="" selected disabled>Cargando lotes...</option>';

        if (!idubi) {
            selectLote.innerHTML = '<option value="" selected>Seleccione primero el almacen</option>';
            if (origenHint) origenHint.textContent = 'Origen: FIFO automatico (requiere almacen)';
            if (stockHint) stockHint.textContent = 'Stock disponible: —';
            if (stockAlert) stockAlert.classList.add('d-none');
            return;
        }

        // URL absoluta para evitar problemas con query strings en la página padre
        const url = `<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); ?>/controllers/csosal.php?idprod=${idprod}&idubi=${idubi}`;

        fetch(url)
            .then(r => {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(data => {
                _prodData = data;

                selectLote.innerHTML = '<option value="" selected>Asignacion automatica FIFO</option>';
                if (data.lotes && data.lotes.length > 0) {
                    data.lotes.forEach(lote => {
                        const option = document.createElement('option');
                        option.value           = lote.idlote;
                        option.dataset.costuni = lote.costuni || 0;
                        option.dataset.cantact = lote.cantact || 0;
                        option.textContent     = `${lote.codlot} — Stock: ${parseFloat(lote.cantact).toLocaleString()} | Costo: $${parseFloat(lote.costuni||0).toFixed(2)}`;
                        selectLote.appendChild(option);
                    });
                    actualizarPrecio();
                    if (origenHint) origenHint.textContent = 'Origen: FIFO automatico (sin seleccionar lote)';
                } else {
                    selectLote.innerHTML = '<option value="" selected>Sin lotes disponibles (se validara al guardar)</option>';
                    if (origenHint) origenHint.textContent = 'Origen: FIFO automatico';
                }
                actualizarStockUI();
            })
            .catch(err => {
                console.error('Error cargando lotes:', err);
                selectLote.innerHTML = '<option value="" selected disabled>Error al cargar lotes</option>';
                if (origenHint) origenHint.textContent = 'Origen: FIFO automatico (error al cargar)';
                if (stockHint) stockHint.textContent = 'Stock disponible: —';
                if (stockAlert) stockAlert.classList.add('d-none');
            });
    }

    function actualizarPrecio() {
        const tipo       = getTipoSalida();
        const vundetEl   = document.getElementById('vundet');
        const lblEl      = document.getElementById('lbl_tipo_precio');
        const selectLote = document.getElementById('select_lote');
        const optSel     = selectLote ? selectLote.options[selectLote.selectedIndex] : null;
        const costuniLote = optSel && optSel.dataset.costuni ? parseFloat(optSel.dataset.costuni) : 0;
        const origenHint = document.getElementById('origen_hint');
        if (origenHint && selectLote) {
            origenHint.textContent = selectLote.value ? 'Origen: Manual (lote seleccionado)' : 'Origen: FIFO automatico';
        }

        if (!vundetEl) return; // El campo precio puede no existir en esta vista

        if (tipo === 'Venta') {
            const pv = parseFloat(_prodData.precioven) || 0;
            vundetEl.value = pv.toFixed(2);
            if (lblEl) { lblEl.textContent = '(P. Venta)'; lblEl.className = 'text-success fw-semibold'; }
        } else if (tipo === 'Traslado' || tipo === 'Ajuste' || tipo === 'Merma') {
            vundetEl.value = costuniLote.toFixed(2);
            if (lblEl) { lblEl.textContent = '(Costo lote)'; lblEl.className = 'text-muted'; }
        } else {
            vundetEl.value = '0.00';
            if (lblEl) lblEl.textContent = '';
        }
    }

    function actualizarStockUI() {
        const selectLote = document.getElementById('select_lote');
        const cantInput = document.getElementById('cantdet');
        const stockHint = document.getElementById('stock_hint');
        const stockAlert = document.getElementById('stock_alert');
        const btnAdd = document.getElementById('btn_add_det');

        if (!selectLote || !cantInput) return;

        const cant = parseFloat(cantInput.value || '0');
        let disponible = 0;

        if (selectLote.value) {
            const optSel = selectLote.options[selectLote.selectedIndex];
            disponible = optSel && optSel.dataset.cantact ? parseFloat(optSel.dataset.cantact) : 0;
        } else if (_prodData && Array.isArray(_prodData.lotes)) {
            _prodData.lotes.forEach(l => {
                const c = parseFloat(l.cantact || 0);
                if (!isNaN(c)) disponible += c;
            });
        }

        if (stockHint) {
            stockHint.textContent = `Stock disponible: ${disponible.toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        }

        const insuficiente = cant > 0 && disponible > 0 && cant > disponible;
        if (stockAlert) {
            stockAlert.classList.toggle('d-none', !insuficiente);
        }
        if (btnAdd) {
            const baseDisabled = btnAdd.getAttribute('data-base-disabled') === '1';
            btnAdd.disabled = baseDisabled || insuficiente;
        }
    }

    document.addEventListener('change', function (e) {
        if (e.target && (e.target.id === 'select_lote' || e.target.id === 'cantdet')) {
            actualizarStockUI();
        }
    });

    document.addEventListener('input', function (e) {
        if (e.target && e.target.id === 'cantdet') {
            actualizarStockUI();
        }
    });
</script>

<script>
    (function () {
        const params = new URLSearchParams(window.location.search);
        const focus = params.get('focus');
        if (focus === 'det') {
            const el = document.getElementById('detalle_salida');
            if (el) {
                el.classList.add('border', 'border-success');
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                setTimeout(() => el.classList.remove('border', 'border-success'), 2500);
            }
        }
    })();
</script>

<script>
    (function () {
        const filtro = document.getElementById('filtro_origen');
        const contador = document.getElementById('contador_origen');
        const contadorCantidad = document.getElementById('contador_cantidad');
        const totalVisibleEl = document.getElementById('total_visible_salida');
        if (!filtro) return;

        const tabla = document.querySelector('.table-container table');
        if (!tabla) return;

        const tbody = tabla.querySelector('tbody');
        if (!tbody) return;

        function aplicarFiltroOrigen() {
            const valor = (filtro.value || '').trim().toUpperCase();
            const filas = tbody.querySelectorAll('tr[data-origen]');
            const total = filas.length;
            let totalVisible = 0;
            let cantidadVisible = 0;
            const nf = new Intl.NumberFormat('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            let visibles = 0;
            filas.forEach(function (fila) {
                const origen = (fila.getAttribute('data-origen') || '').toUpperCase();
                const mostrar = !valor || origen === valor;
                fila.style.display = mostrar ? '' : 'none';
                if (mostrar) {
                    visibles += 1;
                    const subtotal = parseFloat(fila.getAttribute('data-totdet') || '0');
                    totalVisible += isNaN(subtotal) ? 0 : subtotal;
                    const cantidad = parseFloat(fila.getAttribute('data-cantdet') || '0');
                    cantidadVisible += isNaN(cantidad) ? 0 : cantidad;
                }
            });

            if (contador) {
                contador.textContent = `Filas: ${visibles}/${total}`;
            }

            if (contadorCantidad) {
                contadorCantidad.textContent = `Cantidad: ${nf.format(cantidadVisible)}`;
            }

            if (totalVisibleEl) {
                totalVisibleEl.textContent = `$${nf.format(totalVisible)}`;
            }
        }

        filtro.addEventListener('change', aplicarFiltroOrigen);
        aplicarFiltroOrigen();

        const barraSticky = document.querySelector('.barra-filtro-sticky');
        const tableContainer = document.querySelector('.table-container');
        const tablaResponsive = document.querySelector('.table-container .table-responsive');

        function actualizarOffsetSticky() {
            if (!barraSticky || !tableContainer) return;
            const h = barraSticky.offsetHeight || 48;
            tableContainer.style.setProperty('--sticky-offset', `${h}px`);
        }

        function actualizarSombraThead() {
            if (!tablaResponsive || !tableContainer) return;
            const activo = tablaResponsive.scrollTop > 2;
            tableContainer.classList.toggle('thead-sticky-active', activo);
        }

        window.addEventListener('resize', actualizarOffsetSticky);
        if (tablaResponsive) {
            tablaResponsive.addEventListener('scroll', actualizarSombraThead);
        }
        actualizarOffsetSticky();
        actualizarSombraThead();
    })();
</script>

<style>
.barra-filtro-sticky {
    position: sticky;
    top: 0;
    z-index: 5;
    background: #fff;
    padding: 0.35rem 0;
    border-bottom: 1px solid #eef2f7;
}

.table-container {
    --sticky-offset: 48px;
}

.table-container .table thead th {
    position: sticky;
    top: var(--sticky-offset);
    z-index: 4;
    background: #f8f9fa;
    transition: box-shadow .18s ease;
}

.table-container.thead-sticky-active .table thead th {
    box-shadow: 0 5px 10px -8px rgba(15, 23, 42, 0.5);
}

@media (max-width: 576px) {
    .resumen-origen-wrap {
        width: 100%;
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-start;
    }

    .resumen-origen-wrap .badge {
        margin-bottom: 0.15rem;
    }

    .resumen-origen-wrap label {
        width: 100%;
        margin-top: 0.25rem;
    }

    .resumen-origen-wrap .filtro-origen-select {
        width: 100%;
        min-width: 0 !important;
    }

    .barra-filtro-sticky {
        padding-top: 0.2rem;
        padding-bottom: 0.45rem;
    }

    .table-container .table thead th {
        top: var(--sticky-offset);
    }
}
</style>
