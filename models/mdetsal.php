<?php
class Mdetsal {
    private $iddet;
    private $idemp;
    private $idsal;
    private $idprod;
    private $cantdet;
    private $vundet;
    private $idlote;

    // Getters
    function getIddet(){ return $this->iddet; }
    function getIdemp(){ return $this->idemp; }
    function getIdsal(){ return $this->idsal; }
    function getIdprod(){ return $this->idprod; }
    function getCantdet(){ return $this->cantdet; }
    function getVundet(){ return $this->vundet; }
    function getIdlote(){ return $this->idlote; }

    // Setters
    function setIddet($v){ $this->iddet = $v; }
    function setIdemp($v){ $this->idemp = $v; }
    function setIdsal($v){ $this->idsal = $v; }
    function setIdprod($v){ $this->idprod = $v; }
    function setCantdet($v){ $this->cantdet = $v; }
    function setVundet($v){ $this->vundet = $v; }
    function setIdlote($v){ $this->idlote = $v; }

    // Guardar
    public function save(){
        try {
            $sql = "INSERT INTO detsalida(idemp, idsal, idprod, cantdet, vundet, idlote)
                    VALUES(:idemp, :idsal, :idprod, :cantdet, :vundet, :idlote)";
            $cn = (new conexion())->get_conexion();
            $stm = $cn->prepare($sql);
            $stm->bindParam(":idemp", $this->idemp);
            $stm->bindParam(":idsal", $this->idsal);
            $stm->bindParam(":idprod", $this->idprod);
            $stm->bindParam(":cantdet", $this->cantdet);
            $stm->bindParam(":vundet", $this->vundet);
            $stm->bindParam(":idlote", $this->idlote);
            $ok = $stm->execute();
            if (!$ok) {
                error_log("mdetsal::save() error: " . print_r($stm->errorInfo(), true));
            }
            return $ok;
        } catch(Exception $e){
            error_log("mdetsal::save() exception: " . $e->getMessage());
            return false;
        }
    }

    // Eliminar
    public function del(){
        try {
            $sql = "DELETE FROM detsalida WHERE iddsal=:iddsal";
            $cn = (new conexion())->get_conexion();
            $stm = $cn->prepare($sql);
            $stm->bindParam(":iddsal", $this->iddet);
            return $stm->execute();
        } catch(Exception $e){
            return false;
        }
    }
}
?>
