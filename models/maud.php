<?php

class MAud{
    private $idaud;
    private $idemp;
    private $idusu;
    private $tabla;
    private $accion;
    private $idreg;
    private $datos_ant;
    private $datos_nue;
    private $email;
    private $exitoso;
    private $navegador;
    private $fecha;
    private $ip;

    // ─── Getters ───────────────────────────────────────────────────────────────

    function getIdaud()    { return $this->idaud; }
    function getIdemp()    { return $this->idemp; }
    function getIdusu()    { return $this->idusu; }
    function getTabla()    { return $this->tabla; }
    function getAccion()   { return $this->accion; }
    function getIdreg()    { return $this->idreg; }
    function getDatos_ant(){ return $this->datos_ant; }
    function getDatos_nue(){ return $this->datos_nue; }
    function getFecha()    { return $this->fecha; }
    function getIp()       { return $this->ip; }
    function getEmail()    { return $this->email; }
    function getExitoso()  { return $this->exitoso; }
    function getNavegador(){ return $this->navegador; }

    // ─── Setters ───────────────────────────────────────────────────────────────

    function setIdaud($v)     { $this->idaud     = $v; }
    function setIdemp($v)     { $this->idemp     = $v; }
    function setIdusu($v)     { $this->idusu     = $v; }
    function setTabla($v)     { $this->tabla     = $v; }
    function setAccion($v)    { $this->accion    = $v; }
    function setIdreg($v)     { $this->idreg     = $v; }
    function setDatos_ant($v) { $this->datos_ant = $v; }
    function setDatos_nue($v) { $this->datos_nue = $v; }
    function setFecha($v)     { $this->fecha     = $v; }
    function setIp($v)        { $this->ip        = $v; }
    function setEmail($v)     { $this->email     = $v; }
    function setExitoso($v)   { $this->exitoso   = $v; }
    function setNavegador($v) { $this->navegador = $v; }

    // ─── CRUD básico ───────────────────────────────────────────────────────────

    public function getAll($idemp = null){
        try{
            $where = $idemp
                ? "WHERE idemp = :idemp AND (accion IS NULL OR accion NOT IN (4, 5, 6))"
                : "WHERE (accion IS NULL OR accion NOT IN (4, 5, 6))";
            $sql = "SELECT idaud, idemp, idusu, tabla, accion, idreg, datos_ant, datos_nue, fecha, ip
                    FROM auditoria $where ORDER BY fecha DESC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            if($idemp) $result->bindParam(':idemp', $idemp);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            error_log("MAud::getAll - " . $e->getMessage());
            return [];
        }
    }

    public function getOne(){
        try{
            $sql = "SELECT idaud, idusu, tabla, accion, idreg, datos_ant, datos_nue, fecha, ip
                    FROM auditoria WHERE idaud=:idaud";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idaud = $this->getIdaud();
            $result->bindParam(':idaud', $idaud);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            error_log("MAud::getOne - " . $e->getMessage());
            return [];
        }
    }

    public function save(){
        try{
            $sql = "INSERT INTO auditoria(idemp, idusu, tabla, accion, idreg, datos_ant, datos_nue, fecha, ip)
                    VALUES (:idemp, :idusu, :tabla, :accion, :idreg, :datos_ant, :datos_nue, :fecha, :ip)";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idemp     = $this->getIdemp();    $result->bindParam(':idemp',     $idemp);
            $idusu     = $this->getIdusu();    $result->bindParam(':idusu',     $idusu);
            $tabla     = $this->getTabla();    $result->bindParam(':tabla',     $tabla);
            $accion    = $this->getAccion();   $result->bindParam(':accion',    $accion);
            $idreg     = $this->getIdreg();    $result->bindParam(':idreg',     $idreg);
            $datos_ant = $this->getDatos_ant();$result->bindParam(':datos_ant', $datos_ant);
            $datos_nue = $this->getDatos_nue();$result->bindParam(':datos_nue', $datos_nue);
            $fecha     = $this->getFecha();    $result->bindParam(':fecha',     $fecha);
            $ip        = $this->getIp();       $result->bindParam(':ip',        $ip);
            $result->execute();
            return true;
        }catch(Exception $e){
            error_log("MAud::save - " . $e->getMessage());
            return false;
        }
    }

