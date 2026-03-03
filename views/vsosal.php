<div class="container mt-4">
    <h2 class="mb-4">📋 Registro de Salida de Inventario</h2>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            Información General de la Salida
        </div>
        <div class="card-body">
            <form id="formCabeceraSalida">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="tipoSalida">Tipo de Salida</label>
                            <select class="form-control" id="tipoSalida" name="tipoSalida" required>
                                <option value="">Seleccione...</option>
                                <option value="Venta">Venta</option>
                                <option value="Transferencia">Transferencia</option>
                                <option value="Consumo">Consumo Interno</option>
                                <option value="Ajuste">Ajuste por Pérdida</option>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label for="referenciaDoc">Referencia / Doc. Externo</label>
                            <input type="text" class="form-control" id="referenciaDoc" name="referenciaDoc" placeholder="Ej: Factura #12345">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="clienteDestino">Cliente / Destino</label>
                            <input type="text" class="form-control" id="clienteDestino" name="clienteDestino" placeholder="Busque Cliente/Destino">
                        </div>
                        <div class="form-group mb-3">
                            <label for="almacenOrigen">Almacén Origen</label>
                            <select class="form-control" id="almacenOrigen" name="almacenOrigen" required>
                                <option value="">Seleccione Almacén...</option>
                                </select>
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label for="observaciones">Observaciones</label>
                    <textarea class="form-control" id="observaciones" name="observaciones" rows="2"></textarea>
                </div>
                
                <button type="button" class="btn btn-success" id="btnGuardarCabecera">
                    <i class="fas fa-save"></i> Guardar Cabecera y Añadir Productos
                </button>
                <span class="badge bg-info text-dark ms-3">Estado: Creada</span>
            </form>
        </div>
    </div>
    <div class="card shadow-sm mb-4" id="cardDetalles" style="opacity: 0.6; pointer-events: none;">
        <div class="card-header bg-secondary text-white">
            Productos a Despachar (Líneas de Detalle)
        </div>
        <div class="card-body">
            
            <form id="formAgregarLinea" class="mb-4 border p-3 rounded">
                <h5>Añadir Producto</h5>
                <div class="row g-3">
                    <div class="col-md-5">
                        <label for="productoSeleccionado" class="form-label">Producto (SKU/Nombre)</label>
                        <input type="text" class="form-control" id="productoSeleccionado" placeholder="Busque Producto por SKU o Nombre">
                        <small class="form-text text-muted">Stock Disponible: <span id="stockDisponible">-</span></small>
                    </div>
                    <div class="col-md-3">
                        <label for="cantidadRequerida" class="form-label">Cantidad a Despachar</label>
                        <input type="number" class="form-control" id="cantidadRequerida" min="1" required>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100" id="btnAgregarProducto">
                            <i class="fas fa-plus-circle"></i> Agregar a la Lista
                        </button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Producto (SKU)</th>
                            <th>Cantidad Solicitada</th>
                            <th>Lote Asignado (FIFO)</th>
                            <th>Caducidad del Lote</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaLineasDetalle">
                        <tr>
                            <td colspan="5" class="text-center text-muted">No hay productos agregados.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
        </div>
        <div class="card-footer text-end">
            <button type="button" class="btn btn-lg btn-danger me-2">Cancelar Salida</button>
            <button type="button" class="btn btn-lg btn-success" id="btnConfirmarDespacho">
                <i class="fas fa-check-circle"></i> Confirmar y Generar Movimiento (MOVIM)
            </button>
        </div>
    </div>
</div>