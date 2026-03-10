<?php
require_once __DIR__ . '/../controllers/caud.php';

$mes_sel  = isset($_REQUEST['mes'])  && $_REQUEST['mes']  !== '' ? (int)$_REQUEST['mes']  : (int)date('n');
$anio_sel = isset($_REQUEST['anio']) && $_REQUEST['anio'] !== '' ? (int)$_REQUEST['anio'] : (int)date('Y');

$meses_nombres = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
                  7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];

$datLogins = $datMovs = $datCRUD = $datTimeline = $ipsSosp = $rankUsuarios = [];
$statsGlobal = $distEventos = $tendencia = $actPorDia = $resumenMes = $datReporte = [];

if ($idemp_sesion) {
    $datLogins    = $maud->getLogins($idemp_sesion);
    $datMovs      = $maud->getMovimientos($idemp_sesion);
    $datCRUD      = $maud->getEventosCRUD($idemp_sesion);
    $datTimeline  = $maud->getTimeline($idemp_sesion, 20);
    $ipsSosp      = $maud->getIPsSospechosas($idemp_sesion, 3, 60);
    $rankUsuarios = $maud->getResumenPorUsuario($idemp_sesion);
    $statsGlobal  = $maud->getEstadisticasGlobales($idemp_sesion);
    $distEventos  = $maud->getDistribucionEventos($idemp_sesion, $mes_sel, $anio_sel);
    $tendencia    = $maud->getTendenciaLogins($idemp_sesion, 7);
    $actPorDia    = $maud->getActividadPorDia($idemp_sesion, $mes_sel, $anio_sel);
    $resumenMes   = $maud->getResumenMensual($idemp_sesion, $mes_sel, $anio_sel);
    $datReporte   = $maud->getReporteMensual($idemp_sesion, $mes_sel, $anio_sel);
}

// Stats globales
$sg_total     = $statsGlobal['total_eventos']    ?? 0;
$sg_loginok   = $statsGlobal['logins_exitosos']  ?? 0;
$sg_loginfail = $statsGlobal['logins_fallidos']  ?? 0;
$sg_hoy       = $statsGlobal['eventos_hoy']      ?? 0;
$sg_usuarios  = $statsGlobal['usuarios_distintos']?? 0;

// Preparar datos para gráficos
$dias_labels = $dias_total = $dias_loginok = $dias_fail = $dias_ops = [];
foreach ($actPorDia as $d) {
    $dias_labels[] = (int)$d['dia'];
    $dias_total[]  = (int)$d['total'];
    $dias_loginok[]= (int)$d['logins_ok'];
    $dias_fail[]   = (int)$d['logins_fail'];
    $dias_ops[]    = (int)$d['operaciones'];
}

$tend_labels = $tend_ok = $tend_fail = [];
foreach ($tendencia as $t) {
    $tend_labels[] = date('d/m', strtotime($t['dia']));
    $tend_ok[]     = (int)$t['exitosos'];
    $tend_fail[]   = (int)$t['fallidos'];
}

$dona = [
    (int)($distEventos['login_ok']   ?? 0),
    (int)($distEventos['login_fail'] ?? 0),
    (int)($distEventos['logouts']    ?? 0),
    (int)($distEventos['creados']    ?? 0),
    (int)($distEventos['editados']   ?? 0),
    (int)($distEventos['eliminados'] ?? 0),
    (int)($distEventos['movimientos']?? 0),
];

