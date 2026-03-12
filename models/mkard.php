<?php
require_once __DIR__ . '/conexion.php';
class Mkard {
    private $idkar;
    private $anio;
    private $mes;
    private $cerrado;
    private $idemp;

    function getIdkar() { return $this->idkar; }
    function getAnio() { return $this->anio; }
    function getMes() { return $this->mes; }
    function getCerrado() { return $this->cerrado; }
    function getIdemp() { return $this->idemp; }

    function setIdkar($idkar) { $this->idkar = $idkar; }
    function setAnio($anio) { $this->anio = $anio; }
    function setMes($mes) { $this->mes = $mes; }
    function setCerrado($cerrado) { $this->cerrado = $cerrado; }
    function setIdemp($idemp) { $this->idemp = $idemp; }

    // ✅ Listar todos los kardex
    public function getAll() {
        try {
            if (!$this->getIdemp()) {
                return array('error' => 'No se encontró la empresa activa.');
            }
            $sql = "SELECT k.*, 
                           COUNT(m.idmov) AS total_movs, 
                           IFNULL(SUM(CASE WHEN m.tipmov = 1 THEN m.cantmov WHEN m.tipmov = 2 THEN -m.cantmov ELSE 0 END), 0) AS balance
                    FROM kardex k
                    LEFT JOIN movim m ON k.idkar = m.idkar
                    WHERE k.idemp = :idemp
                    GROUP BY k.idkar
                    ORDER BY k.anio DESC, k.mes DESC";

            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idemp = $this->getIdemp();
            $result->bindParam(":idemp", $idemp);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en Mkard::getAll() - " . $e->getMessage());
            return [];
        }
    }

    // ✅ Obtener un kardex
    public function getOne() {
        try {
            if (!$this->getIdemp()) {
                return array('error' => 'No se encontró la empresa activa.');
            }
            $sql = "SELECT * FROM kardex WHERE idkar = :idkar AND idemp = :idemp";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idkar = $this->getIdkar();
            $idemp = $this->getIdemp();
            $result->bindParam(":idkar", $idkar);
            $result->bindParam(":idemp", $idemp);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en Mkard::getOne() - " . $e->getMessage());
            return [];
        }
    }

    // ✅ Guardar nuevo kardex (ahora solo crea si no existe)
    public function save() {
        try {
            if (!$this->getIdemp()) {
                return array('error' => 'No se encontró la empresa activa.');
            }
            // Verificar si ya existe un kardex para ese año, mes y empresa
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $sql_check = "SELECT idkar FROM kardex WHERE anio = :anio AND mes = :mes AND idemp = :idemp LIMIT 1";
            $result_check = $conexion->prepare($sql_check);
            $anio = $this->getAnio();
            $mes = $this->getMes();
            $idemp = $this->getIdemp();
            $result_check->bindParam(":anio", $anio);
            $result_check->bindParam(":mes", $mes);
            $result_check->bindParam(":idemp", $idemp);
            $result_check->execute();
            $existe = $result_check->fetch(PDO::FETCH_ASSOC);
            if ($existe) {
                // Ya existe, no crear otro
                return $existe['idkar'];
            }
            $sql = "INSERT INTO kardex (anio, mes, cerrado, idemp)
                    VALUES (:anio, :mes, :cerrado, :idemp)";
            $result = $conexion->prepare($sql);
            $cerrado = $this->getCerrado();
            $result->bindParam(":anio", $anio);
            $result->bindParam(":mes", $mes);
            $result->bindParam(":cerrado", $cerrado);
            $result->bindParam(":idemp", $idemp);
            $result->execute();
            return $conexion->lastInsertId();
        } catch (Exception $e) {
            error_log("Error en Mkard::save() - " . $e->getMessage());
            return false;
        }
    }

    // ✅ Editar kardex
    public function edit() {
        try {
            if (!$this->getIdemp()) {
                return array('error' => 'No se encontró la empresa activa.');
            }
            $sql = "UPDATE kardex SET anio = :anio, mes = :mes, cerrado = :cerrado
                    WHERE idkar = :idkar AND idemp = :idemp";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $anio = $this->getAnio();
            $mes = $this->getMes();
            $cerrado = $this->getCerrado();
            $idkar = $this->getIdkar();
            $idemp = $this->getIdemp();
            $result->bindParam(":anio", $anio);
            $result->bindParam(":mes", $mes);
            $result->bindParam(":cerrado", $cerrado);
            $result->bindParam(":idkar", $idkar);
            $result->bindParam(":idemp", $idemp);
            $result->execute();
            return true;
        } catch (Exception $e) {
            error_log("Error en Mkard::edit() - " . $e->getMessage());
            return false;
        }
    }

