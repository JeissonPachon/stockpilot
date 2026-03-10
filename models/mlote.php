<?php
require_once __DIR__ . '/conexion.php';

class Mlote {
    private $idlote;
    private $idprod;
    private $codlot;
    private $fecing;
    private $fecven;
    private $cantini;
    private $cantact;
    private $costuni;
    private $iddent;
    private $idubi;   // ← Ubicación del lote

    // Getters
    function getIdlote()  { return $this->idlote;  }
    function getIdprod()  { return $this->idprod;  }
    function getCodlot()  { return $this->codlot;  }
    function getFecing()  { return $this->fecing;  }
    function getFecven()  { return $this->fecven;  }
    function getCantini() { return $this->cantini; }
    function getCantact() { return $this->cantact; }
    function getCostuni() { return $this->costuni; }
    function getIddent()  { return $this->iddent;  }
    function getIdubi()   { return $this->idubi;   }

    // Setters
    function setIdlote($v)  { $this->idlote  = $v; }
    function setIdprod($v)  { $this->idprod  = $v; }
    function setCodlot($v)  { $this->codlot  = $v; }
    function setFecing($v)  { $this->fecing  = $v; }
    function setFecven($v)  { $this->fecven  = $v; }
    function setCantini($v) { $this->cantini = $v; }
    function setCantact($v) { $this->cantact = $v; }
    function setCostuni($v) { $this->costuni = $v; }
    function setIddent($v)  { $this->iddent  = $v; }
    function setIdubi($v)   { $this->idubi   = $v; }

    // ── OBTENER TODOS ─────────────────────────────────────────────
    public function getAll($idemp = null, $idper = null) {
        try {
            $sql = "SELECT l.*,
                           p.nomprod, p.codprod, p.idemp AS prod_idemp,
                           u.nomubi, u.codubi,
                           CASE
                               WHEN l.fecven IS NULL                                          THEN 'Sin vencimiento'
                               WHEN l.fecven < CURDATE()                                      THEN 'Vencido'
                               WHEN l.fecven < DATE_ADD(CURDATE(), INTERVAL 30 DAY)           THEN 'Por vencer'
                               ELSE 'Vigente'
                           END AS estado
                    FROM lote l
                    INNER JOIN producto  p ON l.idprod = p.idprod
                    LEFT  JOIN ubicacion u ON l.idubi  = u.idubi";

            if ($idper != 1 && $idemp !== null) {
                $sql .= " WHERE p.idemp = :idemp";
            }

            $sql .= " ORDER BY l.idlote DESC";

            $cn  = (new conexion())->get_conexion();
            $stm = $cn->prepare($sql);
            if ($idper != 1 && $idemp !== null) {
                $stm->bindParam(':idemp', $idemp);
            }
            $stm->execute();
            return $stm->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Mlote::getAll() error: " . $e->getMessage());
            return [];
        }
    }

    // ── OBTENER UNO ───────────────────────────────────────────────
    public function getOne() {
        try {
            $sql = "SELECT l.*, p.nomprod, u.nomubi
                    FROM lote l
                    INNER JOIN producto  p ON l.idprod = p.idprod
                    LEFT  JOIN ubicacion u ON l.idubi  = u.idubi
                    WHERE l.idlote = :idlote";
            $cn  = (new conexion())->get_conexion();
            $stm = $cn->prepare($sql);
            $stm->bindParam(':idlote', $this->idlote);
            $stm->execute();
            return $stm->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Mlote::getOne() error: " . $e->getMessage());
            return null;
        }
    }

    // ── GUARDAR ───────────────────────────────────────────────────
    public function save() {
        try {
            $sql = "INSERT INTO lote (idprod, codlot, fecing, fecven, cantini, cantact, costuni, iddent, idubi)
                    VALUES (:idprod, :codlot, :fecing, :fecven, :cantini, :cantact, :costuni, :iddent, :idubi)";
            $cn  = (new conexion())->get_conexion();
            $stm = $cn->prepare($sql);
            $stm->bindParam(':idprod',  $this->idprod);
            $stm->bindParam(':codlot',  $this->codlot);
            $stm->bindParam(':fecing',  $this->fecing);
            $stm->bindParam(':fecven',  $this->fecven);
            $stm->bindParam(':cantini', $this->cantini);
            $stm->bindParam(':cantact', $this->cantact);
            $stm->bindParam(':costuni', $this->costuni);
            $stm->bindParam(':iddent',  $this->iddent);
            $stm->bindParam(':idubi',   $this->idubi);
            $stm->execute();
            return $cn->lastInsertId();
        } catch (Exception $e) {
            error_log("Mlote::save() error: " . $e->getMessage());
            return false;
        }
    }

    // ── EDITAR ────────────────────────────────────────────────────
    public function edit() {
        try {
            $sql = "UPDATE lote SET
                        idprod  = :idprod,
                        codlot  = :codlot,
                        fecing  = :fecing,
                        fecven  = :fecven,
                        cantini = :cantini,
                        cantact = :cantact,
                        costuni = :costuni,
                        idubi   = :idubi
                    WHERE idlote = :idlote";
            $cn  = (new conexion())->get_conexion();
            $stm = $cn->prepare($sql);
            $stm->bindParam(':idlote',  $this->idlote);
            $stm->bindParam(':idprod',  $this->idprod);
            $stm->bindParam(':codlot',  $this->codlot);
            $stm->bindParam(':fecing',  $this->fecing);
            $stm->bindParam(':fecven',  $this->fecven);
            $stm->bindParam(':cantini', $this->cantini);
            $stm->bindParam(':cantact', $this->cantact);
            $stm->bindParam(':costuni', $this->costuni);
            $stm->bindParam(':idubi',   $this->idubi);
            return $stm->execute();
        } catch (Exception $e) {
            error_log("Mlote::edit() error: " . $e->getMessage());
            return false;
        }
    }

    // ── ELIMINAR ──────────────────────────────────────────────────
    public function del() {
        try {
            $sql = "DELETE FROM lote WHERE idlote = :idlote";
            $cn  = (new conexion())->get_conexion();
            $stm = $cn->prepare($sql);
            $stm->bindParam(':idlote', $this->idlote);
            return $stm->execute();
        } catch (Exception $e) {
            error_log("Mlote::del() error: " . $e->getMessage());
            return false;
        }
    }

    // ── ACTUALIZAR STOCK ATÓMICAMENTE (Entradas/Salidas) ─────────
    public function updateStock($idlote, $cantidad) {
        try {
            $sql = "UPDATE lote SET cantact = cantact + :cantidad WHERE idlote = :idlote";
            $cn  = (new conexion())->get_conexion();
            $stm = $cn->prepare($sql);
            $stm->bindParam(':idlote',   $idlote);
            $stm->bindParam(':cantidad', $cantidad);
            return $stm->execute();
        } catch (Exception $e) {
            error_log("Mlote::updateStock() error: " . $e->getMessage());
            return false;
        }
    }

    // ── LISTADO DE UBICACIONES (para el select del modal) ────────
    public function getAllUbi($idemp = null, $idper = null) {
        try {
            $sql = "SELECT idubi, nomubi, codubi FROM ubicacion";
            if ($idper != 1 && $idemp) {
                $sql .= " WHERE idemp = :idemp";
            }
            $sql .= " ORDER BY nomubi ASC";
            $cn  = (new conexion())->get_conexion();
            $stm = $cn->prepare($sql);
            if ($idper != 1 && $idemp) {
                $stm->bindParam(':idemp', $idemp);
            }
            $stm->execute();
            return $stm->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
?>
