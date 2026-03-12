<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/minv.php';

class Msoent {
    private $idemp;
    private $lastError;

    private function syncInventarioFromMovim($conexion, $idemp, $idprod, $idubi) {
        $sqlSaldo = "SELECT COALESCE(SUM(CASE WHEN m.tipmov = 1 THEN m.cantmov WHEN m.tipmov = 2 THEN -m.cantmov ELSE 0 END), 0) AS saldo
                     FROM movim m
                     INNER JOIN kardex k ON m.idkar = k.idkar
                     WHERE k.idemp = :idemp AND m.idprod = :idprod AND m.idubi = :idubi";
        $stmSaldo = $conexion->prepare($sqlSaldo);
        $stmSaldo->execute([
            ':idemp' => $idemp,
            ':idprod' => $idprod,
            ':idubi' => $idubi
        ]);
        $saldo = (float)$stmSaldo->fetchColumn();

        $sqlInv = "INSERT INTO inventario (idemp, idprod, idubi, cant, fec_crea, fec_actu)
                   VALUES (:idemp, :idprod, :idubi, :cant, NOW(), NOW())
                   ON DUPLICATE KEY UPDATE cant = VALUES(cant), fec_actu = NOW()";
        $stmInv = $conexion->prepare($sqlInv);
        $stmInv->execute([
            ':idemp' => $idemp,
            ':idprod' => $idprod,
            ':idubi' => $idubi,
            ':cant' => $saldo
        ]);
    }

    function setIdemp($idemp){ 
        $this->idemp = $idemp; 
    }
    
    function getIdemp(){ 
        return $this->idemp; 
    }

    function getLastError(){
        return $this->lastError;
    }

    // Crear cabecera de solicitud de entrada
    public function createSolicitud($idemp, $idusu, $idubi = null, $idprov = null) {
        try {
            $sql = "INSERT INTO solentrada (idemp, idprov, idubi, fecsol, fecent, tippag, estsol, totsol, obssol, idusu, fec_crea, fec_actu)
                    VALUES (:idemp, :idprov, :idubi, CURDATE(), CURDATE(), 'Contado', 'Pendiente', 0, NULL, :idusu, NOW(), NOW())";
            $cn = (new conexion())->get_conexion();
            $stm = $cn->prepare($sql);
            $stm->execute([
                ':idemp' => $idemp,
                ':idprov' => $idprov,
                ':idubi' => $idubi,
                ':idusu' => $idusu
            ]);
            return (int)$cn->lastInsertId();
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en Msoent::createSolicitud() - " . $e->getMessage());
            return 0;
        }
    }

    // Obtener todos los detalles de entrada
    public function getAll($idsol) {
        try {
            // Usamos LEFT JOIN para mostrar el registro aunque el producto no exista
            // Usamos COALESCE para manejar valores nulos si el producto fue borrado
            $sql = "SELECT d.iddet, d.idsol, d.idprod, COALESCE(p.nomprod, 'Producto no encontrado') as nomprod, 
                           d.cantdet, d.vundet, (d.cantdet * d.vundet) as totdet
                    FROM detentrada d
                    LEFT JOIN producto p ON d.idprod = p.idprod
                    WHERE d.idsol = :idsol AND d.idemp = :idemp
                    ORDER BY d.iddet DESC";
            
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(":idsol", $idsol, PDO::PARAM_INT);
            $stmt->bindParam(":idemp", $this->idemp, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Msoent::getAll() - " . $e->getMessage());
            return [];
        }
    }

    // Guardar detalle
    public function save($data) {
        try {
            $sql = "INSERT INTO detentrada (idsol, idprod, cantdet, vundet, idemp, fec_crea)
                    VALUES (:idsol, :idprod, :cantdet, :vundet, :idemp, NOW())";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            if(isset($data[':totdet'])) unset($data[':totdet']);
            $result = $stmt->execute($data);
            if (!$result) {
                $this->lastError = json_encode($stmt->errorInfo());
                throw new Exception("Error al guardar el detalle");
            }
            $this->lastError = null;
            return true;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en Msoent::save() - " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en Msoent::save() - " . $e->getMessage());
            return false;
        }
    }