    // ✅ Eliminar kardex
    public function del() {
        try {
            if (!$this->getIdemp()) {
                return array('error' => 'No se encontró la empresa activa.');
            }
            $sql = "DELETE FROM kardex WHERE idkar = :idkar AND idemp = :idemp";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idkar = $this->getIdkar();
            $idemp = $this->getIdemp();
            $result->bindParam(":idkar", $idkar);
            $result->bindParam(":idemp", $idemp);
            $result->execute();
            return true;
        } catch (Exception $e) {
            error_log("Error en Mkard::del() - " . $e->getMessage());
            return false;
        }
    }

    // ✅ Obtener movimientos de un kardex
    public function getMovimientos($idkar) {
        try {
            $sql = "SELECT m.idmov, m.fecmov AS fecha, p.nomprod AS producto, 
                           m.tipmov, m.cantmov, m.valmov, m.docref, m.obs
                    FROM movim m
                    INNER JOIN producto p ON m.idprod = p.idprod
                    WHERE m.idkar = :idkar
                    ORDER BY m.fecmov DESC";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(":idkar", $idkar);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en Mkard::getMovimientos() - " . $e->getMessage());
            return [];
        }
    }

    // ✅ Guardar un movimiento
    public function saveMovimiento($data) {
        try {
            $sql = "INSERT INTO movim (idkar, idprod, idubi, tipmov, cantmov, valmov, docref, obs, idusu, fecmov, fec_crea)
                    VALUES (:idkar, :idprod, :idubi, :tipmov, :cantmov, :valmov, :docref, :obs, :idusu, NOW(), NOW())";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute($data);
            return $conexion->lastInsertId();
        } catch (Exception $e) {
            error_log("Error en Mkard::saveMovimiento() - " . $e->getMessage());
            return false;
        }
    }

    // ✅ Obtener productos para el select filtrados por empresa
    public function getProductos() {
        try {
            $idemp = $this->getIdemp();
            if ($idemp) {
                $sql = "SELECT idprod, nomprod FROM producto WHERE idemp = :idemp AND act = 1 ORDER BY nomprod ASC";
                $modelo = new conexion();
                $conexion = $modelo->get_conexion();
                $result = $conexion->prepare($sql);
                $result->bindParam(':idemp', $idemp);
            } else {
                $sql = "SELECT idprod, nomprod FROM producto WHERE act = 1 ORDER BY nomprod ASC";
                $modelo = new conexion();
                $conexion = $modelo->get_conexion();
                $result = $conexion->prepare($sql);
            }
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en Mkard::getProductos() - " . $e->getMessage());
            return [];
        }
    }

    // ✅ Obtener o crear kardex por período (año/mes)
    public function getByPeriodo($anio, $mes) {
        try {
            if (!$this->getIdemp()) {
                return null;
            }
            
            // Buscar kardex existente
            $sql = "SELECT * FROM kardex WHERE anio = :anio AND mes = :mes AND idemp = :idemp LIMIT 1";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idemp = $this->getIdemp();
            $result->bindParam(":anio", $anio);
            $result->bindParam(":mes", $mes);
            $result->bindParam(":idemp", $idemp);
            $result->execute();
            $kardex = $result->fetch(PDO::FETCH_ASSOC);
            
            // Si existe, retornarlo
            if ($kardex) {
                return $kardex;
            }
            
            // Si no existe, crear uno nuevo
            $this->setAnio($anio);
            $this->setMes($mes);
            $this->setCerrado(0); // Por defecto abierto
            $idkar = $this->save();
            
            if ($idkar) {
                return [
                    'idkar' => $idkar,
                    'anio' => $anio,
                    'mes' => $mes,
                    'cerrado' => 0,
                    'idemp' => $idemp
                ];
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Error en Mkard::getByPeriodo() - " . $e->getMessage());
            return null;
        }
    }
}
