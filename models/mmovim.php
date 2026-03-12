<?php 
class Mmov {
    // Atributos
    private $idmov;
    private $idemp; // Solo para filtrado, no se guarda en movim
    private $idkar;
    private $idprod;
    private $idubi;
    private $fecmov;
    private $tipmov;
    private $cantmov;
    private $valmov;
    private $costprom;
    private $docref;
    private $obs;
    private $idusu;
    private $fec_crea;
    private $fec_actu;

    // Getters
    function getIdmov()    { return $this->idmov; }
    function getIdemp()    { return $this->idemp; }
    function getIdkar()    { return $this->idkar; }
    function getIdprod()   { return $this->idprod; }
    function getIdubi()    { return $this->idubi; }
    function getFecmov()   { return $this->fecmov; }
    function getTipmov()   { return $this->tipmov; }
    function getCantmov()  { return $this->cantmov; }
    function getValmov()   { return $this->valmov; }
    function getCostprom() { return $this->costprom; }
    function getDocref()   { return $this->docref; }
    function getObs()      { return $this->obs; }
    function getIdusu()    { return $this->idusu; }
    function getFec_crea() { return $this->fec_crea; }
    function getFec_actu() { return $this->fec_actu; }

    // Setters
    function setIdmov($idmov)        { $this->idmov = $idmov; }
    function setIdemp($idemp)        { $this->idemp = $idemp; }
    function setIdkar($idkar)        { $this->idkar = $idkar; }
    function setIdprod($idprod)      { $this->idprod = $idprod; }
    function setIdubi($idubi)        { $this->idubi = $idubi; }
    function setFecmov($fecmov)      { $this->fecmov = $fecmov; }
    function setTipmov($tipmov)      { $this->tipmov = $tipmov; }
    function setCantmov($cantmov)    { $this->cantmov = $cantmov; }
    function setValmov($valmov)      { $this->valmov = $valmov; }
    function setCostprom($costprom)  { $this->costprom = $costprom; }
    function setDocref($docref)      { $this->docref = $docref; }
    function setObs($obs)            { $this->obs = $obs; }
    function setIdusu($idusu)        { $this->idusu = $idusu; }
    function setFec_crea($fec_crea)  { $this->fec_crea = $fec_crea; }
    function setFec_actu($fec_actu)  { $this->fec_actu = $fec_actu; }

    // ======= MÉTODOS CRUD MEJORADOS =======
    
