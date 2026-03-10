<?php
class Msosal {

    // Atributos de la tabla solsalida
    private $idsal;
    private $fecsal;
    private $tpsal;
    private $idemp;
    private $idusu;
    private $idubi;
    private $refdoc;
    private $estsal;

    // GETTERS
    function getIdsal(){ return $this->idsal; }
    function getFecsal(){ return $this->fecsal; }
    function getTpsal(){ return $this->tpsal; }
    function getIdemp(){ return $this->idemp; }
    function getIdusu(){ return $this->idusu; }
    function getIdubi(){ return $this->idubi; }
    function getRefdoc(){ return $this->refdoc; }
    function getEstsal(){ return $this->estsal; }

    // SETTERS
    function setIdsal($v){ $this->idsal = $v; }
    function setFecsal($v){ $this->fecsal = $v; }
    function setTpsal($v){ $this->tpsal = $v; }
    function setIdemp($v){ $this->idemp = $v; }
    function setIdusu($v){ $this->idusu = $v; }
    function setIdubi($v){ $this->idubi = $v; }
    function setRefdoc($v){ $this->refdoc = $v; }
    function setEstsal($v){ $this->estsal = $v; }

    // CARGA MASIVA
    public function setData($d){
        if(isset($d['idsal']))  $this->setIdsal($d['idsal']);
        if(isset($d['fecsal'])) $this->setFecsal($d['fecsal']);
        if(isset($d['tpsal']))  $this->setTpsal($d['tpsal']);
        if(isset($d['idemp']))  $this->setIdemp($d['idemp']);
        if(isset($d['idusu']))  $this->setIdusu($d['idusu']);
        if(isset($d['idubi']))  $this->setIdubi($d['idubi']);
        if(isset($d['refdoc'])) $this->setRefdoc($d['refdoc']);
        if(isset($d['estsal'])) $this->setEstsal($d['estsal']);
    }

    // OBTENER TODOS
    public function getAll($idemp = null, $idper = null){
        try{
            $sql = "SELECT s.*, 
                           u.nomusu, 
                           e.nomemp,
                           ub.nomubi
                    FROM solsalida s
                    LEFT JOIN usuario u ON s.idusu = u.idusu
                    LEFT JOIN empresa e ON s.idemp = e.idemp
                    LEFT JOIN ubicacion ub ON s.idubi = ub.idubi";

            if ($idper != 1 && $idemp !== null) {
                $sql .= " WHERE s.idemp = :idemp";
            }

            $sql .= " ORDER BY s.idsal DESC";
            
            $cn = (new conexion())->get_conexion();
            $stm = $cn->prepare($sql);
            if ($idper != 1 && $idemp !== null) {
                $stm->bindParam(':idemp', $idemp);
            }
            $stm->execute();
            return $stm->fetchAll(PDO::FETCH_ASSOC);

        } catch(Exception $e){
            error_log("Msosal::getAll() error: " . $e->getMessage());
            return [];
        }
    }

    // OBTENER UNO
    public function getOne(){
        try{
            $sql = "SELECT s.*, 
                           u.nomusu, 
                           e.nomemp,
                           ub.nomubi
                    FROM solsalida s
                    LEFT JOIN usuario u ON s.idusu = u.idusu
                    LEFT JOIN empresa e ON s.idemp = e.idemp
                    LEFT JOIN ubicacion ub ON s.idubi = ub.idubi
                    WHERE s.idsal=:idsal";

            $cn = (new conexion())->get_conexion();
            $stm = $cn->prepare($sql);
            $stm->bindParam(":idsal", $this->idsal);
            $stm->execute();
            return $stm->fetch(PDO::FETCH_ASSOC);

        } catch(Exception $e){
            return null;
        }
    }

    // GUARDAR
    public function save(){
        try{
            $sql = "INSERT INTO solsalida(fecsal, tpsal, idemp, idusu, idubi, refdoc, estsal)
                    VALUES(:fecsal, :tpsal, :idemp, :idusu, :idubi, :refdoc, :estsal)";

            $cn = (new conexion())->get_conexion();
            $stm = $cn->prepare($sql);

            $stm->bindParam(":fecsal",  $this->fecsal);
            $stm->bindParam(":tpsal",   $this->tpsal);
            $stm->bindParam(":idemp",   $this->idemp);
            $stm->bindParam(":idusu",   $this->idusu);
            $stm->bindParam(":idubi",   $this->idubi);
            $stm->bindParam(":refdoc",  $this->refdoc);
            $stm->bindParam(":estsal",  $this->estsal);

            $stm->execute();
            return $cn->lastInsertId();

        } catch(Exception $e){
            return false;
        }
    }

    // EDITAR
    public function edit(){
        try{
            $sql = "UPDATE solsalida SET
                        fecsal=:fecsal,
                        tpsal=:tpsal,
                        idemp=:idemp,
                        idusu=:idusu,
                        idubi=:idubi,
                        refdoc=:refdoc,
                        estsal=:estsal
                    WHERE idsal=:idsal";

            $cn = (new conexion())->get_conexion();
            $stm = $cn->prepare($sql);

            $stm->bindParam(":idsal",   $this->idsal);
            $stm->bindParam(":fecsal",  $this->fecsal);
            $stm->bindParam(":tpsal",   $this->tpsal);
            $stm->bindParam(":idemp",   $this->idemp);
            $stm->bindParam(":idusu",   $this->idusu);
            $stm->bindParam(":idubi",   $this->idubi);
            $stm->bindParam(":refdoc",  $this->refdoc);
            $stm->bindParam(":estsal",  $this->estsal);

            $stm->execute();
            return true;

        } catch(Exception $e){
            return false;
        }
    }

    // ELIMINAR
    public function del(){
        try{
            $sql = "DELETE FROM solsalida WHERE idsal=:idsal";

            $cn = (new conexion())->get_conexion();
            $stm = $cn->prepare($sql);
            $stm->bindParam(":idsal", $this->idsal);
            $stm->execute();
            return true;

        } catch(Exception $e){
            return false;
        }
    }

    // OBTENER DETALLES DE UNA SALIDA
    public function getDetalles(){
        try{
            $sql = "SELECT d.iddsal, d.idemp, d.idsal, d.idprod, d.cantdet, d.vundet, d.idlote,
                           (d.cantdet * d.vundet) AS totdet,
                           p.nomprod,
                           l.codlot,
                           l.fecven,
                           l.costuni AS costuni_lote
                    FROM detsalida d
                    LEFT JOIN producto p ON d.idprod = p.idprod
                    LEFT JOIN lote l ON d.idlote = l.idlote
                    WHERE d.idsal=:idsal
                    ORDER BY d.iddsal DESC";

            $cn = (new conexion())->get_conexion();
            $stm = $cn->prepare($sql);
            $stm->bindParam(":idsal", $this->idsal);
            $stm->execute();
            return $stm->fetchAll(PDO::FETCH_ASSOC);

        } catch(Exception $e){
            error_log("msosal::getDetalles() error: " . $e->getMessage());
            return [];
        }
    }
}
?>
