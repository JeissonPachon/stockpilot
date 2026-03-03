<?php
class Mlote {
    // Atributos
    private $idlote;
    private $idprod;
    private $codlot;
    private $fecing;
    private $fecven;
    private $cantini;
    private $cantact;
    private $cstuni;

    // Getters
    function getIdlote() { return $this->idlote;   }
    function getIdprod   () { return $this->idprod;   }
    function getCodlot   () { return $this->codlot;   }
    function getFecing   () { return $this->fecing;   }
    function getFecven   () { return $this->fecven;   }
    function getCantini  () { return $this->cantini;  }
    function getCantact  () { return $this->cantact;  }
    function getCstuni   () { return $this->cstuni;   }

    // Setters
    function setIdlote  ($v) { $this->idlote  = $v; }
    function setIdprod  ($v) { $this->idprod  = $v; }
    function setCodlot ($v) { $this->codlot = $v; }

    function setFecing  ($v) { $this->fecing  = $v; }
    function setFecven  ($v) { $this->fecven  = $v; }
    function setCantini ($v) { $this->cantini = $v; }
    function setCantact ($v) { $this->cantact = $v; }
    function setCstuni  ($v) { $this->cstuni  = $v; }

    // ==================== MÉTODOS ====================

    public function getAll() {
        try {
            $sql = "SELECT l.*, p.nomprod 
                    FROM lote l 
                    LEFT JOIN producto p ON l.idprod = p.idprod 
                    ORDER BY l.idlote DESC";
            $modelo    = new conexion();
            $conexion  = $modelo->get_conexion();
            $res       = $conexion->prepare($sql);
            $res->execute();
            return $res->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            men($e->getMessage());
        }
    }

    public function getOne() {
        try {
            $sql = "SELECT l.*, p.nomprod 
                    FROM lote l 
                    LEFT JOIN producto p ON l.idprod = p.idprod 
                    WHERE l.idlote = :idlote";
            $modelo   = new conexion();
            $conexion = $modelo->get_conexion();
            $res      = $conexion->prepare($sql);
            $res->bindParam(':idlote', $this->idlote);
            $res->execute();
            $resultado = $res->fetch(PDO::FETCH_ASSOC);
            return $resultado ?: []; // devuelve array vacío si no existe
        } catch (Exception $e) {
            men($e->getMessage());
        }
    }

    public function save() {
        try {
            $sql = "INSERT INTO lote 
                    (idprod, codlot, fecing, fecven, cantini, cantact, cstuni) 
                    VALUES 
                    (:idprod, :codlot, :fecing, :fecven, :cantini, :cantact, :cstuni)";
            $modelo   = new conexion();
            $conexion = $modelo->get_conexion();
            $res      = $conexion->prepare($sql);

            $res->bindParam(':idprod',   $this->idprod);
            $res->bindParam(':codlot',  $this->codlot);
            $res->bindParam(':fecing',   $this->fecing);
            $res->bindParam(':fecven',   $this->fecven);
            $res->bindParam(':cantini',  $this->cantini);
            $res->bindParam(':cantact',  $this->cantact);
            $res->bindParam(':cstuni',   $this->cstuni);

            $res->execute();
            men("Guardado correctamente");
            return true;
        } catch (Exception $e) {
            men($e->getMessage());
            return false;
        }
    }

    public function edit() {
        try {
            $sql = "UPDATE lote SET 
                        idprod   = :idprod,
                        codlot  = :codlot,
                        fecing   = :fecing,
                        fecven   = :fecven,
                        cantini  = :cantini,
                        cantact  = :cantact,
                        cstuni   = :cstuni
                    WHERE idlote = :idlote";
            $modelo   = new conexion();
            $conexion = $modelo->get_conexion();
            $res      = $conexion->prepare($sql);

            $res->bindParam(':idlote',  $this->idlote);
            $res->bindParam(':idprod',  $this->idprod);
            $res->bindParam(':codlot', $this->codlot);
            $res->bindParam(':fecing',  $this->fecing);
            $res->bindParam(':fecven',  $this->fecven);
            $res->bindParam(':cantini', $this->cantini);
            $res->bindParam(':cantact', $this->cantact);
            $res->bindParam(':cstuni',  $this->cstuni);

            $res->execute();
            men("Actualizado correctamente");
            return true;
        } catch (Exception $e) {
            men($e->getMessage());
            return false;
        }
    }

    public function del() {
        try {
            $sql = "DELETE FROM lote WHERE idlote = :idlote";
            $modelo   = new conexion();
            $conexion = $modelo->get_conexion();
            $res      = $conexion->prepare($sql);
            $res->bindParam(':idlote', $this->idlote);
            $res->execute();
            men("Eliminado correctamente");
        } catch (Exception $e) {
            men($e->getMessage());
        }
    }

    // Opcional: contar cuántos movimientos tiene este lote (para no borrarlo si tiene detalle)
    public function tieneMovimientos() {
        try {
            $sql = "SELECT COUNT(*) AS total FROM detsalida WHERE idlote = :idlote 
                    UNION ALL 
                    SELECT COUNT(*) FROM detcompra WHERE idlote = :idlote";
            $modelo   = new conexion();
            $conexion = $modelo->get_conexion();
            $res      = $conexion->prepare($sql);
            $res->bindParam(':idlote', $this->idlote);
            $res->execute();
            $resultados = $res->fetchAll(PDO::FETCH_COLUMN);
            $total = array_sum($resultados);
            return $total > 0;
        } catch (Exception $e) {
            men($e->getMessage());
            return true; // por seguridad no borrar si falla
        }
    }
}
?>