    public function edit(){
        try{
            $sql = "UPDATE auditoria SET idusu=:idusu, tabla=:tabla, accion=:accion, idreg=:idreg,
                    datos_ant=:datos_ant, datos_nue=:datos_nue, fecha=:fecha, ip=:ip
                    WHERE idaud=:idaud";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idaud     = $this->getIdaud();    $result->bindParam(':idaud',     $idaud);
            $idusu     = $this->getIdusu();    $result->bindParam(':idusu',     $idusu);
            $tabla     = $this->getTabla();    $result->bindParam(':tabla',     $tabla);
            $accion    = $this->getAccion();   $result->bindParam(':accion',    $accion);
            $idreg     = $this->getIdreg();    $result->bindParam(':idreg',     $idreg);
            $datos_ant = $this->getDatos_ant();$result->bindParam(':datos_ant', $datos_ant);
            $datos_nue = $this->getDatos_nue();$result->bindParam(':datos_nue', $datos_nue);
            $fecha     = $this->getFecha();    $result->bindParam(':fecha',     $fecha);
            $ip        = $this->getIp();       $result->bindParam(':ip',        $ip);
            $result->execute();
            return true;
        }catch(Exception $e){
            error_log("MAud::edit - " . $e->getMessage());
            return false;
        }
    }

    public function del(){
        try{
            $sql = "DELETE FROM auditoria WHERE idaud=:idaud";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idaud = $this->getIdaud();
            $result->bindParam(':idaud', $idaud);
            $result->execute();
            return true;
        }catch(Exception $e){
            error_log("MAud::del - " . $e->getMessage());
            return false;
        }
    }

    // ─── Registrar eventos de sesión ───────────────────────────────────────────
    // NOTA: La tabla auditoria no tiene columnas email/exitoso/navegador.
    // Se guardan en datos_nue como JSON. idreg = 1 (exitoso) o 0 (fallido).

    public function registrarLogin($idemp, $idusu, $email, $exitoso, $ip, $navegador){
        try{
            // Evitar violaciones de FK: si vienen como '' o 0 o nulo, forzamos null nativo.
            $idemp = empty($idemp) ? null : (int)$idemp;
            $idusu = empty($idusu) ? null : (int)$idusu;
             
            $sql = "INSERT INTO auditoria(idemp, idusu, tabla, accion, idreg, datos_ant, datos_nue, email, exitoso, navegador, fecha, ip)
                    VALUES (:idemp, :idusu, 'login', 4, :idreg, NULL, NULL, :email, :exitoso, :navegador, NOW(), :ip)";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            
            if ($idemp === null) $result->bindValue(':idemp', null, PDO::PARAM_NULL);
            else $result->bindValue(':idemp', $idemp, PDO::PARAM_INT);
            
            if ($idusu === null) $result->bindValue(':idusu', null, PDO::PARAM_NULL);
            else $result->bindValue(':idusu', $idusu, PDO::PARAM_INT);
            
            $idreg = (int)$exitoso;

            $result->bindValue(':idreg',     $idreg, PDO::PARAM_INT);
            $result->bindValue(':email',     $email);
            $result->bindValue(':exitoso',   (int)$exitoso, PDO::PARAM_INT);
            $result->bindValue(':navegador', $navegador);
            $result->bindValue(':ip',        $ip);
            
            $result->execute();
        }catch(PDOException $e) {
            error_log("MAud::registrarLogin [PDO] - " . $e->getMessage());
        }catch(Exception $e){
            error_log("MAud::registrarLogin - " . $e->getMessage());
        }
    }

