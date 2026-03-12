<?php

class MInv{
    private $idinv;
    private $idemp;  // ✅ NUEVO
    private $idprod;
    private $idubi;
    private $cant;
    private $fec_crea;
    private $fec_actu;

    function getIdinv(){
        return $this->idinv;
    }
    function getIdemp(){
        return $this->idemp;
    }
    function getIdprod(){
        return $this->idprod;
    }
    function getIdubi(){
        return $this->idubi;
    }
    function getCant(){
        return $this->cant;
    }
    function getFec_crea(){
        return $this->fec_crea;
    }
    function getFec_actu(){
        return $this->fec_actu;
    }
    
    function setIdinv($idinv){
        $this->idinv = $idinv;
    }
    function setIdemp($idemp){
        $this->idemp = $idemp;
    }
    function setIdprod($idprod){
        $this->idprod = $idprod;
    }
    function setIdubi($idubi){
        $this->idubi = $idubi;
    }
    function setCant($cant){
        $this->cant = $cant;
    }
    function setFec_crea($fec_crea){
        $this->fec_crea = $fec_crea;
    }
    function setFec_actu($fec_actu){
        $this->fec_actu = $fec_actu;
    }

    // ✅ MODIFICADO: Filtra por empresa
    public function getAll(){
        try{
            $idper = isset($_SESSION['idper']) ? $_SESSION['idper'] : NULL;
            $idemp = isset($_SESSION['idemp']) ? $_SESSION['idemp'] : NULL;

            if($idper == 1){
                // SuperAdmin ve TODO
                $sql = "SELECT i.idinv, i.idemp, i.idprod, p.nomprod, p.codprod,
                               i.idubi, u.nomubi, u.codubi,
                               c.idcat, c.nomcat,
                               i.cant, i.fec_crea, i.fec_actu,
                               e.nomemp, e.razemp
                        FROM inventario i
                        INNER JOIN producto p ON i.idprod = p.idprod
                        INNER JOIN categoria c ON p.idcat = c.idcat
                        INNER JOIN ubicacion u ON i.idubi = u.idubi
                        LEFT JOIN empresa e ON i.idemp = e.idemp
                        ORDER BY i.idinv DESC";
                $modelo = new conexion();
                $conexion = $modelo->get_conexion();
                $result = $conexion->prepare($sql);
            } else {
                // Admin/Empleado: Solo ve su empresa
                $sql = "SELECT i.idinv, i.idemp, i.idprod, p.nomprod, p.codprod,
                               i.idubi, u.nomubi, u.codubi,
                               c.idcat, c.nomcat,
                               i.cant, i.fec_crea, i.fec_actu,
                               e.nomemp, e.razemp
                        FROM inventario i
                        INNER JOIN producto p ON i.idprod = p.idprod
                        INNER JOIN categoria c ON p.idcat = c.idcat
                        INNER JOIN ubicacion u ON i.idubi = u.idubi
                        LEFT JOIN empresa e ON i.idemp = e.idemp
                        WHERE i.idemp = :idemp
                        ORDER BY i.idinv DESC";
                $modelo = new conexion();
                $conexion = $modelo->get_conexion();
                $result = $conexion->prepare($sql);
                $result->bindParam(':idemp', $idemp);
            }
            
            $result->execute();
            $res = $result->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        }catch(Exception $e){
            echo "Error: ".$e->getMessage()."<br><br>";
        }
    }

    /**
     * Obtener resumen de stock desde productos y lotes
     */
    public function getStockResumen(){
        try {
            $idper = isset($_SESSION['idper']) ? $_SESSION['idper'] : NULL;
            $idemp = isset($_SESSION['idemp']) ? $_SESSION['idemp'] : NULL;

            $sql = "SELECT i.idinv, p.idprod, p.nomprod, p.codprod,
                           c.nomcat,
                           u.idubi, u.nomubi, u.codubi,
                           e.nomemp,
                           COALESCE(SUM(l.cantact), i.cant, 0) AS cant
                    FROM producto p
                    LEFT JOIN lote l ON l.idprod = p.idprod
                    LEFT JOIN ubicacion u ON l.idubi = u.idubi
                    LEFT JOIN inventario i ON i.idprod = p.idprod AND i.idubi = l.idubi AND i.idemp = p.idemp
                    LEFT JOIN categoria c ON p.idcat = c.idcat
                    LEFT JOIN empresa e ON p.idemp = e.idemp
                    WHERE p.act = 1";

            if ($idper != 1 && $idemp !== null) {
                $sql .= " AND p.idemp = :idemp";
            }

            $sql .= " GROUP BY p.idprod, u.idubi, i.idinv, p.nomprod, p.codprod, c.nomcat, u.nomubi, u.codubi, e.nomemp, i.cant
                      ORDER BY p.nomprod ASC, u.nomubi ASC";

            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            if ($idper != 1 && $idemp !== null) {
                $result->bindParam(':idemp', $idemp);
            }
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en MInv::getStockResumen() - " . $e->getMessage());
            return [];
        }
    }