// Helpers
function tiempoRelativo($fecha) {
    $diff = time() - strtotime($fecha);
    if ($diff < 60)   return "hace {$diff}s";
    if ($diff < 3600) return "hace " . floor($diff/60) . "m";
    if ($diff < 86400)return "hace " . floor($diff/3600) . "h";
    return date('d/m/Y', strtotime($fecha));
}
function iconAccion($accion, $exitoso = 1) {
    $map = [1=>'fa-plus-circle text-success',2=>'fa-edit text-warning',3=>'fa-trash text-danger',
            4=>($exitoso?'fa-sign-in-alt text-info':'fa-sign-in-alt text-danger'),
            5=>'fa-exchange-alt text-primary',6=>'fa-sign-out-alt text-secondary'];
    return $map[$accion] ?? 'fa-circle text-muted';
}
function labelAccion($accion, $exitoso = 1) {
    if ($accion == 4) return $exitoso ? 'Login exitoso' : 'Login fallido';
    $map = [1=>'Creó registro',2=>'Editó registro',3=>'Eliminó registro',5=>'Movimiento',6=>'Cierre de sesión'];
    return $map[$accion] ?? 'Acción';
}
function navegadorCorto($ua) {
    if (!$ua) return 'Desconocido';
    if (strpos($ua,'Chrome')!==false && strpos($ua,'Edg')===false) return '<i class="fab fa-chrome"></i> Chrome';
    if (strpos($ua,'Firefox')!==false) return '<i class="fab fa-firefox"></i> Firefox';
    if (strpos($ua,'Edg')!==false)    return '<i class="fab fa-edge"></i> Edge';
    if (strpos($ua,'Safari')!==false) return '<i class="fab fa-safari"></i> Safari';
    return '<i class="fa fa-globe"></i> ' . substr($ua, 0, 30);
}
?>
<style>
:root {
    --aud-primary: #4f46e5;
    --aud-success: #10b981;
    --aud-danger:  #ef4444;
    --aud-warning: #f59e0b;
    --aud-info:    #06b6d4;
    --aud-dark:    #1e293b;
    --aud-card-bg: #ffffff;
    --aud-shadow:  0 4px 20px rgba(0,0,0,.08);
}
.aud-wrap { font-family: 'Segoe UI', sans-serif; }
/* ── Header ── */
.aud-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:10px; }
.aud-header h2 { margin:0; font-size:1.6rem; font-weight:700; color:var(--aud-dark); }
.aud-header h2 span { color:var(--aud-primary); }
.aud-badge-live { background:var(--aud-danger); color:#fff; border-radius:50px; padding:2px 10px; font-size:.72rem; font-weight:700; animation:pulse 2s infinite; }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.6} }
/* ── Stat cards ── */
.aud-stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:16px; margin-bottom:24px; }
.aud-stat { background:var(--aud-card-bg); border-radius:14px; padding:20px 18px; box-shadow:var(--aud-shadow); border-top:4px solid #e5e7eb; position:relative; overflow:hidden; transition:transform .2s; }
.aud-stat:hover { transform:translateY(-3px); }
.aud-stat.c-primary { border-top-color:var(--aud-primary); }
.aud-stat.c-success { border-top-color:var(--aud-success); }
.aud-stat.c-danger  { border-top-color:var(--aud-danger);  }
.aud-stat.c-warning { border-top-color:var(--aud-warning); }
.aud-stat.c-info    { border-top-color:var(--aud-info);    }
.aud-stat .s-icon { position:absolute; right:14px; top:14px; font-size:2rem; opacity:.12; }
.aud-stat .s-num  { font-size:2.2rem; font-weight:800; line-height:1; }
.aud-stat .s-lbl  { font-size:.73rem; color:#6b7280; text-transform:uppercase; letter-spacing:.05em; margin-top:4px; }
/* ── Alertas de seguridad ── */
.aud-alert-panel { background:#fff1f2; border:1.5px solid #fecdd3; border-radius:14px; padding:18px 20px; margin-bottom:24px; }
.aud-alert-panel h5 { color:#be123c; margin:0 0 14px; font-weight:700; }
.ip-badge { display:inline-flex; align-items:center; gap:6px; background:#fee2e2; border:1px solid #fca5a5; border-radius:8px; padding:6px 14px; margin:4px; font-size:.82rem; font-weight:600; color:#991b1b; }
/* ── Gráficos ── */
.aud-charts { display:grid; grid-template-columns:2fr 1fr; gap:20px; margin-bottom:24px; }
@media(max-width:900px){ .aud-charts { grid-template-columns:1fr; } }
.aud-chart-card { background:var(--aud-card-bg); border-radius:14px; padding:20px; box-shadow:var(--aud-shadow); }
.aud-chart-card h6 { font-weight:700; color:var(--aud-dark); margin-bottom:14px; font-size:.9rem; text-transform:uppercase; letter-spacing:.05em; }
/* ── Timeline ── */
.aud-timeline { background:var(--aud-card-bg); border-radius:14px; padding:20px; box-shadow:var(--aud-shadow); margin-bottom:24px; }
.aud-timeline h5 { font-weight:700; color:var(--aud-dark); margin-bottom:16px; }
.tl-item { display:flex; gap:14px; padding:10px 0; border-bottom:1px solid #f1f5f9; align-items:flex-start; }
.tl-item:last-child { border-bottom:none; }
.tl-icon { width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:.9rem; }
.tl-icon.bg-ok     { background:#d1fae5; color:#065f46; }
.tl-icon.bg-fail   { background:#fee2e2; color:#991b1b; }
.tl-icon.bg-op     { background:#ede9fe; color:#4c1d95; }
.tl-icon.bg-logout { background:#f1f5f9; color:#475569; }
.tl-icon.bg-mov    { background:#dbeafe; color:#1e40af; }
.tl-body { flex:1; }
.tl-who  { font-weight:600; font-size:.88rem; color:var(--aud-dark); }
.tl-what { font-size:.8rem; color:#6b7280; }
.tl-time { font-size:.72rem; color:#9ca3af; white-space:nowrap; padding-top:2px; }
/* ── Tablas ── */
.aud-table-wrap { background:var(--aud-card-bg); border-radius:14px; padding:22px; box-shadow:var(--aud-shadow); margin-bottom:24px; }
.aud-table-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; padding-bottom:12px; border-bottom:2px solid #f1f5f9; flex-wrap:wrap; gap:8px; }
.aud-table-head h5 { margin:0; font-size:1rem; font-weight:700; color:var(--aud-dark); }
.aud-table-head .actions { display:flex; gap:8px; flex-wrap:wrap; }
/* ── Tabs ── */
.aud-tabs { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:20px; border-bottom:2px solid #e5e7eb; padding-bottom:0; }
.aud-tab { background:none; border:none; padding:10px 18px; font-size:.85rem; font-weight:600; color:#6b7280; cursor:pointer; border-bottom:3px solid transparent; margin-bottom:-2px; transition:all .2s; }
.aud-tab.active, .aud-tab:hover { color:var(--aud-primary); border-bottom-color:var(--aud-primary); }
.aud-panel { display:none; }
.aud-panel.active { display:block; }
/* ── Diff visual ── */
.diff-old { background:#fff1f2; color:#9b1c1c; padding:3px 8px; border-radius:5px; font-size:.78rem; font-family:monospace; display:block; margin-bottom:3px; }
.diff-new { background:#f0fdf4; color:#14532d; padding:3px 8px; border-radius:5px; font-size:.78rem; font-family:monospace; display:block; }
/* ── Ranking ── */
.rank-row { display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid #f1f5f9; }
.rank-row:last-child { border-bottom:none; }
.rank-num { width:28px; height:28px; border-radius:50%; background:var(--aud-primary); color:#fff; font-size:.75rem; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.rank-num.gold   { background:#f59e0b; }
.rank-num.silver { background:#9ca3af; }
.rank-num.bronze { background:#b45309; }
.rank-info { flex:1; }
.rank-name { font-weight:600; font-size:.88rem; color:var(--aud-dark); }
.rank-sub  { font-size:.75rem; color:#6b7280; }
.rank-bar-wrap { width:120px; }
.rank-bar { height:6px; background:#e5e7eb; border-radius:4px; overflow:hidden; }
.rank-bar-fill { height:100%; background:var(--aud-primary); border-radius:4px; }
/* ── Navegador ── */
.nav-short { font-size:.8rem; color:#4b5563; }
</style>

<div class="aud-wrap conte">

<!-- ══ HEADER ══════════════════════════════════════════════════════════════ -->
<div class="aud-header">
    <h2><i class="fa fa-shield-alt"></i> Auditoría <span>del Sistema</span>
        <?php if(count($ipsSosp) > 0): ?>
            <span class="aud-badge-live ms-2"><i class="fa fa-exclamation-triangle"></i> <?= count($ipsSosp) ?> ALERTA<?= count($ipsSosp)>1?'S':'' ?></span>
        <?php endif; ?>
        <span id="badge-nuevos" class="d-none ms-2" style="background:#6366f1;color:#fff;border-radius:50px;padding:2px 10px;font-size:.72rem;font-weight:700;"></span>
    </h2>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <small class="text-muted" style="line-height:2.2;font-size:.77rem;"><i class="fa fa-clock"></i> <?= date('d/m/Y H:i') ?></small>
        <button class="btn btn-outline-secondary btn-sm" onclick="location.reload()"><i class="fa fa-sync-alt"></i> Actualizar</button>
    </div>
</div>

<!-- ══ STAT CARDS ══════════════════════════════════════════════════════════ -->
<div class="aud-stats">
    <div class="aud-stat c-primary">
        <div class="s-icon"><i class="fa fa-database"></i></div>
        <div class="s-num"><?= number_format($sg_total) ?></div>
        <div class="s-lbl">Total Eventos</div>
    </div>
    <div class="aud-stat c-success">
        <div class="s-icon"><i class="fa fa-check-circle"></i></div>
        <div class="s-num" style="color:var(--aud-success)"><?= $sg_loginok ?></div>
        <div class="s-lbl">Logins Exitosos</div>
    </div>
    <div class="aud-stat c-danger">
        <div class="s-icon"><i class="fa fa-times-circle"></i></div>
        <div class="s-num" style="color:var(--aud-danger)"><?= $sg_loginfail ?></div>
        <div class="s-lbl">Intentos Fallidos</div>
    </div>
    <div class="aud-stat c-info">
        <div class="s-icon"><i class="fa fa-users"></i></div>
        <div class="s-num" style="color:var(--aud-info)"><?= $sg_usuarios ?></div>
        <div class="s-lbl">Usuarios Distintos</div>
    </div>
    <div class="aud-stat c-warning">
        <div class="s-icon"><i class="fa fa-calendar-day"></i></div>
        <div class="s-num" style="color:var(--aud-warning)"><?= $sg_hoy ?></div>
        <div class="s-lbl">Eventos Hoy</div>
    </div>
</div>

<!-- ══ ALERTAS DE SEGURIDAD ════════════════════════════════════════════════ -->
<?php if (count($ipsSosp) > 0): ?>
<div class="aud-alert-panel">
    <h5><i class="fa fa-exclamation-triangle"></i> Alertas de Seguridad — IPs Sospechosas (última hora)</h5>
    <p style="font-size:.82rem;color:#be123c;margin-bottom:10px;">
        Las siguientes IPs han tenido <strong>3 o más intentos fallidos</strong> de inicio de sesión en la última hora:
    </p>
    <?php foreach ($ipsSosp as $ip_s): ?>
    <span class="ip-badge">
        <i class="fa fa-ban"></i>
        <code><?= htmlspecialchars($ip_s['ip']) ?></code>
        &mdash; <strong><?= $ip_s['intentos'] ?> intentos</strong>
        &mdash; último: <?= date('H:i:s', strtotime($ip_s['ultimo_intento'])) ?>
        &mdash; <small><?= htmlspecialchars($ip_s['emails_usados']) ?></small>
    </span>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ══ GRÁFICOS ════════════════════════════════════════════════════════════ -->
<div class="aud-charts">
    <div class="aud-chart-card">
        <h6><i class="fa fa-chart-bar text-primary"></i> Actividad por Día — <?= $meses_nombres[$mes_sel] ?> <?= $anio_sel ?></h6>
        <canvas id="chartBarDias" height="120"></canvas>
    </div>
    <div class="aud-chart-card">
        <h6><i class="fa fa-chart-pie text-info"></i> Distribución de Eventos</h6>
        <canvas id="chartDona" height="180"></canvas>
    </div>
</div>
<div class="aud-chart-card mb-4">
    <h6><i class="fa fa-chart-line text-success"></i> Tendencia de Logins — Últimos 7 días</h6>
    <canvas id="chartTendencia" height="80"></canvas>
</div>

<!-- ══ TIMELINE ═════════════════════════════════════════════════════════════ -->
<div class="aud-timeline">
    <h5><i class="fa fa-history text-primary"></i> Actividad Reciente</h5>
    <?php if (count($datTimeline) === 0): ?>
        <p class="text-muted text-center mb-0"><i class="fa fa-inbox"></i> Sin eventos registrados.</p>
    <?php else: ?>
    <?php foreach ($datTimeline as $tl):
        $a = (int)$tl['accion'];
        $e = (int)($tl['exitoso'] ?? 1);
        $bgMap = [4=>($e?'bg-ok':'bg-fail'), 6=>'bg-logout', 1=>'bg-op', 2=>'bg-op', 3=>'bg-fail', 5=>'bg-mov'];
        $bg = $bgMap[$a] ?? 'bg-op';
    ?>
    <div class="tl-item">
        <div class="tl-icon <?= $bg ?>">
            <i class="fa <?= iconAccion($a, $e) ?>"></i>
        </div>
        <div class="tl-body">
            <div class="tl-who"><?= htmlspecialchars(trim(($tl['nomusu']??'').' '.($tl['apeusu']??'')) ?: ($tl['email']??'Desconocido')) ?></div>
            <div class="tl-what"><?= labelAccion($a, $e) ?><?= $tl['tabla'] && $tl['tabla']!='login' ? ' en <code>'.htmlspecialchars($tl['tabla']).'</code>' : '' ?></div>
        </div>
        <div class="tl-time"><?= tiempoRelativo($tl['fecha']) ?></div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ══ TABS PRINCIPALES ═════════════════════════════════════════════════════ -->
<div class="aud-table-wrap">
    <div class="aud-tabs">
        <button class="aud-tab active" onclick="showTab('tab-sesiones',this)"><i class="fa fa-sign-in-alt"></i> Sesiones <span class="badge bg-secondary ms-1"><?= count($datLogins) ?></span></button>
        <button class="aud-tab" onclick="showTab('tab-crud',this)"><i class="fa fa-edit"></i> Cambios CRUD <span class="badge bg-secondary ms-1"><?= count($datCRUD) ?></span></button>
        <button class="aud-tab" onclick="showTab('tab-movs',this)"><i class="fa fa-boxes"></i> Movimientos <span class="badge bg-secondary ms-1"><?= count($datMovs) ?></span></button>
        <button class="aud-tab" onclick="showTab('tab-ranking',this)"><i class="fa fa-trophy"></i> Ranking Usuarios</button>
        <button class="aud-tab" onclick="showTab('tab-reporte',this)"><i class="fa fa-calendar-alt"></i> Reporte Mensual</button>
    </div>

    <!-- TAB: Sesiones -->
    <div id="tab-sesiones" class="aud-panel active">
        <div class="aud-table-head">
            <h5><i class="fa fa-sign-in-alt text-info"></i> Historial de Sesiones</h5>
            <div class="actions">
                <button class="btn btn-success btn-sm" id="btnExcelLogins"><i class="fa fa-file-excel"></i> Excel</button>
                <button class="btn btn-outline-danger btn-sm" onclick="if(confirm('¿Vaciar historial?')) window.location='controllers/caud.php?ope=clear_logins'"><i class="fa fa-trash-alt"></i> Vaciar</button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-sm" id="tableLogins">
                <thead class="table-dark"><tr>
                    <th>Fecha y Hora</th><th>Usuario</th><th>Tipo</th><th>Email</th><th>Estado</th><th>IP</th><th>Navegador</th>
                </tr></thead>
                <tbody>
                <?php foreach ($datLogins as $l): ?>
                <tr>
                    <td><i class="fa fa-clock text-muted"></i> <?= date('d/m/Y H:i:s', strtotime($l['fecha'])) ?></td>
                    <td><i class="fa fa-user text-primary"></i> <?= htmlspecialchars(trim(($l['nomusu']??'').' '.($l['apeusu']??'')) ?: 'Desconocido') ?></td>
                    <td><?php if($l['accion']==6): ?>
                        <span class="badge bg-secondary"><i class="fa fa-sign-out-alt"></i> Logout</span>
                    <?php else: ?>
                        <span class="badge bg-info text-dark"><i class="fa fa-sign-in-alt"></i> Login</span>
                    <?php endif; ?></td>
                    <td><small><?= htmlspecialchars($l['email']??'') ?></small></td>
                    <td><?php if($l['accion']==6): ?>
                        <span class="badge bg-secondary"><i class="fa fa-door-open"></i> Cerrado</span>
                    <?php elseif(!empty($l['exitoso'])): ?>
                        <span class="badge bg-success"><i class="fa fa-check"></i> Exitoso</span>
                    <?php else: ?>
                        <span class="badge bg-danger"><i class="fa fa-times"></i> Fallido</span>
                    <?php endif; ?></td>
                    <td><code style="font-size:.78rem;background:#f4f4f4;padding:2px 6px;border-radius:4px;"><?= htmlspecialchars($l['ip']??'') ?></code></td>
                    <td class="nav-short"><?= navegadorCorto($l['navegador']??'') ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB: Cambios CRUD -->
    <div id="tab-crud" class="aud-panel">
        <div class="aud-table-head">
            <h5><i class="fa fa-code-branch text-warning"></i> Registro de Cambios — Crear / Editar / Eliminar</h5>
            <div class="actions"><button class="btn btn-success btn-sm" id="btnExcelCRUD"><i class="fa fa-file-excel"></i> Excel</button></div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-sm" id="tableCRUD">
                <thead class="table-warning"><tr>
                    <th>Fecha</th><th>Usuario</th><th>Acción</th><th>Tabla</th><th>ID Reg.</th><th>Antes → Después</th><th>IP</th>
                </tr></thead>
                <tbody>
                <?php foreach ($datCRUD as $c):
                    $ant = json_decode($c['datos_ant']??'', true);
                    $nue = json_decode($c['datos_nue']??'', true);
                    $acc_map = [1=>['Creado','success','fa-plus-circle'],2=>['Editado','warning','fa-edit'],3=>['Eliminado','danger','fa-trash']];
                    $info = $acc_map[$c['accion']] ?? ['Otro','secondary','fa-circle'];
                ?>
                <tr>
                    <td><small><?= date('d/m/Y H:i', strtotime($c['fecha'])) ?></small></td>
                    <td><?= htmlspecialchars(trim(($c['nomusu']??'').' '.($c['apeusu']??''))) ?></td>
                    <td><span class="badge bg-<?= $info[1] ?>"><i class="fa <?= $info[2] ?>"></i> <?= $info[0] ?></span></td>
                    <td><code><?= htmlspecialchars(ucfirst($c['tabla']??'')) ?></code></td>
                    <td><small class="text-muted">#<?= htmlspecialchars($c['idreg']??'') ?></small></td>
                    <td style="max-width:340px;">
                        <?php
                        // Función helper para limpiar de tecnicismos
                        $limpiarKey = function($k) {
                            $k = preg_replace('/^id.*/', '', $k); // quita 'id' del inicio si quieres, o podemos usar un mapa
                            $mapa = [
                                'nomubi'=>'Nombre', 'codubi'=>'Código', 'desubi'=>'Descripción',
                                'nomusu'=>'Nombre', 'apeusu'=>'Apellido', 'emausu'=>'Email',
                                'nomemp'=>'Empresa', 'nit'=>'NIT', 'dir'=>'Dirección',
                                'nomprod'=>'Producto', 'codprod'=>'Cod. Prod', 'cant'=>'Cantidad',
                                'valmov'=>'Valor', 'tipmov'=>'Tipo Mov.', 'obs'=>'Observación',
                                'anio'=>'Año', 'fec'=>'Fecha'
                            ];
                            return isset($mapa[$k]) ? $mapa[$k] : ucfirst(preg_replace('/[^a-zA-Z0-9]/', ' ', $k));
                        };
                        $ignorar = ['ope','pg','pasusu','token'];

                        if ($ant && $nue && $c['accion']==2) {
                            foreach ($nue as $k => $v) {
                                if (in_array($k, $ignorar) || str_starts_with($k, 'id')) continue;
                                $old = $ant[$k] ?? null;
                                if ((string)$old !== (string)$v) {
                                    echo '<div style="margin-bottom:4px;"><small class="text-muted"><strong>'.htmlspecialchars($limpiarKey($k)).':</strong></small>';
                                    echo '<span class="diff-old"><i class="fa fa-minus"></i> '.htmlspecialchars((string)$old).'</span>';
                                    echo '<span class="diff-new"><i class="fa fa-plus"></i> '.htmlspecialchars((string)$v).'</span></div>';
                                }
                            }
                        } elseif ($nue) {
                            $preview = [];
                            foreach (array_slice($nue, 0, 8) as $k => $v) {
                                if (in_array($k, $ignorar) || str_starts_with($k, 'id') || $v==='') continue;
                                $preview[] = '<strong>'.htmlspecialchars($limpiarKey($k)).':</strong> '.htmlspecialchars((string)$v);
                                if(count($preview) == 3) break; // Mostramos max 3
                            }
                            echo '<small>'.implode(' &bull; ', $preview).'</small>';
                        } elseif ($ant) {
                            $preview = [];
                            foreach (array_slice($ant, 0, 8) as $k => $v) {
                                if (in_array($k, $ignorar) || str_starts_with($k, 'id') || $v==='') continue;
                                $preview[] = '<strong>'.htmlspecialchars($limpiarKey($k)).':</strong> '.htmlspecialchars((string)$v);
                                if(count($preview) == 3) break;
                            }
                            echo '<small class="text-danger">'.implode(' &bull; ', $preview).'</small>';
                        } else { echo '<small class="text-muted">—</small>'; }
                        ?>
                    </td>
                    <td><code style="font-size:.75rem;"><?= htmlspecialchars($c['ip']??'') ?></code></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB: Movimientos -->
    <div id="tab-movs" class="aud-panel">
        <div class="aud-table-head">
            <h5><i class="fa fa-boxes text-primary"></i> Movimientos de Inventario</h5>
            <div class="actions"><button class="btn btn-success btn-sm" id="btnExcelMovs"><i class="fa fa-file-excel"></i> Excel</button></div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-sm" id="tableMovs">
                <thead class="table-primary"><tr>
                    <th>Fecha</th><th>Usuario</th><th>Acción</th><th>Detalle</th><th>IP</th>
                </tr></thead>
                <tbody>
                <?php foreach ($datMovs as $mov):
                    $datos = json_decode($mov['datos_nue']??'', true);
                    $acc_map2=[1=>['Creado','success','fa-plus-circle'],2=>['Editado','warning','fa-edit'],3=>['Eliminado','danger','fa-trash'],5=>['Movim.','primary','fa-exchange-alt']];
                    $info2 = $acc_map2[$mov['accion']] ?? ['Otro','secondary','fa-circle'];
                ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($mov['fecha'])) ?></td>
                    <td><i class="fa fa-user-circle"></i> <?= htmlspecialchars($mov['nomusu'].' '.$mov['apeusu']) ?></td>
                    <td><span class="badge bg-<?= $info2[1] ?>"><i class="fa <?= $info2[2] ?>"></i> <?= $info2[0] ?></span></td>
                    <td>
                        <?php if($datos && isset($mov['tabla']) && $mov['tabla']=='movim'): ?>
                        <strong><?= isset($datos['tipmov']) && $datos['tipmov']==1 ? '↑ Entrada' : '↓ Salida' ?></strong>
                        <?php if(isset($datos['cantmov'])): ?> &bull; Cant: <strong><?= $datos['cantmov'] ?></strong><?php endif; ?>
                        <?php if(isset($datos['valmov'])): ?> &bull; $<?= number_format($datos['valmov'],2) ?><?php endif; ?>
                        <?php if(!empty($datos['docref'])): ?> <small class="text-muted"><?= htmlspecialchars($datos['docref']) ?></small><?php endif; ?>
                        <?php elseif(isset($mov['tabla'])): ?>
                            <code><?= htmlspecialchars(ucfirst($mov['tabla'])) ?></code>
                        <?php endif; ?>
                    </td>
                    <td><code style="font-size:.78rem;"><?= htmlspecialchars($mov['ip']??'') ?></code></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB: Ranking Usuarios -->
    <div id="tab-ranking" class="aud-panel">
        <h5 class="mb-3"><i class="fa fa-trophy text-warning"></i> Ranking de Actividad por Usuario</h5>
        <?php if (empty($rankUsuarios)): ?>
            <p class="text-muted text-center"><i class="fa fa-inbox"></i> Sin datos.</p>
        <?php else:
            $maxOps = max(array_column($rankUsuarios, 'total_eventos'));
            foreach ($rankUsuarios as $i => $r):
                $n = $i + 1;
                $pct = $maxOps > 0 ? round(($r['total_eventos'] / $maxOps) * 100) : 0;
                $medal = $n==1?'gold':($n==2?'silver':($n==3?'bronze':''));
        ?>
        <div class="rank-row">
            <div class="rank-num <?= $medal ?>"><?= $n ?></div>
            <div class="rank-info">
                <div class="rank-name"><?= htmlspecialchars(trim(($r['nomusu']??'').' '.($r['apeusu']??''))) ?></div>
                <div class="rank-sub">
                    <i class="fa fa-check-circle text-success"></i> <?= $r['logins_ok'] ?> ok &nbsp;
                    <i class="fa fa-times-circle text-danger"></i> <?= $r['logins_fail'] ?> fail &nbsp;
                    <i class="fa fa-cog text-primary"></i> <?= $r['operaciones'] ?> ops &nbsp;&nbsp;
                    <i class="fa fa-clock text-muted"></i> <?= $r['ultima_actividad'] ? tiempoRelativo($r['ultima_actividad']) : '—' ?>
                </div>
            </div>
            <div class="rank-bar-wrap">
                <div style="font-size:.75rem;text-align:right;font-weight:700;color:#4f46e5;margin-bottom:3px;"><?= number_format($r['total_eventos']) ?></div>
                <div class="rank-bar"><div class="rank-bar-fill" style="width:<?= $pct ?>%"></div></div>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>

    <!-- TAB: Reporte Mensual -->
    <div id="tab-reporte" class="aud-panel">
        <div class="aud-table-head">
            <h5><i class="fa fa-calendar-alt text-primary"></i> Reporte Mensual</h5>
            <?php if(count($datReporte)>0): ?>
            <div class="actions"><button class="btn btn-success btn-sm" id="btnExcelReporte"><i class="fa fa-file-excel"></i> Excel <?= $meses_nombres[$mes_sel].' '.$anio_sel ?></button></div>
            <?php endif; ?>
        </div>
        <!-- Selector mes/año -->
        <form method="GET" class="d-flex flex-wrap gap-2 align-items-end mb-3">
            <input type="hidden" name="pg" value="1006">
            <div>
                <label class="form-label form-label-sm fw-bold mb-1">Mes</label>
                <select name="mes" class="form-select form-select-sm" style="min-width:130px;">
                    <?php foreach($meses_nombres as $num=>$nom): ?>
                    <option value="<?= $num ?>" <?= $num==$mes_sel?'selected':'' ?>><?= $nom ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label form-label-sm fw-bold mb-1">Año</label>
                <select name="anio" class="form-select form-select-sm" style="min-width:100px;">
                    <?php for($y=(int)date('Y');$y>=(int)date('Y')-5;$y--): ?>
                    <option value="<?= $y ?>" <?= $y==$anio_sel?'selected':'' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i> Filtrar</button>
        </form>
        <!-- Stats del mes -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:10px;margin-bottom:16px;">
            <?php $rm_cards=[['total_eventos','Total','primary'],['logins_exitosos','Login OK','success'],['logins_fallidos','Fallidos','danger'],['logouts','Logouts','secondary'],['movimientos','Operaciones','info']]; ?>
            <?php foreach($rm_cards as [$k,$lbl,$c]): ?>
            <div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:12px;text-align:center;">
                <div style="font-size:1.5rem;font-weight:800;color:var(--aud-<?= $c=='secondary'?'dark':$c ?>)"><?= $resumenMes[$k]??0 ?></div>
                <div style="font-size:.72rem;color:#6b7280;text-transform:uppercase;"><?= $lbl ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if(count($datReporte)>0): ?>
        <div class="table-responsive">
            <table class="table table-sm table-hover" id="tableReporte">
                <thead class="table-dark"><tr>
                    <th>Fecha</th><th>Usuario</th><th>Email</th><th>Evento</th><th>Estado</th><th>Tabla</th><th>IP</th>
                </tr></thead>
                <tbody>
                <?php foreach($datReporte as $r):
                    $ev_map=['Login'=>'bg-info text-dark','Logout'=>'bg-secondary','Creado'=>'bg-success','Editado'=>'bg-warning text-dark','Eliminado'=>'bg-danger','Movimiento'=>'bg-primary'];
                    $ev_icon=['Login'=>'fa-sign-in-alt','Logout'=>'fa-sign-out-alt','Creado'=>'fa-plus-circle','Editado'=>'fa-edit','Eliminado'=>'fa-trash','Movimiento'=>'fa-boxes'];
                    $ev=$r['tipo_evento']??'Otro';
                    $cls=$ev_map[$ev]??'bg-light text-dark';
                    $icon=$ev_icon[$ev]??'fa-circle';
                ?>
                <tr>
                    <td><i class="fa fa-clock text-muted"></i> <?= date('d/m/Y H:i',strtotime($r['fecha'])) ?></td>
                    <td><?= htmlspecialchars(trim(($r['nomusu']??'').' '.($r['apeusu']??''))?:'Sistema') ?></td>
                    <td><small><?= htmlspecialchars($r['emausu']??$r['email']??'') ?></small></td>
                    <td><span class="badge <?= $cls ?>"><i class="fa <?= $icon ?>"></i> <?= htmlspecialchars($ev) ?></span></td>
                    <td><small class="text-muted"><?= htmlspecialchars($r['estado_sesion']??'') ?></small></td>
                    <td><?php if($r['tabla']): ?><code class="text-primary"><?= htmlspecialchars(ucfirst($r['tabla'])) ?></code><?php if($r['idreg']): ?><small class="text-muted"> #<?= htmlspecialchars($r['idreg']) ?></small><?php endif; ?><?php endif; ?></td>
                    <td><code style="font-size:.75rem;"><?= htmlspecialchars($r['ip']??'') ?></code></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-light text-center"><i class="fa fa-info-circle text-muted"></i> Sin eventos para <strong><?= $meses_nombres[$mes_sel].' '.$anio_sel ?></strong>.</div>
        <?php endif; ?>
    </div>
</div><!-- /aud-table-wrap -->

</div><!-- /aud-wrap -->

<!-- ─── Scripts ──────────────────────────────────────────────────────────── -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js"></script>
<script>
// ── Tabs ──
function showTab(id, btn) {
    document.querySelectorAll('.aud-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.aud-tab').forEach(b => b.classList.remove('active'));
    document.getElementById(id).classList.add('active');
    btn.classList.add('active');
}

$(document).ready(function(){
    const esLang = {
        emptyTable:"Sin datos",info:"_START_-_END_ de _TOTAL_",infoEmpty:"0",
        infoFiltered:"(filtrado de _MAX_)",lengthMenu:"Mostrar _MENU_",search:"Buscar:",
        zeroRecords:"Sin resultados",paginate:{first:"«",last:"»",next:"›",previous:"‹"}
    };
    const dtOpts = { language:esLang, order:[[0,'desc']], pageLength:10, lengthMenu:[[10,25,50,-1],[10,25,50,"Todos"]], responsive:true };
    if($('#tableLogins').length)  $('#tableLogins').DataTable(dtOpts);
    if($('#tableCRUD').length)    $('#tableCRUD').DataTable(dtOpts);
    if($('#tableMovs').length)    $('#tableMovs').DataTable(dtOpts);
    if($('#tableReporte').length) $('#tableReporte').DataTable({...dtOpts, pageLength:15});

    // ── Chart: Actividad por Día ──
    const diasLabels = <?= json_encode($dias_labels) ?>;
    const diasOk   = <?= json_encode($dias_loginok) ?>;
    const diasFail = <?= json_encode($dias_fail) ?>;
    const diasOps  = <?= json_encode($dias_ops) ?>;
    if(document.getElementById('chartBarDias') && diasLabels.length) {
        new Chart(document.getElementById('chartBarDias'), {
            type:'bar',
            data:{ labels:diasLabels, datasets:[
                {label:'Login OK', data:diasOk,  backgroundColor:'rgba(16,185,129,.7)', borderRadius:4},
                {label:'Fallidos', data:diasFail, backgroundColor:'rgba(239,68,68,.7)',  borderRadius:4},
                {label:'Ops',      data:diasOps,  backgroundColor:'rgba(79,70,229,.7)',  borderRadius:4},
            ]},
            options:{ responsive:true, plugins:{legend:{position:'bottom'}}, scales:{x:{grid:{display:false}},y:{beginAtZero:true,ticks:{precision:0}}} }
        });
    }

    // ── Chart: Dona ──
    const donaData  = <?= json_encode($dona) ?>;
    if(document.getElementById('chartDona') && donaData.some(v=>v>0)) {
        new Chart(document.getElementById('chartDona'), {
            type:'doughnut',
            data:{ labels:['Login OK','Login Fail','Logout','Creado','Editado','Eliminado','Movimiento'],
                   datasets:[{data:donaData, backgroundColor:['#10b981','#ef4444','#6b7280','#3b82f6','#f59e0b','#dc2626','#6366f1'], borderWidth:2}]},
            options:{ responsive:true, plugins:{legend:{position:'bottom',labels:{font:{size:11}}}} }
        });
    }

    // ── Chart: Tendencia ──
    const tendLabels = <?= json_encode($tend_labels) ?>;
    const tendOk     = <?= json_encode($tend_ok) ?>;
    const tendFail   = <?= json_encode($tend_fail) ?>;
    if(document.getElementById('chartTendencia') && tendLabels.length) {
        new Chart(document.getElementById('chartTendencia'), {
            type:'line',
            data:{ labels:tendLabels, datasets:[
                {label:'Exitosos', data:tendOk,   borderColor:'#10b981', backgroundColor:'rgba(16,185,129,.1)', tension:.4, fill:true, pointRadius:4},
                {label:'Fallidos', data:tendFail, borderColor:'#ef4444', backgroundColor:'rgba(239,68,68,.1)',  tension:.4, fill:true, pointRadius:4},
            ]},
            options:{ responsive:true, plugins:{legend:{position:'bottom'}}, scales:{y:{beginAtZero:true,ticks:{precision:0}},x:{grid:{display:false}}} }
        });
    }

    // ── AJAX Polling (cada 60s) ──
    let ultimaFecha = '<?= date('Y-m-d H:i:s') ?>';
    setInterval(function(){
        $.getJSON('controllers/caud.php?ope=ajax_nuevos&desde=' + encodeURIComponent(ultimaFecha), function(res){
            if(res.nuevos > 0){
                $('#badge-nuevos').text(res.nuevos + ' nuevo' + (res.nuevos>1?'s':'')).removeClass('d-none');
                ultimaFecha = res.ahora;
            }
        });
    }, 60000);

    // ── Exportar Excel ──
    function exportTableToExcel(tableId, filename, sheetName) {
        const table = document.getElementById(tableId); if(!table){return;}
        const headers=[];
        table.querySelectorAll('thead tr th').forEach(th=>{const t=th.innerText.trim();if(t)headers.push(t);});
        const dtInst = $.fn.dataTable.isDataTable('#'+tableId) ? $('#'+tableId).DataTable() : null;
        const rows=[];
        if(dtInst){ dtInst.rows({search:'applied'}).nodes().each(function(r){const c=[];r.querySelectorAll('td').forEach(td=>c.push(td.innerText.trim()));rows.push(c);}); }
        else{ table.querySelectorAll('tbody tr').forEach(tr=>{const c=[];tr.querySelectorAll('td').forEach(td=>c.push(td.innerText.trim()));rows.push(c);}); }
        const now=new Date(), fechaGen=now.toLocaleDateString('es-ES')+' '+now.toLocaleTimeString('es-ES');
        const hStyle={fill:{fgColor:{rgb:"4F46E5"}},font:{bold:true,color:{rgb:"FFFFFF"}},alignment:{horizontal:"center"}};
        const dStyle={alignment:{vertical:"center"},border:{top:{style:"thin",color:{rgb:"DDDDDD"}},bottom:{style:"thin",color:{rgb:"DDDDDD"}},left:{style:"thin",color:{rgb:"DDDDDD"}},right:{style:"thin",color:{rgb:"DDDDDD"}}}};
        const data=[];
        data.push([{v:"StockPilot — Auditoría del Sistema",s:{font:{bold:true,sz:14,color:{rgb:"4F46E5"}}}}]);
        data.push([{v:`${sheetName} | Generado: ${fechaGen} | Total: ${rows.length} registros`,s:{font:{sz:10,color:{rgb:"555555"}}}}]);
        data.push([]);
        data.push(headers.map(h=>({v:h,s:hStyle})));
        rows.forEach(r=>data.push(r.map(c=>({v:c,s:dStyle}))));
        data.push([]); data.push([{v:`StockPilot — ${fechaGen}`,s:{font:{italic:true,color:{rgb:"888888"}}}}]);
        const wb=XLSX.utils.book_new(), ws=XLSX.utils.aoa_to_sheet(data);
        ws['!cols']=headers.map((h,i)=>({wch:Math.min(Math.max(h.length+2,...rows.map(r=>String(r[i]||'').length),10),45)}));
        XLSX.utils.book_append_sheet(wb,ws,(sheetName||'Datos').substring(0,31));
        XLSX.writeFile(wb,filename+'.xlsx');
    }

    $('#btnExcelLogins').on('click',  ()=>exportTableToExcel('tableLogins', 'Historial_Sesiones', 'Historial de Sesiones'));
    $('#btnExcelCRUD').on('click',    ()=>exportTableToExcel('tableCRUD',   'Cambios_CRUD',       'Cambios CRUD'));
    $('#btnExcelMovs').on('click',    ()=>exportTableToExcel('tableMovs',   'Movimientos',        'Movimientos de Inventario'));
    $('#btnExcelReporte').on('click', ()=>exportTableToExcel('tableReporte','Reporte_<?= $meses_nombres[$mes_sel].'_'.$anio_sel ?>','Reporte <?= $meses_nombres[$mes_sel].' '.$anio_sel ?>'));
});
</script>