    public function registrarLogout($idemp, $idusu, $email, $ip, $navegador){
        try{
            $idemp = empty($idemp) ? null : (int)$idemp;
            $idusu = empty($idusu) ? null : (int)$idusu;

            $sql = "INSERT INTO auditoria(idemp, idusu, tabla, accion, idreg, datos_ant, datos_nue, email, exitoso, navegador, fecha, ip)
                    VALUES (:idemp, :idusu, 'login', 6, 1, NULL, NULL, :email, 1, :navegador, NOW(), :ip)";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            
            if ($idemp === null) $result->bindValue(':idemp', null, PDO::PARAM_NULL);
            else $result->bindValue(':idemp', $idemp, PDO::PARAM_INT);
            
            if ($idusu === null) $result->bindValue(':idusu', null, PDO::PARAM_NULL);
            else $result->bindValue(':idusu', $idusu, PDO::PARAM_INT);

            $result->bindValue(':email',     $email);
            $result->bindValue(':navegador', $navegador);
            $result->bindValue(':ip',        $ip);
            
            $result->execute();
        }catch(PDOException $e) {
            error_log("MAud::registrarLogout [PDO] - " . $e->getMessage());
        }catch(Exception $e){
            error_log("MAud::registrarLogout - " . $e->getMessage());
        }
    }

    // ─── Sesiones ─────────────────────────────────────────────────────────────
    
    public function getLogins($idemp, $idusu = null){
        try{
            $sql = "SELECT a.idaud, a.idemp, a.idusu, a.email, a.navegador,
                           a.exitoso,
                           a.ip, a.fecha, a.accion,
                           COALESCE(u.nomusu, 'Desconocido') AS nomusu,
                           COALESCE(u.apeusu, '') AS apeusu
                    FROM auditoria a
                    LEFT JOIN usuario u ON a.idusu = u.idusu
                    WHERE a.idemp = :idemp AND a.accion IN (4, 6)";
            if(!empty($idusu)) $sql .= " AND a.idusu = :idusu";
            $sql .= " ORDER BY a.fecha DESC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':idemp', $idemp);
            if(!empty($idusu)) $result->bindParam(':idusu', $idusu);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            error_log("MAud::getLogins - " . $e->getMessage());
            return [];
        }
    }

