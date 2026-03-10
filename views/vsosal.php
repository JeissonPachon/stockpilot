<?php include("controllers/csosal.php"); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Salida de Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .card { border: none; border-radius: 12px; }
        .card-header { font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .table-container { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-truck-loading text-primary"></i> Nueva Salida de Almacén</h2>

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
                                <select class="form-select" name="idubi" required>
                                    <option selected disabled>Seleccionar almacén...</option>
                                    <?php foreach ($almacenes as $a): ?>
                                        <option value="<?php echo $a['idubi']; ?>" <?php echo ($cab && $cab['idubi'] == $a['idubi']) ? 'selected' : ''; ?>>
                                            <?= $a['nomubi']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
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
                                <input type="number" class="form-control" name="cantdet" min="1" value="1" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Lote (Stock Disponible)</label>
                                <select class="form-select" name="idlote" id="select_lote" required>
                                    <option value="" selected disabled>Seleccione producto primero...</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="hidden" name="ope" value="save">
                                <input type="hidden" name="idsal" value="<?php echo $idsal; ?>">
                                <button type="submit" class="btn btn-info w-100 text-white" <?php echo !$idsal ? 'disabled' : ''; ?>>
                                    <i class="fas fa-cart-plus"></i> Agregar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-container shadow-sm">
                <h5 class="mb-3">Lista de Productos a Despachar</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th class="text-primary">Lote Asignado (FIFO)</th>
                                <th>Fecha Venc.</th>
                                <th>Costo Unit.</th>
                                <th>Subtotal</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <tbody>
                            <?php 
                            $total_salida = 0;
                            if (!empty($detalles)): 
                                foreach ($detalles as $d): 
                                    $total_salida += $d['totdet'];
                            ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($d['nomprod']); ?></strong><br>
                                        <small class="text-muted">Lote: <?php echo $d['codlot']; ?></small>
                                    </td>
                                    <td><?php echo $d['cantdet']; ?> Unidades</td>
                                    <td><span class="badge bg-primary"><?php echo $d['codlot']; ?></span></td>
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
                                    <td colspan="7" class="text-center text-muted">No hay productos en la lista</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-light fw-bold">
                                <td colspan="5" class="text-end">VALOR TOTAL DE SALIDA (Afectación Kardex):</td>
                                <td colspan="2" class="text-success">$<?php echo number_format($total_salida, 2); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end">
                <a href="home.php?pg=1012" class="btn btn-outline-secondary me-2 btn-lg">Nueva Salida / Limpiar</a>
                <form method="POST" action="home.php?pg=<?php echo $pg; ?>&idsal=<?php echo $idsal; ?>">
                    <input type="hidden" name="ope" value="Fin">
                    <button type="submit" class="btn btn-success btn-lg" <?php echo (!$idsal || empty($detalles)) ? 'disabled' : ''; ?>>
                        <i class="fas fa-check-double"></i> Confirmar Salida y Actualizar Kardex
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
            actualizarPrecio();
        });
    }

    function cargarLotes(idprod) {
        const selectLote = document.getElementById('select_lote');
        selectLote.innerHTML = '<option value="" selected disabled>Cargando lotes...</option>';

        // URL absoluta para evitar problemas con query strings en la página padre
        const url = `<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); ?>/controllers/csosal.php?idprod=${idprod}`;

        fetch(url)
            .then(r => {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(data => {
                _prodData = data;

                selectLote.innerHTML = '<option value="" selected disabled>Seleccionar lote...</option>';
                if (data.lotes && data.lotes.length > 0) {
                    data.lotes.forEach(lote => {
                        const option = document.createElement('option');
                        option.value           = lote.idlote;
                        option.dataset.costuni = lote.costuni || 0;
                        option.textContent     = `${lote.codlot} — Stock: ${parseFloat(lote.cantact).toLocaleString()} | Costo: $${parseFloat(lote.costuni||0).toFixed(2)}`;
                        selectLote.appendChild(option);
                    });
                    selectLote.selectedIndex = 1;
                    actualizarPrecio();
                } else {
                    selectLote.innerHTML = '<option value="" selected disabled>Sin stock disponible</option>';
                }
            })
            .catch(err => {
                console.error('Error cargando lotes:', err);
                selectLote.innerHTML = '<option value="" selected disabled>Error al cargar lotes</option>';
            });
    }

    function actualizarPrecio() {
        const tipo       = getTipoSalida();
        const vundetEl   = document.getElementById('vundet');
        const lblEl      = document.getElementById('lbl_tipo_precio');
        const selectLote = document.getElementById('select_lote');
        const optSel     = selectLote ? selectLote.options[selectLote.selectedIndex] : null;
        const costuniLote = optSel && optSel.dataset.costuni ? parseFloat(optSel.dataset.costuni) : 0;

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
</script>
</body>
</html>