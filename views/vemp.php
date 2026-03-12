<?php
require_once('controllers/cemp.php');

// Verifica el perfil actual del usuario
$perfil = $_SESSION['idper'] ?? 0; // Se maneja por número (1=SuperAdmin)

// =========================================================================
// !!! NOTA IMPORTANTE: SIMULACIÓN DE DATOS PARA LOS CUADROS Y GRÁFICAS !!!
// Se ASUME que el controlador cemp.php ha cargado estas variables.
// DEBES ASEGURARTE de que cemp.php las obtenga de la base de datos.
// =========================================================================
// Valores de ejemplo para la presentación (Reemplaza con tu lógica real de DB)
if ($perfil == 1 && !isset($totalEmpresas)) {
    // Si tu controlador no las carga, esta es una simulación temporal:
    $totalEmpresas = is_array($datAll) ? count($datAll) : 0;
    $activasEmpresas = 0;
    $inactivasEmpresas = 0;

    if ($totalEmpresas > 0 && is_array($datAll)) {
        foreach ($datAll as $dt) {
            if ($dt['act'] == 1) {
                $activasEmpresas++;
            } else {
                $inactivasEmpresas++;
            }
        }
    }
}
// =========================================================================

// 🔑 CAMBIOS NECESARIOS EN PHP: OBTENER LOS AÑOS PARA EL FILTRO
// --------------------------------------------------------------------------
// NOTA: ASUMO que el controlador ya te trae el año actual o lo que sea necesario.
// Si deseas obtener el listado real de años con datos (ej. 2020, 2021, 2022...)
// debes agregar en cemp.php la lógica para llamar a $memp->getAniosConDatos()
// y pasar la lista ($listaAnios) a esta vista.
$primerAnio = 2020; // Reemplazar con el primer año real de la DB
$anioActual = date('Y');
$listaAnios = range($anioActual, $primerAnio); // Lista de años disponibles (ej: 2025, 2024, 2023, ...)

// El año seleccionado actualmente (del controlador/URL)
// Se usa $year del controlador cemp.php, si no existe, usa el actual (o null si el controlador lo maneja)
$yearSeleccionado = isset($_REQUEST['year']) && is_numeric($_REQUEST['year']) ? (int)$_REQUEST['year'] : $anioActual;
// --------------------------------------------------------------------------
// =========================================================================