    public function getLoginsMes($idemp, $mes, $anio){
        try{
            $sql = "SELECT a.idaud, a.idemp, a.idusu, a.datos_nue,
                           a.idreg AS exitoso,
                           a.ip, a.fecha, a.accion,
                           COALESCE(u.nomusu, 'Desconocido') AS nomusu,
                           COALESCE(u.apeusu, '') AS apeusu
                    FROM auditoria a
                    LEFT JOIN usuario u ON a.idusu = u.idusu
                    WHERE a.idemp = :idemp
                      AND a.accion IN (4, 6)
                      AND MONTH(a.fecha) = :mes
                      AND YEAR(a.fecha)  = :anio
                    ORDER BY a.fecha DESC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':idemp', $idemp);
            $result->bindParam(':mes',   $mes,  PDO::PARAM_INT);
            $result->bindParam(':anio',  $anio, PDO::PARAM_INT);
            $result->execute();
            $rows = $result->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$row) {
                $d = json_decode($row['datos_nue'] ?? '', true);
                $row['email']    = $d['email']    ?? '';
                $row['navegador']= $d['navegador'] ?? '';
            }
            return $rows;
        }catch(Exception $e){
            error_log("MAud::getLoginsMes - " . $e->getMessage());
            return [];
        }
    }

    // ─── Movimientos ───────────────────────────────────────────────────────────

    public function getMovimientos($idemp, $idusu = null){
        try{
            $sql = "SELECT a.idaud, a.idemp, a.idusu, a.idreg,
                           a.datos_nue, a.datos_ant, a.fecha, a.ip, a.accion, a.tabla,
                           COALESCE(u.nomusu, 'Sistema') AS nomusu,
                           COALESCE(u.apeusu, '')        AS apeusu,
                           p.nomprod
                    FROM auditoria a
                    LEFT JOIN usuario u ON a.idusu = u.idusu
                    LEFT JOIN movim   m ON a.idreg = m.idmov
                    LEFT JOIN producto p ON m.idprod = p.idprod
                    WHERE a.idemp = :idemp
                      AND (a.accion = 5 OR (a.tabla = 'movim' AND a.accion IN (1,2,3)))";
            if($idusu) $sql .= " AND a.idusu = :idusu";
            $sql .= " ORDER BY a.fecha DESC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':idemp', $idemp);
            if($idusu) $result->bindParam(':idusu', $idusu);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            error_log("MAud::getMovimientos - " . $e->getMessage());
            return [];
        }
    }

    public function getMovimientosMes($idemp, $mes, $anio){
        try{
            $sql = "SELECT a.idaud, a.idemp, a.idusu, a.idreg,
                           a.datos_nue, a.datos_ant, a.fecha, a.ip, a.accion, a.tabla,
                           COALESCE(u.nomusu, 'Sistema') AS nomusu,
                           COALESCE(u.apeusu, '')        AS apeusu,
                           p.nomprod
                    FROM auditoria a
                    LEFT JOIN usuario u ON a.idusu = u.idusu
                    LEFT JOIN movim   m ON a.idreg = m.idmov
                    LEFT JOIN producto p ON m.idprod = p.idprod
                    WHERE a.idemp = :idemp
                      AND (a.accion = 5 OR (a.tabla = 'movim' AND a.accion IN (1,2,3)))
                      AND MONTH(a.fecha) = :mes
                      AND YEAR(a.fecha)  = :anio
                    ORDER BY a.fecha DESC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':idemp', $idemp);
            $result->bindParam(':mes',   $mes,  PDO::PARAM_INT);
            $result->bindParam(':anio',  $anio, PDO::PARAM_INT);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            error_log("MAud::getMovimientosMes - " . $e->getMessage());
            return [];
        }
    }

    // ─── Eventos CRUD (Crear / Editar / Eliminar en cualquier tabla) ───────────

    public function getEventosCRUD($idemp, $idusu = null){
        try{
            $sql = "SELECT a.idaud, a.idusu, a.tabla, a.accion, a.idreg,
                           a.datos_ant, a.datos_nue, a.fecha, a.ip,
                           COALESCE(u.nomusu, 'Sistema') AS nomusu,
                           COALESCE(u.apeusu, '')        AS apeusu
                    FROM auditoria a
                    LEFT JOIN usuario u ON a.idusu = u.idusu
                    WHERE a.idemp = :idemp
                      AND a.accion IN (1, 2, 3)
                      AND (a.tabla IS NULL OR a.tabla != 'login')";
            if($idusu) $sql .= " AND a.idusu = :idusu";
            $sql .= " ORDER BY a.fecha DESC LIMIT 200";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':idemp', $idemp);
            if($idusu) $result->bindParam(':idusu', $idusu);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            error_log("MAud::getEventosCRUD - " . $e->getMessage());
            return [];
        }
    }

    // ─── Timeline — últimas N acciones de todos los usuarios ──────────────────

    public function getTimeline($idemp, $limite = 25){
        try{
            $sql = "SELECT a.idaud, a.idusu, a.tabla, a.accion, a.idreg,
                           a.datos_nue, a.fecha, a.ip, a.exitoso, a.email,
                           COALESCE(u.nomusu, 'Desconocido') AS nomusu,
                           COALESCE(u.apeusu, '')            AS apeusu,
                           COALESCE(u.imgusu, '')            AS imgusu
                    FROM auditoria a
                    LEFT JOIN usuario u ON a.idusu = u.idusu
                    WHERE a.idemp = :idemp
                    ORDER BY a.fecha DESC
                    LIMIT :limite";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':idemp',  $idemp);
            $result->bindParam(':limite', $limite, PDO::PARAM_INT);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            error_log("MAud::getTimeline - " . $e->getMessage());
            return [];
        }
    }

    // ─── IPs Sospechosas — idreg=0 → login fallido (accion=4, idreg=0) ──────────

    public function getIPsSospechosas($idemp, $umbral = 3, $minutos = 60){
        try{
            // idreg=0 significa login fallido (así lo guarda registrarLogin)
            $sql = "SELECT a.ip,
                           COUNT(*) AS intentos,
                           MAX(a.fecha) AS ultimo_intento,
                           GROUP_CONCAT(
                               DISTINCT COALESCE(
                                   JSON_UNQUOTE(JSON_EXTRACT(a.datos_nue,'$.email')),
                                   '?'
                               ) ORDER BY a.fecha DESC SEPARATOR ', '
                           ) AS emails_usados
                    FROM auditoria a
                    WHERE a.idemp = :idemp
                      AND a.accion = 4
                      AND a.idreg  = 0
                      AND a.fecha >= DATE_SUB(NOW(), INTERVAL :minutos MINUTE)
                    GROUP BY a.ip
                    HAVING COUNT(*) >= :umbral
                    ORDER BY intentos DESC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':idemp',   $idemp);
            $result->bindParam(':minutos', $minutos, PDO::PARAM_INT);
            $result->bindParam(':umbral',  $umbral,  PDO::PARAM_INT);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            error_log("MAud::getIPsSospechosas - " . $e->getMessage());
            return [];
        }
    }

    // ─── Ranking de usuarios por actividad ────────────────────────────────────
    // idreg=1 → login exitoso, idreg=0 → login fallido (para accion=4)

    public function getResumenPorUsuario($idemp){
        try{
            $sql = "SELECT a.idusu,
                           COALESCE(u.nomusu, 'Desconocido') AS nomusu,
                           COALESCE(u.apeusu, '')            AS apeusu,
                           COALESCE(u.emausu, '')            AS emausu,
                           COUNT(*) AS total_eventos,
                           SUM(a.accion = 4 AND a.idreg = 1) AS logins_ok,
                           SUM(a.accion = 4 AND a.idreg = 0) AS logins_fail,
                           SUM(a.accion = 6)                  AS logouts,
                           SUM(a.accion IN (1,2,3,5))         AS operaciones,
                           MAX(a.fecha) AS ultima_actividad
                    FROM auditoria a
                    LEFT JOIN usuario u ON a.idusu = u.idusu
                    WHERE a.idemp = :idemp
                    GROUP BY a.idusu, u.nomusu, u.apeusu, u.emausu
                    ORDER BY total_eventos DESC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':idemp', $idemp);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            error_log("MAud::getResumenPorUsuario - " . $e->getMessage());
            return [];
        }
    }

    // ─── Actividad por día del mes (datos para gráfico de barras) ─────────────

    public function getActividadPorDia($idemp, $mes, $anio){
        try{
            $sql = "SELECT DAY(fecha) AS dia,
                           COUNT(*) AS total,
                           SUM(accion = 4 AND exitoso = 1) AS logins_ok,
                           SUM(accion = 4 AND exitoso = 0) AS logins_fail,
                           SUM(accion IN (1,2,3,5))        AS operaciones
                    FROM auditoria
                    WHERE idemp = :idemp
                      AND MONTH(fecha) = :mes
                      AND YEAR(fecha)  = :anio
                    GROUP BY DAY(fecha)
                    ORDER BY dia ASC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':idemp', $idemp);
            $result->bindParam(':mes',   $mes,  PDO::PARAM_INT);
            $result->bindParam(':anio',  $anio, PDO::PARAM_INT);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            error_log("MAud::getActividadPorDia - " . $e->getMessage());
            return [];
        }
    }

    // ─── Distribución de eventos (datos para gráfico de dona) ────────────────

    public function getDistribucionEventos($idemp, $mes, $anio){
        try{
            $sql = "SELECT
                        SUM(accion = 4 AND idreg = 1) AS login_ok,
                        SUM(accion = 4 AND idreg = 0) AS login_fail,
                        SUM(accion = 6)               AS logouts,
                        SUM(accion = 1)               AS creados,
                        SUM(accion = 2)               AS editados,
                        SUM(accion = 3)               AS eliminados,
                        SUM(accion = 5)               AS movimientos
                    FROM auditoria
                    WHERE idemp = :idemp
                      AND MONTH(fecha) = :mes
                      AND YEAR(fecha)  = :anio";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':idemp', $idemp);
            $result->bindParam(':mes',   $mes,  PDO::PARAM_INT);
            $result->bindParam(':anio',  $anio, PDO::PARAM_INT);
            $result->execute();
            return $result->fetch(PDO::FETCH_ASSOC) ?: [];
        }catch(Exception $e){
            error_log("MAud::getDistribucionEventos - " . $e->getMessage());
            return [];
        }
    }

    // ─── Tendencia de logins últimos 7 días ───────────────────────────────────

    public function getTendenciaLogins($idemp, $dias = 7){
        try{
            $sql = "SELECT DATE(fecha) AS dia,
                           SUM(accion = 4 AND idreg = 1) AS exitosos,
                           SUM(accion = 4 AND idreg = 0) AS fallidos
                    FROM auditoria
                    WHERE idemp = :idemp
                      AND accion = 4
                      AND fecha >= DATE_SUB(CURDATE(), INTERVAL :dias DAY)
                    GROUP BY DATE(fecha)
                    ORDER BY dia ASC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':idemp', $idemp);
            $result->bindParam(':dias',  $dias, PDO::PARAM_INT);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            error_log("MAud::getTendenciaLogins - " . $e->getMessage());
            return [];
        }
    }

    // ─── Estadísticas globales del dashboard ──────────────────────────────────
    // idreg=1 → exitoso, idreg=0 → fallido para accion=4

    public function getEstadisticasGlobales($idemp){
        try{
            $sql = "SELECT
                        COUNT(*) AS total_eventos,
                        SUM(accion = 4 AND idreg = 1) AS logins_exitosos,
                        SUM(accion = 4 AND idreg = 0) AS logins_fallidos,
                        SUM(accion = 6)               AS logouts,
                        SUM(accion IN (1,2,3,5))      AS operaciones,
                        COUNT(DISTINCT idusu)          AS usuarios_distintos,
                        COUNT(DISTINCT ip)             AS ips_distintas,
                        SUM(DATE(fecha) = CURDATE())   AS eventos_hoy
                    FROM auditoria
                    WHERE idemp = :idemp";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':idemp', $idemp);
            $result->execute();
            return $result->fetch(PDO::FETCH_ASSOC) ?: [];
        }catch(Exception $e){
            error_log("MAud::getEstadisticasGlobales - " . $e->getMessage());
            return [];
        }
    }

    // ─── Cantidad de eventos nuevos desde un timestamp (para polling AJAX) ────

    public function getCountDesde($idemp, $desde){
        try{
            $sql = "SELECT COUNT(*) AS nuevos FROM auditoria
                    WHERE idemp = :idemp AND fecha > :desde";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':idemp', $idemp);
            $result->bindParam(':desde', $desde);
            $result->execute();
            $row = $result->fetch(PDO::FETCH_ASSOC);
            return (int)($row['nuevos'] ?? 0);
        }catch(Exception $e){
            error_log("MAud::getCountDesde - " . $e->getMessage());
            return 0;
        }
    }

    // ─── Reporte Mensual ───────────────────────────────────────────────────────

    public function getReporteMensual($idemp, $mes, $anio){
        try{
            $sql = "SELECT
                        a.idaud,
                        a.fecha,
                        a.accion,
                        a.tabla,
                        a.idreg,
                        a.email,
                        a.exitoso,
                        a.ip,
                        a.navegador,
                        a.datos_nue,
                        a.datos_ant,
                        COALESCE(u.nomusu, 'Sistema')  AS nomusu,
                        COALESCE(u.apeusu, '')          AS apeusu,
                        COALESCE(u.emausu, a.email)     AS emausu,
                        CASE a.accion
                            WHEN 1 THEN 'Creado'
                            WHEN 2 THEN 'Editado'
                            WHEN 3 THEN 'Eliminado'
                            WHEN 4 THEN 'Login'
                            WHEN 5 THEN 'Movimiento'
                            WHEN 6 THEN 'Logout'
                            ELSE 'Otro'
                        END AS tipo_evento,
                        CASE a.accion
                            WHEN 4 THEN IF(a.exitoso = 1, 'Exitoso', 'Fallido')
                            WHEN 6 THEN 'Cierre de sesión'
                            ELSE ''
                        END AS estado_sesion
                    FROM auditoria a
                    LEFT JOIN usuario u ON a.idusu = u.idusu
                    WHERE a.idemp = :idemp
                      AND MONTH(a.fecha) = :mes
                      AND YEAR(a.fecha)  = :anio
                    ORDER BY a.fecha DESC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':idemp', $idemp);
            $result->bindParam(':mes',   $mes,  PDO::PARAM_INT);
            $result->bindParam(':anio',  $anio, PDO::PARAM_INT);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            error_log("MAud::getReporteMensual - " . $e->getMessage());
            return [];
        }
    }

    public function getResumenMensual($idemp, $mes, $anio){
        try{
            $sql = "SELECT
                        COUNT(*) AS total_eventos,
                        SUM(accion = 4 AND exitoso = 1) AS logins_exitosos,
                        SUM(accion = 4 AND exitoso = 0) AS logins_fallidos,
                        SUM(accion = 6)                 AS logouts,
                        SUM(accion IN (1,2,3,5))        AS movimientos
                    FROM auditoria
                    WHERE idemp = :idemp
                      AND MONTH(fecha) = :mes
                      AND YEAR(fecha)  = :anio";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':idemp', $idemp);
            $result->bindParam(':mes',   $mes,  PDO::PARAM_INT);
            $result->bindParam(':anio',  $anio, PDO::PARAM_INT);
            $result->execute();
            $row = $result->fetch(PDO::FETCH_ASSOC);
            return $row ?: [
                'total_eventos'  => 0,
                'logins_exitosos'=> 0,
                'logins_fallidos'=> 0,
                'logouts'        => 0,
                'movimientos'    => 0,
            ];
        }catch(Exception $e){
            error_log("MAud::getResumenMensual - " . $e->getMessage());
            return [];
        }
    }

    // ─── Utilidades ────────────────────────────────────────────────────────────

    public function getUsuariosEmpresa($idemp){
        try{
            $sql = "SELECT DISTINCT u.idusu, u.nomusu, u.apeusu, u.emausu
                    FROM usuario u
                    INNER JOIN usuario_empresa ue ON u.idusu = ue.idusu
                    WHERE ue.idemp = :idemp
                    ORDER BY u.nomusu, u.apeusu";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':idemp', $idemp);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            error_log("MAud::getUsuariosEmpresa - " . $e->getMessage());
            return [];
        }
    }

    public function vaciarLogins($idemp){
        try{
            $sql = "DELETE FROM auditoria WHERE idemp = :idemp AND accion IN (4, 6)";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':idemp', $idemp);
            $result->execute();
            return true;
        }catch(Exception $e){
            error_log("MAud::vaciarLogins - " . $e->getMessage());
            return false;
        }
    }
}
?>