    // Eliminar detalle
    public function delete($iddet) {
        try {
            $sql = "DELETE FROM detentrada WHERE iddet = :iddet AND idemp = :idemp";
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(":iddet", $iddet, PDO::PARAM_INT);
            $stmt->bindParam(":idemp", $this->idemp, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Msoent::delete() - " . $e->getMessage());
            return false;
        }
    }

    // Obtener total de la solicitud
    public function getTotal($idsol) {
        try {
            $sql = "SELECT SUM(cantdet * vundet) as total FROM detentrada 
                    WHERE idsol = :idsol AND idemp = :idemp";
            
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(":idsol", $idsol, PDO::PARAM_INT);
            $stmt->bindParam(":idemp", $this->idemp, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error en Msoent::getTotal() - " . $e->getMessage());
            return 0;
        }
    }

    // Aprobar solicitud y crear movimientos en Kardex
    public function aprobarSolicitud($idsol, $idkar, $idubi, $idusu) {
        try {
            $modelo = new conexion();
            $conexion = $modelo->get_conexion();
            $conexion->beginTransaction();

            $minv = new MInv();

            $this->lastError = null;

            // Bloquear cabecera para evitar aprobaciones concurrentes.
            $sqlCab = "SELECT idsol, idubi, estsol, fecent
                       FROM solentrada
                       WHERE idsol = :idsol AND idemp = :idemp
                       FOR UPDATE";
            $stmtCab = $conexion->prepare($sqlCab);
            $stmtCab->execute([
                ':idsol' => $idsol,
                ':idemp' => $this->idemp
            ]);
            $cab = $stmtCab->fetch(PDO::FETCH_ASSOC);

            if (!$cab) {
                throw new Exception("La solicitud de entrada no existe para la empresa activa.");
            }

            if (($cab['estsol'] ?? '') === 'Aprobada') {
                throw new Exception("La solicitud ya estaba aprobada.");
            }

            $sqlKar = "SELECT cerrado FROM kardex WHERE idkar = :idkar LIMIT 1";
            $stmtKar = $conexion->prepare($sqlKar);
            $stmtKar->execute([':idkar' => $idkar]);
            $kar = $stmtKar->fetch(PDO::FETCH_ASSOC);

            if (!$kar) {
                throw new Exception("No existe el kardex seleccionado para aprobar la entrada.");
            }

            if ((int)$kar['cerrado'] === 1) {
                throw new Exception("El kardex del periodo actual esta cerrado.");
            }

            $sqlDet = "SELECT iddet, idprod, cantdet, vundet
                       FROM detentrada
                       WHERE idsol = :idsol AND idemp = :idemp";
            $stmtDet = $conexion->prepare($sqlDet);
            $stmtDet->execute([
                ':idsol' => $idsol,
                ':idemp' => $this->idemp
            ]);
            $detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($detalles)) {
                throw new Exception("No hay detalles para aprobar");
            }

            $idubiMov = (int)($cab['idubi'] ?? $idubi);
            if ($idubiMov <= 0) {
                $sqlUbi = "SELECT idubi FROM ubicacion WHERE idemp = :idemp AND act = 1 ORDER BY idubi ASC LIMIT 1";
                $stmtUbi = $conexion->prepare($sqlUbi);
                $stmtUbi->execute([':idemp' => $this->idemp]);
                $idubiFallback = (int)$stmtUbi->fetchColumn();
                if ($idubiFallback > 0) {
                    $idubiMov = $idubiFallback;
                    $sqlUpdUbi = "UPDATE solentrada SET idubi = :idubi WHERE idsol = :idsol AND idemp = :idemp";
                    $stmtUpdUbi = $conexion->prepare($sqlUpdUbi);
                    $stmtUpdUbi->execute([
                        ':idubi' => $idubiMov,
                        ':idsol' => $idsol,
                        ':idemp' => $this->idemp
                    ]);
                } else {
                    throw new Exception("La solicitud no tiene ubicacion valida y no hay ubicaciones activas.");
                }
            }

            $fecMov = !empty($cab['fecent']) ? $cab['fecent'] : date('Y-m-d');
            $docRef = 'ENT-' . $idsol;

            $sqlMov = "INSERT INTO movim (idkar, idprod, idubi, fecmov, tipmov, cantmov, valmov, costprom, docref, obs, idusu, fec_crea, fec_actu)
                       VALUES (:idkar, :idprod, :idubi, :fecmov, 1, :cantmov, :valmov, :costprom, :docref, :obs, :idusu, NOW(), NOW())";
            $stmtMov = $conexion->prepare($sqlMov);
            $syncPairs = [];
            
            foreach ($detalles as $detalle) {
                $cant = (float)$detalle['cantdet'];
                $costoUni = (float)$detalle['vundet'];
                $valMov = $cant * $costoUni;

                // Crear lote automaticamente por cada detalle aprobado para trazabilidad real.
                $codLote = 'ENT-' . $idsol . '-' . $detalle['iddet'];
                $sqlLote = "INSERT INTO lote (idprod, codlot, fecing, fecven, cantini, cantact, costuni, iddent, idubi, fec_crea, fec_actu)
                            VALUES (:idprod, :codlot, :fecing, NULL, :cantini, :cantact, :costuni, :iddent, :idubi, NOW(), NOW())";
                $stmtLote = $conexion->prepare($sqlLote);
                $stmtLote->execute([
                    ':idprod' => (int)$detalle['idprod'],
                    ':codlot' => $codLote,
                    ':fecing' => $fecMov,
                    ':cantini' => $cant,
                    ':cantact' => $cant,
                    ':costuni' => $costoUni,
                    ':iddent' => (int)$detalle['iddet'],
                    ':idubi' => $idubiMov
                ]);

                // Sincronizar inventario desde lotes para el producto/ubicacion
                $minv->syncInventarioFromLotes((int)$this->idemp, (int)$detalle['idprod'], $idubiMov, $conexion);

                $stmtMov->execute([
                    ':idkar' => $idkar,
                    ':idprod' => $detalle['idprod'],
                    ':idubi' => $idubiMov,
                    ':fecmov' => $fecMov,
                    ':idusu' => $idusu,
                    ':cantmov' => $cant,
                    ':valmov' => $valMov,
                    ':costprom' => $costoUni,
                    ':docref' => $docRef,
                    ':obs' => 'Entrada #' . $idsol
                ]);

                $pairKey = $detalle['idprod'] . '|' . $idubiMov;
                $syncPairs[$pairKey] = [
                    'idprod' => (int)$detalle['idprod'],
                    'idubi' => (int)$idubiMov
                ];
            }

            foreach ($syncPairs as $pair) {
                $this->syncInventarioFromMovim($conexion, (int)$this->idemp, (int)$pair['idprod'], (int)$pair['idubi']);
            }

            $sqlUpd = "UPDATE solentrada
                       SET estsol = 'Aprobada', idusu_apr = :idusuapr, fec_actu = NOW()
                       WHERE idsol = :idsol AND idemp = :idemp AND (estsol IS NULL OR estsol <> 'Aprobada')";
            $stmtUpd = $conexion->prepare($sqlUpd);
            $stmtUpd->execute([
                ':idusuapr' => $idusu,
                ':idsol' => $idsol,
                ':idemp' => $this->idemp
            ]);
            
            $conexion->commit();
            return true;
            
        } catch (Exception $e) {
            if (isset($conexion)) {
                $conexion->rollBack();
            }
            $this->lastError = $e->getMessage();
            error_log("Error en Msoent::aprobarSolicitud() - " . $e->getMessage());
            return false;
        }
    }
}
?>