    // ✅ MODIFICADO: Filtra por empresa
    public function getOne(){
        try{
            $idper = isset($_SESSION['idper']) ? $_SESSION['idper'] : NULL;
            $idemp = isset($_SESSION['idemp']) ? $_SESSION['idemp'] : NULL;

            if($idper == 1){
                $sql = "SELECT idinv, idemp, idprod, idubi, cant, fec_crea, fec_actu 
                        FROM inventario 
                        WHERE idinv=:idinv";
                $modelo = new conexion();
                $conexion = $modelo->get_conexion();
                $result = $conexion->prepare($sql);
                $idinv = $this->getIdinv();
                $result->bindParam(':idinv', $idinv);
            } else {
                $sql = "SELECT idinv, idemp, idprod, idubi, cant, fec_crea, fec_actu 
                        FROM inventario 
                        WHERE idinv=:idinv AND idemp=:idemp";
                $modelo = new conexion();
                $conexion = $modelo->get_conexion();
                $result = $conexion->prepare($sql);
                $idinv = $this->getIdinv();
                $result->bindParam(':idinv', $idinv);
                $result->bindParam(':idemp', $idemp);
            }
            
            $result->execute();
            $res = $result->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        }catch(Exception $e){
            echo "Error: ".$e->getMessage()."<br><br>";
        }
    }

    // ✅ MODIFICADO: Guarda con idemp
    public function save(){
        try{
            $sql = "INSERT INTO inventario(idemp, idprod, idubi, cant, fec_crea, fec_actu) 
                    VALUES (:idemp, :idprod, :idubi, :cant, :fec_crea, :fec_actu)";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            
            $idemp = $this->getIdemp();
            $result->bindParam(':idemp', $idemp);
            $idprod = $this->getIdprod();
            $result->bindParam(':idprod', $idprod);
            $idubi = $this->getIdubi();
            $result->bindParam(':idubi', $idubi);
            $cant = $this->getCant();
            $result->bindParam(':cant', $cant);
            $fec_crea = $this->getFec_crea();
            $result->bindParam(':fec_crea', $fec_crea);
            $fec_actu = $this->getFec_actu();
            $result->bindParam(':fec_actu', $fec_actu);
            
            $result->execute();
            return true;
        }catch(Exception $e){
            echo "Error: ".$e->getMessage()."<br><br>";
            return false;
        }
    }

    // ✅ MODIFICADO: Solo edita de su empresa
    public function upd(){
        try{
            $idper = isset($_SESSION['idper']) ? $_SESSION['idper'] : NULL;
            $idemp_session = isset($_SESSION['idemp']) ? $_SESSION['idemp'] : NULL;

            if($idper == 1){
                $sql = "UPDATE inventario 
                        SET idemp=:idemp, idprod=:idprod, idubi=:idubi, cant=:cant, 
                            fec_crea=:fec_crea, fec_actu=:fec_actu 
                        WHERE idinv=:idinv";
            } else {
                $sql = "UPDATE inventario 
                        SET idprod=:idprod, idubi=:idubi, cant=:cant, 
                            fec_crea=:fec_crea, fec_actu=:fec_actu 
                        WHERE idinv=:idinv AND idemp=:idemp_session";
            }
            
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            
            $idinv = $this->getIdinv();
            $result->bindParam(':idinv', $idinv);
            
            if($idper == 1){
                $idemp = $this->getIdemp();
                $result->bindParam(':idemp', $idemp);
            } else {
                $result->bindParam(':idemp_session', $idemp_session);
            }
            
            $idprod = $this->getIdprod();
            $result->bindParam(':idprod', $idprod);
            $idubi = $this->getIdubi();
            $result->bindParam(':idubi', $idubi);
            $cant = $this->getCant();
            $result->bindParam(':cant', $cant);
            $fec_crea = $this->getFec_crea();
            $result->bindParam(':fec_crea', $fec_crea);
            $fec_actu = $this->getFec_actu();
            $result->bindParam(':fec_actu', $fec_actu);
            
            $result->execute();
            return true;
        }catch(Exception $e){
            echo "Error: ".$e->getMessage()."<br><br>";
            return false;
        }
    }