    /**
     * Obtener todos los movimientos con información relacionada
     */
    public function getAll(){
        try {
            $sql = "SELECT m.idmov, m.idkar, m.idprod, m.idubi, m.fecmov, m.tipmov, 
                           m.cantmov, m.valmov, m.costprom, m.docref, m.obs, m.idusu, 
                           m.fec_crea, m.fec_actu,
                           k.idemp, e.nomemp, p.nomprod, u.nomubi, us.nomusu, us.apeusu
                    FROM movim m
                    LEFT JOIN kardex k ON m.idkar = k.idkar
                    LEFT JOIN empresa e ON k.idemp = e.idemp
                    LEFT JOIN producto p ON m.idprod = p.idprod
                    LEFT JOIN ubicacion u ON m.idubi = u.idubi
                    LEFT JOIN usuario us ON m.idusu = us.idusu
                    ORDER BY m.fecmov DESC, m.idmov DESC";
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch(Exception $e){
            error_log("Error en Mmov::getAll: ".$e->getMessage());
            return [];
        }
    }

    /**
     * Obtener movimientos por empresa (a través de kardex)
     */
    public function getAllByEmpresa($idemp){
        try {
            $sql = "SELECT m.idmov, m.idkar, m.idprod, m.idubi, m.fecmov, m.tipmov, 
                           m.cantmov, m.valmov, m.costprom, m.docref, m.obs, m.idusu, 
                           m.fec_crea, m.fec_actu,
                           k.idemp, e.nomemp, p.nomprod, u.nomubi, us.nomusu, us.apeusu
                    FROM movim m
                    INNER JOIN kardex k ON m.idkar = k.idkar
                    LEFT JOIN empresa e ON k.idemp = e.idemp
                    LEFT JOIN producto p ON m.idprod = p.idprod
                    LEFT JOIN ubicacion u ON m.idubi = u.idubi
                    LEFT JOIN usuario us ON m.idusu = us.idusu
                    WHERE k.idemp = :idemp
                    ORDER BY m.fecmov DESC, m.idmov DESC";
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':idemp', $idemp);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch(Exception $e){
            error_log("Error en Mmov::getAllByEmpresa: ".$e->getMessage());
            return [];
        }
    }

    /**
     * Obtener un movimiento específico
     */
    public function getOne(){
        try {
            $sql = "SELECT m.idmov, m.idkar, m.idprod, m.idubi, m.fecmov, m.tipmov, 
                           m.cantmov, m.valmov, m.costprom, m.docref, m.obs, m.idusu, 
                           m.fec_crea, m.fec_actu,
                           k.idemp, e.nomemp, p.nomprod, u.nomubi
                    FROM movim m
                    LEFT JOIN kardex k ON m.idkar = k.idkar
                    LEFT JOIN empresa e ON k.idemp = e.idemp
                    LEFT JOIN producto p ON m.idprod = p.idprod
                    LEFT JOIN ubicacion u ON m.idubi = u.idubi
                    WHERE m.idmov=:idmov";
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idmov = $this->getIdmov();
            $result->bindParam(':idmov', $idmov);
            $result->execute();
            return $result->fetch(PDO::FETCH_ASSOC);
        } catch(Exception $e){
            error_log("Error en Mmov::getOne: ".$e->getMessage());
            return null;
        }
    }

    /**
     * Guardar nuevo movimiento
     */
    public function save(){
        try {
            $log = "Mmov::save called at " . date('Y-m-d H:i:s') . "\n";
            $log .= "Data: " . json_encode([
                'idkar' => $this->idkar,
                'idprod' => $this->idprod,
                'idubi' => $this->idubi,
                'fecmov' => $this->fecmov,
                'tipmov' => $this->tipmov,
                'cantmov' => $this->cantmov,
                'valmov' => $this->valmov,
                'idusu' => $this->idusu
            ]) . "\n";
            file_put_contents(__DIR__ . '/../debug_log.txt', $log, FILE_APPEND);

            $sql = "INSERT INTO movim(idkar, idprod, idubi, fecmov, tipmov, 
                                      cantmov, valmov, costprom, docref, obs, idusu, fec_crea, fec_actu) 
                    VALUES(:idkar, :idprod, :idubi, :fecmov, :tipmov, 
                           :cantmov, :valmov, :costprom, :docref, :obs, :idusu, NOW(), NOW())";
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $res = $conexion->prepare($sql);

            $res->bindParam(":idkar", $this->idkar);
            $res->bindParam(":idprod", $this->idprod);
            $res->bindParam(":idubi", $this->idubi);
            $res->bindParam(":fecmov", $this->fecmov);
            $res->bindParam(":tipmov", $this->tipmov);
            $res->bindParam(":cantmov", $this->cantmov);
            $res->bindParam(":valmov", $this->valmov);
            $res->bindParam(":costprom", $this->costprom);
            $res->bindParam(":docref", $this->docref);
            $res->bindParam(":obs", $this->obs);
            $res->bindParam(":idusu", $this->idusu);
            
            if($res->execute()){
                return $conexion->lastInsertId();
            }
            return false;
        } catch(Exception $e){
            $log = "Mmov::save ERROR: " . $e->getMessage() . "\n";
            file_put_contents(__DIR__ . '/../debug_log.txt', $log, FILE_APPEND);
            echo "Error en Mmov::save: ".$e->getMessage()."<br>";
            error_log("Error en Mmov::save: ".$e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar movimiento existente
     */
    public function edit(){
        try {
            $sql = "UPDATE movim SET 
                        idkar=:idkar, idprod=:idprod, idubi=:idubi, 
                        fecmov=:fecmov, tipmov=:tipmov, cantmov=:cantmov, valmov=:valmov, 
                        costprom=:costprom, docref=:docref, obs=:obs, idusu=:idusu, 
                        fec_actu=NOW() 
                    WHERE idmov=:idmov";
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $res = $conexion->prepare($sql);

            $res->bindParam(":idmov", $this->idmov);
            $res->bindParam(":idkar", $this->idkar);
            $res->bindParam(":idprod", $this->idprod);
            $res->bindParam(":idubi", $this->idubi);
            $res->bindParam(":fecmov", $this->fecmov);
            $res->bindParam(":tipmov", $this->tipmov);
            $res->bindParam(":cantmov", $this->cantmov);
            $res->bindParam(":valmov", $this->valmov);
            $res->bindParam(":costprom", $this->costprom);
            $res->bindParam(":docref", $this->docref);
            $res->bindParam(":obs", $this->obs);
            $res->bindParam(":idusu", $this->idusu);

            return $res->execute();
        } catch(Exception $e){
            error_log("Error en Mmov::edit: ".$e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar movimiento
     */
    public function del(){
        try {
            $sql = "DELETE FROM movim WHERE idmov=:idmov";
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $res = $conexion->prepare($sql);
            $res->bindParam(":idmov", $this->idmov);
            return $res->execute();
        } catch(Exception $e){
            error_log("Error en Mmov::del: ".$e->getMessage());
            return false;
        }
    }

    /**
     * Obtener movimientos por periodo (kardex)
     */
    public function getByKardex($idkar, $idemp = null){
        try {
            $sql = "SELECT m.idmov, m.idkar, m.idprod, m.idubi, m.fecmov, m.tipmov,
                           m.cantmov, m.valmov, m.costprom, m.docref, m.obs, m.idusu,
                           m.fec_crea, m.fec_actu,
                           k.idemp, e.nomemp, p.nomprod, u.nomubi, us.nomusu, us.apeusu
                    FROM movim m
                    INNER JOIN kardex k ON m.idkar = k.idkar
                    LEFT JOIN empresa e ON k.idemp = e.idemp
                    LEFT JOIN producto p ON m.idprod = p.idprod
                    LEFT JOIN ubicacion u ON m.idubi = u.idubi
                    LEFT JOIN usuario us ON m.idusu = us.idusu
                    WHERE m.idkar = :idkar";
            if ($idemp) {
                $sql .= " AND k.idemp = :idemp";
            }
            $sql .= " ORDER BY m.fecmov DESC, m.idmov DESC";

            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->bindParam(':idkar', $idkar);
            if ($idemp) {
                $result->bindParam(':idemp', $idemp);
            }
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch(Exception $e){
            error_log("Error en Mmov::getByKardex: ".$e->getMessage());
            return [];
        }
    }
}
?>