// Según el perfil, carga la vista correspondiente
if ($perfil == 1) {
?>

<style>
    /* Estilos para los Recuadros de Métricas (Inspirado en tarjetas tipo Dashboard) */
    .metric-card {
        background-color: #ffffff; /* Fondo blanco, como solicitaste */
        border: 1px solid #e9ecef; /* Borde muy sutil */
        border-radius: 0.5rem;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        text-align: center;
        margin-bottom: 1rem;
    }
    .metric-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 12px rgba(0,0,0,0.1);
    }
    .metric-icon {
        font-size: 2.5rem;
        color: #343a40; /* Icono gris oscuro para mantener la elegancia */
        margin-bottom: 0.5rem;
    }
    .metric-value {
        font-size: 2rem;
        font-weight: 700;
        color: #000;
        display: block;
    }
    .metric-title {
        font-size: 1rem;
        color: #6c757d; /* Título gris para contraste sutil */
        margin: 0;
    }
    .btn-create-empresa {
        background-color: #3b5998;
        border-color: #3b5998;
        color: #fff;
    }
    .btn-create-empresa:hover {
        background-color: #2d4373;
        border-color: #2d4373;
        color: #fff;
    }
    .chart-container {
        background-color: #ffffff;
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        height: 350px; /* Altura fija para la gráfica */
    }
    .stockpilot-modal .modal-content {
        border: 0;
        border-radius: 1rem;
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.16);
    }
    .stockpilot-modal .modal-header {
        background: linear-gradient(135deg, #3b5998, #2d4373);
        color: white;
        border-top-left-radius: 1rem;
        border-top-right-radius: 1rem;
    }
    .stockpilot-modal .modal-footer {
        border-top: 1px solid #e9ecef;
        padding: 0.8rem 1rem;
    }
    .stockpilot-modal .modal-title {
        font-weight: 700;
        letter-spacing: 0.2px;
    }
    .stockpilot-modal .modal-body {
        padding: 1rem 1rem 0.8rem 1rem;
        background: #fbfcff;
    }
    .stockpilot-modal .form-label {
        font-weight: 600;
        color: #334155;
        margin-bottom: 0.2rem;
        font-size: 0.9rem;
    }
    .stockpilot-modal .form-control,
    .stockpilot-modal .form-select {
        border-radius: 0.65rem;
        border: 1px solid #d7dfec;
        padding: 0.5rem 0.72rem;
        font-size: 0.92rem;
    }
    .stockpilot-modal .form-control:focus,
    .stockpilot-modal .form-select:focus {
        border-color: #3b5998;
        box-shadow: 0 0 0 0.2rem rgba(59, 89, 152, 0.16);
    }
    .stockpilot-modal .modal-footer .btn {
        min-width: 120px;
    }
    .stockpilot-modal .logo-preview {
        max-width: 100px;
        max-height: 100px;
        border-radius: 0.5rem;
        border: 1px solid #dbe2ef;
        margin-bottom: 6px;
    }
</style>

<div class="container-fluid px-4 py-3 module-panel module-empresas">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-dark"><i class="fa-solid fa-screwdriver-wrench me-2"></i>Panel de Administración de Empresas</h2>
        <button type="button" class="btn btn-create-empresa" data-bs-toggle="modal" data-bs-target="#empresaFormModal">
            <i class="fa-solid fa-square-plus me-1"></i> 
            <?php echo ($datOne && $datOne[0]['idemp']) ? 'Editar Empresa' : 'Registrar Nueva Empresa'; ?>
        </button>
    </div>

    <div class="row g-4 mb-5">
        
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="metric-card">
                <i class="fa-solid fa-list-ul metric-icon"></i>
                <span class="metric-value"><?= number_format($totalEmpresas ?? 0, 0, ',', '.'); ?></span>
                <p class="metric-title">Total de Empresas Registradas</p>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="metric-card">
                <i class="fa-solid fa-circle-check metric-icon text-success"></i>
                <span class="metric-value"><?= number_format($activasEmpresas ?? 0, 0, ',', '.'); ?></span>
                <p class="metric-title">Empresas Activas</p>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="metric-card">
                <i class="fa-solid fa-circle-xmark metric-icon text-danger"></i>
                <span class="metric-value"><?= number_format($inactivasEmpresas ?? 0, 0, ',', '.'); ?></span>
                <p class="metric-title">Empresas Inactivas</p>
            </div>
        </div>
    </div>

    <h4 class="mb-3 text-dark"><i class="fa-solid fa-chart-bar me-2"></i>Estadísticas Clave</h4>
    <div class="row g-4 mb-5">
        <div class="col-lg-6">
            <div class="chart-container">
                <canvas id="empresasActivasChart"></canvas>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="chart-container">
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 text-dark">Crecimiento Histórico</h5>
                    <div class="input-group input-group-sm w-auto">
                        <span class="input-group-text">Año:</span>
                        <select id="filtroAnio" class="form-select" onchange="cambiarAnio(this.value)">
                            <?php foreach ($listaAnios as $year): ?>
                                <option value="<?= $year; ?>" <?= ($year == $yearSeleccionado) ? 'selected' : ''; ?>>
                                    <?= $year; ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="" <?= ($yearSeleccionado === null || $yearSeleccionado == 0) ? 'selected' : ''; ?>>
                                Últimos 12 Meses (Default)
                            </option>
                        </select>
                    </div>
                </div>
                <canvas id="otroGrafico"></canvas>
            </div>
        </div>
    </div>

    <hr>
    
    <h4 class="mb-3 text-dark"><i class="fa-solid fa-table me-2"></i>Gestión de Empresas</h4>
    
    <div class="table-responsive">
        <table id="example" class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>NIT</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Estado</th> <th>Acciones</th>
                </tr>   
            </thead>

            <tbody>
                <?php if($datAll){foreach($datAll AS $dt){ ?>
                <tr>
                    <td><?=$dt['idemp'] ?></td>
                    <td><?=$dt['nomemp'] ?></td>
                    <td><?=$dt['nitemp'] ?></td>
                    <td><?=$dt['emaemp'] ?></td>
                    <td><?=$dt['telemp'] ?></td>
                    <td>
                        <?php 
                            // Muestra el estado actual (Activo/Inactivo)
                            echo $dt['act'] == 1 ? '<span class="badge bg-success">Activa</span>' : '<span class="badge bg-danger">Inactiva</span>'; 
                        ?>
                    </td>
                    <td style="text-align: right;">
                        
                        <?php 
                            // Lógica para el botón de Activar/Desactivar
                            $current_status = $dt['act'];
                            $new_status = $current_status == 1 ? 0 : 1; // Cambia el estado opuesto
                            $btn_class = $current_status == 1 ? 'btn-outline-danger' : 'btn-outline-success';
                            $btn_icon = $current_status == 1 ? 'fa-lock' : 'fa-unlock';
                            $btn_title = $current_status == 1 ? 'Desactivar Empresa' : 'Activar Empresa';
                        ?>
                        <a href="controllers/cstatus.php?action=empresa&id=<?= $dt['idemp']; ?>&estado=<?= $new_status; ?>" 
                            class="btn btn-sm <?= $btn_class; ?> me-2" title="<?= $btn_title; ?>">
                            <i class="fa-solid <?= $btn_icon; ?>"></i>
                        </a>
                        
                        <a href="home.php?pg=<?= $pg; ?>&idemp=<?= $dt['idemp']; ?>&ope=edi" 
                            class="btn btn-sm btn-outline-warning me-2" title="Editar">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <a href="javascript:void(0);" onclick="confirmarEliminacion(
                            'controllers/cdelete.php?action=empresa&id=<?= $dt['idemp']; ?>'
                        )" 
                            class="btn btn-sm btn-outline-danger" title="Eliminar">
                            <i class="fa-solid fa-trash-can"></i>
                        </a>
                    </td>       
                </tr>
                <?php }}?>  
            </tbody>

            <tfoot>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>NIT</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Estado</th> <th>Acciones</th>
                </tr>   
            </tfoot>
        </table>
    </div>
    