    // ✅ MODIFICADO: Solo elimina de su empresa
    public function del(){
        try{
            $idper = isset($_SESSION['idper']) ? $_SESSION['idper'] : NULL;
            $idemp = isset($_SESSION['idemp']) ? $_SESSION['idemp'] : NULL;

            if($idper == 1){
                $sql = "DELETE FROM inventario WHERE idinv=:idinv";
                $modelo = new conexion();
                $conexion = $modelo->get_conexion();
                $result = $conexion->prepare($sql);
                $idinv = $this->getIdinv();
                $result->bindParam(':idinv', $idinv);
            } else {
                $sql = "DELETE FROM inventario WHERE idinv=:idinv AND idemp=:idemp";
                $modelo = new conexion();
                $conexion = $modelo->get_conexion();
                $result = $conexion->prepare($sql);
                $idinv = $this->getIdinv();
                $result->bindParam(':idinv', $idinv);
                $result->bindParam(':idemp', $idemp);
            }
            
            $result->execute();
            return true;
        }catch(Exception $e){
            echo "Error: ".$e->getMessage()."<br><br>";
            return false;
        }
    }

    // ✅ NUEVO: Obtener productos de la empresa
    public function getAllProd(){
        try{
            $idper = isset($_SESSION['idper']) ? $_SESSION['idper'] : NULL;
            $idemp = isset($_SESSION['idemp']) ? $_SESSION['idemp'] : NULL;

            if($idper == 1){
                $sql = "SELECT p.idprod, p.nomprod, p.codprod, c.nomcat 
                        FROM producto p
                        INNER JOIN categoria c ON p.idcat = c.idcat
                        WHERE p.act = 1
                        ORDER BY p.nomprod ASC";
                $modelo = new conexion();
                $conexion = $modelo->get_conexion();
                $result = $conexion->prepare($sql);
            } else {
                $sql = "SELECT p.idprod, p.nomprod, p.codprod, c.nomcat 
                        FROM producto p
                        INNER JOIN categoria c ON p.idcat = c.idcat
                        WHERE p.idemp = :idemp AND p.act = 1
                        ORDER BY p.nomprod ASC";
                $modelo = new conexion();
                $conexion = $modelo->get_conexion();
                $result = $conexion->prepare($sql);
                $result->bindParam(':idemp', $idemp);
            }
            
            $result->execute();
            $res = $result->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        }catch(Exception $e){
            echo "Error: ".$e->getMessage()."<br><br>";
        }
    }

    // ✅ NUEVO: Obtener ubicaciones de la empresa
    public function getAllUbi(){
        try{
            $idper = isset($_SESSION['idper']) ? $_SESSION['idper'] : NULL;
            $idemp = isset($_SESSION['idemp']) ? $_SESSION['idemp'] : NULL;

            if($idper == 1){
                $sql = "SELECT idubi, nomubi, codubi 
                        FROM ubicacion
                        WHERE act = 1
                        ORDER BY nomubi ASC";
                $modelo = new conexion();
                $conexion = $modelo->get_conexion();
                $result = $conexion->prepare($sql);
            } else {
                $sql = "SELECT idubi, nomubi, codubi 
                        FROM ubicacion
                        WHERE idemp = :idemp AND act = 1
                        ORDER BY nomubi ASC";
                $modelo = new conexion();
                $conexion = $modelo->get_conexion();
                $result = $conexion->prepare($sql);
                $result->bindParam(':idemp', $idemp);
            }
            
            $result->execute();
            $res = $result->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        }catch(Exception $e){
            echo "Error: ".$e->getMessage()."<br><br>";
        }
    }

    // ✅ NUEVO: Obtener datos de la empresa para el PDF
    public function getEmpresa(){
        try{
            $idemp = isset($_SESSION['idemp']) ? $_SESSION['idemp'] : NULL;
            
            $sql = "SELECT idemp, nomemp, razemp, nitemp, diremp, telemp, emaemp, logo 
                    FROM empresa 
                    WHERE idemp = :idemp";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':idemp', $idemp);
            $result->execute();
            $res = $result->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        }catch(Exception $e){
            echo "Error: ".$e->getMessage()."<br><br>";
        }
    }

    /**
     * Retorna todos los lotes activos de la empresa agrupados por (idprod, idubi).
     * Resultado: array indexado por "idprod_idubi" => [ [...lote...], ... ]
     */
    public function getLotesPorInventario() {
        try {
            $idper = isset($_SESSION['idper']) ? $_SESSION['idper'] : null;
            $idemp = isset($_SESSION['idemp']) ? $_SESSION['idemp'] : null;

            $where = ($idper == 1) ? '' : 'WHERE l.idemp = :idemp';

            $sql = "SELECT l.idlote, l.codlot, l.idprod, l.idubi,
                           l.cantini, l.cantact, l.costuni, l.fecing, l.fecven,
                           CASE
                               WHEN l.fecven IS NULL THEN 'Sin vencimiento'
                               WHEN l.fecven < CURDATE() THEN 'Vencido'
                               WHEN l.fecven <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'Por vencer'
                               ELSE 'Vigente'
                           END AS estado_lote
                    FROM lote l
                    $where
                    ORDER BY l.idprod, l.idubi,
                             (l.fecven IS NULL) DESC,
                             l.fecven ASC,
                             l.fecing ASC";

            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            if ($idper != 1) {
                $result->bindParam(':idemp', $idemp);
            }
            $result->execute();
            $rows = $result->fetchAll(PDO::FETCH_ASSOC);

            // Index by "idprod_idubi"
            $indexed = [];
            foreach ($rows as $row) {
                $key = $row['idprod'] . '_' . (int)$row['idubi'];
                $indexed[$key][] = $row;
            }
            return $indexed;
        } catch (Exception $e) {
            error_log("Error en MInv::getLotesPorInventario() - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Contar lotes asociados a un registro de inventario
     */
    public function countLotesByInv($idinv) {
        try {
            $idper = isset($_SESSION['idper']) ? $_SESSION['idper'] : null;
            $idemp = isset($_SESSION['idemp']) ? $_SESSION['idemp'] : null;

            $sql = "SELECT COUNT(*)
                    FROM lote l
                    INNER JOIN inventario i ON i.idprod = l.idprod AND i.idubi = l.idubi
                    WHERE i.idinv = :idinv";
            if ($idper != 1) {
                $sql .= " AND i.idemp = :idemp";
            }

            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':idinv', $idinv);
            if ($idper != 1) {
                $result->bindParam(':idemp', $idemp);
            }
            $result->execute();
            return (int)$result->fetchColumn();
        } catch (Exception $e) {
            error_log("Error en MInv::countLotesByInv() - " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Sincronizar inventario con el stock actual de lotes
     */
    public function syncInventarioFromLotes($idemp, $idprod, $idubi, $conexion = null) {
        try {
            $idemp = (int)$idemp;
            $idprod = (int)$idprod;
            $idubi = (int)$idubi;
            if ($idemp <= 0 || $idprod <= 0 || $idubi <= 0) {
                return false;
            }

            $cn = $conexion ?: (new conexion())->get_conexion();

            $sqlSum = "SELECT COALESCE(SUM(l.cantact), 0) AS stock_total
                       FROM lote l
                       INNER JOIN producto p ON l.idprod = p.idprod
                       WHERE l.idprod = :idprod AND l.idubi = :idubi AND p.idemp = :idemp";
            $stmSum = $cn->prepare($sqlSum);
            $stmSum->execute([
                ':idprod' => $idprod,
                ':idubi' => $idubi,
                ':idemp' => $idemp
            ]);
            $stockTotal = (float)$stmSum->fetchColumn();

            $sqlUp = "INSERT INTO inventario (idemp, idprod, idubi, cant, fec_crea, fec_actu)
                      VALUES (:idemp, :idprod, :idubi, :cant, NOW(), NOW())
                      ON DUPLICATE KEY UPDATE cant = VALUES(cant), fec_actu = NOW()";
            $stmUp = $cn->prepare($sqlUp);
            return $stmUp->execute([
                ':idemp' => $idemp,
                ':idprod' => $idprod,
                ':idubi' => $idubi,
                ':cant' => $stockTotal
            ]);
        } catch (Exception $e) {
            error_log("Error en MInv::syncInventarioFromLotes() - " . $e->getMessage());
            return false;
        }
    }
}
?>