</div>

<div class="modal fade stockpilot-modal" id="empresaFormModal" tabindex="-1" aria-labelledby="empresaFormModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="empresaFormModalLabel">
            <i class="fa-solid fa-building me-2"></i>
            <?php echo ($datOne && $datOne[0]['idemp']) ? 'Editar Empresa' : 'Registrar Nueva Empresa'; ?>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <form action="home.php?pg=<?=$pg;?>" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
            <div class="row g-2">

                <div class="col-md-6">
                    <label for="nomemp" class="form-label">Nombre Empresa</label>
                    <input type="text" name="nomemp" id="nomemp" class="form-control" 
                        value="<?php if($datOne && $datOne[0]['nomemp']) echo htmlspecialchars($datOne[0]['nomemp']); ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="razemp" class="form-label">Razón Social</label>
                    <input type="text" name="razemp" id="razemp" class="form-control" 
                        value="<?php if($datOne && $datOne[0]['razemp']) echo htmlspecialchars($datOne[0]['razemp']); ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="nitemp" class="form-label">NIT</label>
                    <input type="text" name="nitemp" id="nitemp" class="form-control" 
                        value="<?php if($datOne && $datOne[0]['nitemp']) echo htmlspecialchars($datOne[0]['nitemp']); ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="diremp" class="form-label">Dirección</label>
                    <input type="text" name="diremp" id="diremp" class="form-control" 
                        value="<?php if($datOne && $datOne[0]['diremp']) echo htmlspecialchars($datOne[0]['diremp']); ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="telemp" class="form-label">Teléfono</label>
                    <input type="text" name="telemp" id="telemp" class="form-control" 
                        value="<?php if($datOne && $datOne[0]['telemp']) echo htmlspecialchars($datOne[0]['telemp']); ?>">
                </div>

                <div class="col-md-6">
                    <label for="emaemp" class="form-label">Correo electrónico</label>
                    <input type="email" name="emaemp" id="emaemp" class="form-control" 
                        value="<?php if($datOne && $datOne[0]['emaemp']) echo htmlspecialchars($datOne[0]['emaemp']); ?>">
                </div>

                <div class="col-md-6">
                    <label for="logo_file" class="form-label">Logo de la empresa</label>

                    <?php if ($datOne && $datOne[0]['idemp'] && !empty($datOne[0]['logo'])): ?>
                        <p class="mb-1 text-muted small">Logo actual:</p>
                        <img src="img/logos/<?php echo htmlspecialchars($datOne[0]['logo']); ?>" 
                             alt="Logo Empresa" 
                             class="logo-preview">
                        <br>
                    <?php endif; ?>

                    <input type="file" class="form-control" id="logo_file" name="logo_file" accept="image/*">
                    <small class="text-muted d-block mt-1">Formatos recomendados: JPG, PNG o GIF.</small>
                </div>

                <div class="col-md-6">
                    <label for="act" class="form-label">Estado</label>
                    <select name="act" id="act" class="form-select">
                        <option value="1" <?php if($datOne && $datOne[0]['act'] == 1) echo 'selected'; ?>>Activa</option>
                        <option value="0" <?php if($datOne && $datOne[0]['act'] == 0) echo 'selected'; ?>>Inactiva</option>
                    </select>

                    <input type="hidden" name="estado" id="estado" 
                        value="<?php if($datOne && $datOne[0]['estado']) echo htmlspecialchars($datOne[0]['estado']); else echo 'Activa'; ?>">
                </div>

            </div>
        </div>

        <div class="modal-footer">
            <input type="hidden" name="idemp" 
                value="<?php if($datOne && $datOne[0]['idemp']) echo htmlspecialchars($datOne[0]['idemp']); ?>">

            <input type="hidden" name="ope" 
                value="<?php echo ($datOne && $datOne[0]['idemp']) ? 'save' : 'save_reg'; ?>">

            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <input type="submit" class="btn btn-gradient" 
                value="<?php echo ($datOne && $datOne[0]['idemp']) ? 'Actualizar' : 'Guardar Empresa'; ?>">
        </div>
      </form>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    
    // ===========================================================================
    // 🔑 CAMBIO 3: FUNCIÓN JAVASCRIPT PARA FILTRAR POR AÑO
    // --------------------------------------------------------------------------
    /**
     * Recarga la página con el nuevo parámetro de año en la URL.
     * @param {string} year - El año seleccionado. Si es una cadena vacía, se usa el filtro por defecto (12 meses).
     */
    function cambiarAnio(year) {
        // Obtenemos la URL base (sin parámetros)
        let url = window.location.href.split('?')[0];
        
        // Creamos nuevos parámetros, manteniendo los existentes (como pg)
        let params = new URLSearchParams(window.location.search);
        
        // Borramos el parámetro 'year' anterior si existe
        params.delete('year');
        
        // Si se selecciona un año, lo añadimos a los parámetros
        if (year && year !== '' && !isNaN(parseInt(year))) {
            params.set('year', year);
        } else {
            // Si es cadena vacía (opción "Últimos 12 Meses"), simplemente no añadimos 'year'
            // para que el controlador use su lógica por defecto (últimos 12 meses).
        }
        
        // Reconstruimos la URL y navegamos
        // Conservamos los parámetros existentes (como 'pg') y añadimos el nuevo (o ninguno)
        window.location.href = url + '?' + params.toString();
    }
    // --------------------------------------------------------------------------
    // ===========================================================================

    // Tu Script de SweetAlert y DataTables... (Se mantiene sin cambios, solo se asegura de que el DataTables se inicialice)
    
    // ===========================================================================
    // SCRIPT DE SWEETALERT Y FUNCIÓN confirmarEliminacion (Mantener Original)
    // ===========================================================================
    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        const ope = urlParams.get('ope'); // Necesario para identificar si estamos en modo edición
        
        // El resto de tu lógica de SweetAlert (msg, message, error)

        const msg = urlParams.get('msg');
        const message = urlParams.get('message'); 
        const error = urlParams.get('error');
        let showSwal = false; 

        // 1. Manejo de mensajes de cdelete.php y cstatus.php (Prioritario)
        if (message) {
            Swal.fire({
                icon: 'success',
                title: '¡Operación exitosa!',
                text: decodeURIComponent(message),
                confirmButtonColor: '#198754',
                confirmButtonText: 'Aceptar'
            });
            showSwal = true;
        } else if (error) {
            Swal.fire({
                icon: 'error',
                title: '¡Error!',
                text: decodeURIComponent(error),
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Aceptar'
            });
            showSwal = true;
        }
        // 2. Lógica de mensajes CUD original (Solo si no hubo mensaje de cdelete/cstatus)
        else {
            if (msg === 'saved') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Guardado exitosamente!',
                    text: 'La empresa se ha registrado correctamente.',
                    confirmButtonColor: '#198754',
                    confirmButtonText: 'Aceptar'
                });
                showSwal = true;
            }

            if (msg === 'updated') {
                Swal.fire({
                    icon: 'info',
                    title: '¡Actualización exitosa!',
                    text: 'Los datos se han actualizado correctamente.',
                    confirmButtonColor: '#0d6efd',
                    confirmButtonText: 'Aceptar'
                });
                showSwal = true;
            }
            
            if (msg === 'deleted') {
                Swal.fire({
                    icon: 'warning',
                    title: '¡Eliminación exitosa!',
                    text: 'La empresa ha sido eliminada correctamente.',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Aceptar'
                });
                showSwal = true;
            }
        }
        
        // 🔑 CLAVE: Limpiar la URL después de mostrar la alerta para evitar reaparición
        if (showSwal && history.replaceState) {
            // Se usa una expresión regular más segura para limpiar múltiples parámetros
            const cleanUrl = window.location.href.replace(/(\?|&)(msg|message|error|idemp|ope)=[^&]*/g, '').replace(/^&/, '?');
            history.replaceState(null, '', cleanUrl);
        }
        
        // CLAVE: Lógica para abrir el modal automáticamente en modo edición (ope=edi)
        // Esto captura los datos del controlador al cargar la página y muestra el modal.
        if (ope === 'edi') {
            var myModal = new bootstrap.Modal(document.getElementById('empresaFormModal'), {
                keyboard: false
            });
            myModal.show();
        }

    });

    function confirmarEliminacion(url) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción eliminará la empresa y todos sus datos dependientes y NO se puede deshacer.',
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

    // ===========================================================================
    // DATATABLES PARA EMPRESAS (PERFIL 1) - Tu código original
    // ===========================================================================
    $(document).ready(function() {
        if ($.fn.DataTable.isDataTable('#example')) {
            $('#example').DataTable().destroy();
        }

        $('#example').DataTable({
            "language": {
                "decimal":        "",
                "emptyTable":     "No hay empresas registradas",
                "info":           "Mostrando _START_ a _END_ de _TOTAL_ empresas",
                "infoEmpty":      "Mostrando 0 a 0 de 0 empresas",
                "infoFiltered":   "(filtrado de _MAX_ empresas totales)",
                "infoPostFix":    "",
                "thousands":      ".",
                "lengthMenu":     "Mostrar _MENU_ empresas",
                "loadingRecords": "Cargando...",
                "processing":     "Procesando...",
                "search":         "Buscar:",
                "zeroRecords":    "No se encontraron empresas coincidentes",
                "paginate": {
                    "first":      "Primero",
                    "last":       "Último",
                    "next":       "Siguiente",
                    "previous":   "Anterior"
                },
                "aria": {
                    "sortAscending":  ": activar para ordenar la columna de forma ascendente",
                    "sortDescending": ": activar para ordenar la columna de forma descendente"
                }
            },
            "dom": '<"row mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>><"row"<"col-sm-12"tr>><"row mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            "pagingType": "full_numbers",
            "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "Todos"] ],
            "oClasses": {
                "sFilterInput": "form-control form-control-sm",
                "sLengthSelect": "form-select form-select-sm"
            },
        });
        
        $('div.dataTables_filter input').attr('placeholder', 'Buscar empresa...');
        $('div.dataTables_filter label').contents().filter(function(){
            return this.nodeType === 3; 
        }).remove();

        // ===========================================================================
        // LÓGICA DE GRÁFICAS (Requiere Chart.js)
        // ===========================================================================
        const total = <?php echo $totalEmpresas ?? 0; ?>;
        const activas = <?php echo $activasEmpresas ?? 0; ?>;
        const inactivas = <?php echo $inactivasEmpresas ?? 0; ?>;

        // Gráfico de Barras/Donut: Estado de Actividad de Empresas
        const ctx1 = document.getElementById('empresasActivasChart');
        if (ctx1) {
            new Chart(ctx1, {
                type: 'doughnut',
                data: {
                    labels: ['Activas', 'Inactivas'],
                    datasets: [{
                        label: 'Estado de Empresas',
                        data: [activas, inactivas],
                        backgroundColor: [
                            'rgba(25, 135, 84, 0.8)', // Color para Activas (Verde)
                            'rgba(220, 53, 69, 0.8)' // Color para Inactivas (Rojo)
                        ],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                }
                            }
                        },
                        title: {
                            display: true,
                            text: 'Distribución de Empresas (Activas vs. Inactivas)',
                            font: {
                                size: 16
                            }
                        }
                    }
                }
            });
        }
        
        // ... (LÓGICA ANTERIOR DEL GRÁFICO DONUT ctx1)

        // ===========================================================================
        // LÓGICA DE GRÁFICAS (Requiere Chart.js) - Crecimiento Histórico
        // ===========================================================================
        // Gráfico de Crecimiento Histórico (Línea): Usando datos REALES del controlador
        const ctx2 = document.getElementById('otroGrafico');
        if (ctx2) {
            var crecimientoHistorico = <?php echo $jsonCrecimiento ?? "{}"; ?>; 
            
            var labels = crecimientoHistorico.meses || [];
            var data = crecimientoHistorico.acumulado || [];
            
            if (labels.length > 0 && data.length > 0) {
                new Chart(ctx2, {
                    type: 'line',
                    data: {
                        labels: labels, 
                        datasets: [{
                            label: 'Crecimiento Histórico Acumulado',
                            data: data,
                            // 🔑 ESTILO EXACTO SOLICITADO
                            borderColor: 'rgba(52, 58, 64, 1)', // Línea Negra
                            backgroundColor: 'rgba(52, 58, 64, 0.2)', // Sombreado Gris Claro (20% opacidad del negro)
                            fill: true, 
                            tension: 0.3, 
                            borderWidth: 2, 
                            pointRadius: 5 
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Crecimiento Histórico Acumulado de Empresas',
                                font: { size: 16 }
                            }
                        },
                        // 🔑 EJE Y SOLO CON ENTEROS
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value, index, values) {
                                        if (Math.floor(value) === value) {
                                            return value;
                                        }
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                ctx2.parentNode.innerHTML = '<p class="text-center mt-5 text-muted">No hay datos suficientes para el gráfico de crecimiento.</p>';
            }
        }
        // ===========================================================================
    });
    // ===========================================================================
</script>
<?php
} else {
// ======== VISTA MODERNA PARA ADMIN / EMPLEADO ========

// Obtiene la empresa activa desde la sesión
$idemp = $_SESSION['idemp'] ?? null;

// ===============================================================
// PREPARACIÓN DE RUTA DE LOGO
// ===============================================================

// ===============================================================
// PREPARACIÓN DE RUTA DE LOGO (CORREGIDO)
// ===============================================================

// Nombre del archivo de logo por defecto si la empresa no tiene uno.
// Asegúrate de que 'default.png' sea el nombre correcto de tu logo de relleno.
$LOGO_POR_DEFECTO = 'logo.png'; 
$logo_empresa = $LOGO_POR_DEFECTO; 

$ruta_base_logo = "img/logos/";

if ($idemp) {
    if (!isset($memp) || !($memp instanceof Memp)) {
        require_once('models/memp.php'); 
        $memp = new Memp();
    }
    
    $memp->setIdemp($idemp);
    $empresaUsuario = $memp->getOne(); 
    $emp = $empresaUsuario[0] ?? null;

    // ELIMINADO EL CÓDIGO DE DEPURACIÓN CRÍTICA
    
    // Si la empresa existe y el campo 'logo' NO está vacío en la DB
    if ($emp && !empty($emp['logo'])) {
        $logo_empresa = htmlspecialchars($emp['logo']); 
    }
    // Si $emp['logo'] está vacío (""), se mantiene el valor por defecto $LOGO_POR_DEFECTO.
} else {
    $emp = null;
}

$ruta_logo_final = $ruta_base_logo . $logo_empresa;

// ===============================================================
// FIN PREPARACIÓN DE RUTA DE LOGO
// ===============================================================

if (!$emp) {
    ?>
    <div class="alert alert-warning text-center mt-5 p-4 rounded-4 shadow-sm">
        <i class="fas fa-exclamation-circle fa-2x mb-2"></i><br>
        No se encontró información de tu empresa.
    </div>
    <?php
} else {
    ?>
    <style>
        /* (Estilos CSS existentes) */
        .empresa-header {
            background: linear-gradient(135deg, #2c2c2c, #1a1a1a);
            color: #fff;
            padding: 3rem 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            position: relative;
        }
        .empresa-header img {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #555;
            box-shadow: 0 0 10px rgba(255,255,255,0.1);
        }
        .empresa-header h2 {
            font-weight: 700;
            margin-top: 1rem;
        }
        .empresa-body {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-top: -2rem;
        }
        .empresa-item strong {
            display: inline-block;
            width: 160px;
            color: #555;
        }
        .btn-gradient {
            background: linear-gradient(135deg, #3b5998, #2d4373);
            color: white;
            border: none;
            transition: 0.3s ease;
        }
        .btn-gradient:hover {
            background: linear-gradient(135deg, #2d4373, #24385f);
        }
        .stockpilot-modal .modal-content {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 12px 32px rgba(0,0,0,0.16);
        }
        .stockpilot-modal .modal-header {
            background: linear-gradient(135deg, #3b5998, #2d4373);
            color: white;
            border-top-left-radius: 1rem;
            border-top-right-radius: 1rem;
        }
        .stockpilot-modal .modal-footer {
            border-top: 1px solid #e9ecef;
            padding: 0.8rem 1rem;
        }
        .stockpilot-modal .modal-title {
            font-weight: 700;
            letter-spacing: 0.2px;
        }
        .stockpilot-modal .modal-body {
            padding: 1rem 1rem 0.8rem 1rem;
            background: #fbfcff;
        }
        .stockpilot-modal .form-label {
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.2rem;
            font-size: 0.9rem;
        }
        .stockpilot-modal .form-control,
        .stockpilot-modal .form-select {
            border-radius: 0.65rem;
            border: 1px solid #d7dfec;
            padding: 0.5rem 0.72rem;
            font-size: 0.92rem;
        }
        .stockpilot-modal .form-control:focus,
        .stockpilot-modal .form-select:focus {
            border-color: #3b5998;
            box-shadow: 0 0 0 0.2rem rgba(59, 89, 152, 0.16);
        }
        .stockpilot-modal .modal-footer .btn {
            min-width: 120px;
        }
        .badge-estado {
            font-size: 0.9rem;
            padding: 0.5em 0.8em;
        }
        .edit-btn-container {
            position: absolute;
            bottom: 20px;
            right: 30px;
        }
    </style>

    <div class="container-fluid px-4 py-5 module-panel module-empresas">
        <div class="empresa-header text-center position-relative">
            <div class="d-flex justify-content-center">
                <img src="<?= $ruta_logo_final; ?>" alt="Logo Empresa">
            </div>
            <h2 class="mt-3 mb-0"><?= htmlspecialchars($emp['nomemp']); ?></h2>
            <p class="lead mb-2"><?= htmlspecialchars($emp['razemp']); ?></p>

            <div class="edit-btn-container">
                <button class="btn btn-gradient btn-sm px-4 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#editarEmpresaModal">
                    <i class="fas fa-pen me-1"></i> Editar información
                </button>
            </div>
        </div>

        <div class="empresa-body mt-4">
            <h4 class="mb-4 text-dark fw-bold"><i class="fas fa-info-circle me-2"></i>Detalles de la Empresa</h4>
            <div class="row g-4">
                <div class="col-md-6 empresa-item"><strong>NIT:</strong> <?= htmlspecialchars($emp['nitemp']); ?></div>
                <div class="col-md-6 empresa-item"><strong>Dirección:</strong> <?= htmlspecialchars($emp['diremp']); ?></div>
                <div class="col-md-6 empresa-item"><strong>Teléfono:</strong> <?= htmlspecialchars($emp['telemp']); ?></div>
                <div class="col-md-6 empresa-item"><strong>Email:</strong> <?= htmlspecialchars($emp['emaemp']); ?></div>
                <div class="col-md-6 empresa-item"><strong>Estado:</strong> 
                    <?= $emp['act'] ? '<span class="text-success fw-semibold">Activa</span>' : '<span class="text-danger fw-semibold">Inactiva</span>'; ?>
                </div>
                <div class="col-md-6 empresa-item"><strong>Última actualización:</strong> 
                    <?= htmlspecialchars($emp['fec_actu']); ?>
                </div>
            </div>
        </div>
    </div>

        <div class="modal fade stockpilot-modal" id="editarEmpresaModal" tabindex="-1" aria-labelledby="editarEmpresaLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editarEmpresaLabel"><i class="fas fa-pen-to-square me-2"></i>Editar información de empresa</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>

          <form action="home.php?pg=<?= $pg; ?>" method="POST" class="needs-validation" novalidate enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="row g-2">
                
                <div class="col-md-6">
                  <label class="form-label">Nombre Empresa</label>
                  <input type="text" name="nomemp" class="form-control" value="<?= htmlspecialchars($emp['nomemp']); ?>" required>
                </div>
                
                <div class="col-md-6">
                  <label class="form-label">Razón Social</label>
                  <input type="text" name="razemp" class="form-control" value="<?= htmlspecialchars($emp['razemp']); ?>" required>
                </div>
                
                <div class="col-md-6">
                  <label class="form-label">NIT</label>
                  <input type="text" name="nitemp_view" class="form-control" value="<?= htmlspecialchars($emp['nitemp']); ?>" required readonly>
                    <small class="text-muted d-block mt-1">El NIT no puede ser modificado.</small>
                </div>
                
                <div class="col-md-6">
                  <label class="form-label">Dirección</label>
                  <input type="text" name="diremp" class="form-control" value="<?= htmlspecialchars($emp['diremp']); ?>" required>
                </div>
                
                <div class="col-md-6">
                  <label class="form-label">Teléfono</label>
                  <input type="text" name="telemp" class="form-control" value="<?= htmlspecialchars($emp['telemp']); ?>">
                </div>
                
                <div class="col-md-6">
                  <label class="form-label">Correo electrónico</label>
                  <input type="email" name="emaemp" class="form-control" value="<?= htmlspecialchars($emp['emaemp']); ?>">
                </div>
                
                <div class="col-md-12">
                    <label class="form-label">Subir Nuevo Logo (JPG, PNG, GIF, WEBP, AVIF, SVG)</label>
                    <input 
                        type="file" 
                        name="logo_file" 
                        class="form-control" 
                        accept="image/*, .webp, .avif, .svg" 
                        >
                    <small class="text-muted d-block mt-1">Logo actual: <?= htmlspecialchars($emp['logo']); ?>. Si subes uno nuevo, se reemplazará.</small>
                </div>
                
              </div>
            </div>

            <div class="modal-footer">
              <input type="hidden" name="idemp" value="<?= $emp['idemp']; ?>">
              <input type="hidden" name="ope" value="save">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-gradient"><i class="fas fa-save me-1"></i> Guardar Cambios</button>
            </div>
          </form>
        </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        const msg = urlParams.get('msg');
        const message = urlParams.get('message'); 
        const error = urlParams.get('error');
        let showSwal = false;

        // 1. Manejo de mensajes personalizados (message y error)
        if (message) {
            Swal.fire({
                icon: 'success',
                title: '¡Operación exitosa!',
                text: decodeURIComponent(message), 
                confirmButtonColor: '#198754',
                confirmButtonText: 'Aceptar'
            });
            showSwal = true;
        } else if (error) {
            Swal.fire({
                icon: 'error',
                title: '¡Error!',
                text: decodeURIComponent(error), 
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Aceptar'
            });
            showSwal = true;
        }
        // 2. Lógica de mensajes CUD original
        else {
            if (msg === 'saved') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Guardado exitosamente!',
                    text: 'La empresa se ha registrado correctamente.',
                    confirmButtonColor: '#198754',
                    confirmButtonText: 'Aceptar'
                });
                showSwal = true;
            }

            // Mensaje de actualización
            if (msg === 'updated') {
                Swal.fire({
                    icon: 'info',
                    title: '¡Actualización exitosa!',
                    text: 'Los datos se han actualizado correctamente.',
                    confirmButtonColor: '#0d6efd',
                    confirmButtonText: 'Aceptar'
                });
                showSwal = true;
            }
            
            if (msg === 'deleted') {
                Swal.fire({
                    icon: 'warning',
                    title: '¡Eliminación exitosa!',
                    text: 'La empresa ha sido eliminada correctamente.',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Aceptar'
                });
                showSwal = true;
            }
        }

        // CLAVE: Limpiar la URL después de mostrar la alerta para evitar reaparición
        if (showSwal && history.replaceState) {
            // Elimina los parámetros 'msg', 'message', o 'error' de la URL sin recargar la página
            const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + window.location.search.replace(/(\?|&)(msg|message|error)=[^&]*/g, '').replace(/^&/, '?');
            history.replaceState(null, '', newUrl);
        }
    });

    function confirmarEliminacion(url) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción eliminará la empresa y todos sus datos dependientes (productos, inventario, etc.) y NO se puede deshacer.',
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
    <?php
} // Cierre del else de if (!$emp)
}
?>