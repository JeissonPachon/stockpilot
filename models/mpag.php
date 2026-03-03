<?php
require_once('conexion.php');

class Mpag {
    private $idpag;
    private $idmod;
    private $nompag;
    private $ruta;
    private $icono;
    private $orden;
    private $fec_crea;
    private $fec_actu;
    private $act;

    // Getters
    function getIdpag(){
        return $this->idpag;
    }
    function getIdmod(){
        return $this->idmod;
    }
    function getNompag(){
        return $this->nompag;
    }
    function getRuta(){
        return $this->ruta;
    }
    function getIcono(){
        return $this->icono;
    }
    function getOrden(){
        return $this->orden;
    }
    function getFec_crea(){
        return $this->fec_crea;
    }
    function getFec_actu(){
        return $this->fec_actu;
    }
    function getAct(){
        return $this->act;
    }

    // Setters
    function setIdpag($idpag){
        $this->idpag = $idpag;
    }
    function setIdmod($idmod){
        $this->idmod = $idmod;
    }
    function setNompag($nompag){
        $this->nompag = $nompag;
    }
    function setRuta($ruta){
        $this->ruta = $ruta;
    }
    function setIcono($icono){
        $this->icono = $icono;
    }
    function setOrden($orden){
        $this->orden = $orden;
    }
    function setFec_crea($fec_crea){
        $this->fec_crea = $fec_crea;
    }
    function setFec_actu($fec_actu){
        $this->fec_actu = $fec_actu;
    }
    function setAct($act){
        $this->act = $act;
    }

    public function getAll(){
        try{
            $sql = "SELECT idpag, idmod, nompag, ruta, icono, orden, fec_crea, fec_actu, act FROM pagina";
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            $res = $result->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        }catch(Exception $e){
            echo "Error: ".$e->getMessage()."<br><br>";
        }
    }

    public function getOne(){
        try{
            $sql = "SELECT idpag, idmod, nompag, ruta, icono, orden, fec_crea, fec_actu, act FROM pagina WHERE idpag = :idpag";
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idpag = $this->getIdpag();
            $result->bindParam(':idpag', $idpag);
            $result->execute();
            $res = $result->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        }catch(Exception $e){
            echo "Error: ".$e->getMessage()."<br><br>";
        }
    }

    public function save(){
        try{
            $sql = "INSERT INTO pagina (idmod, nompag, ruta, icono, orden, fec_crea, fec_actu, act) 
                    VALUES (:idmod, :nompag, :ruta, :icono, :orden, :fec_crea, :fec_actu, :act)";
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);

            $idmod = $this->getIdmod();
            $nompag = $this->getNompag();
            $ruta = $this->getRuta();
            $icono = $this->getIcono();
            $orden = $this->getOrden();
            $fec_crea = $this->getFec_crea();
            $fec_actu = $this->getFec_actu();
            $act = $this->getAct();

            $result->bindParam(':idmod', $idmod);
            $result->bindParam(':nompag', $nompag);
            $result->bindParam(':ruta', $ruta);
            $result->bindParam(':icono', $icono);
            $result->bindParam(':orden', $orden);
            $result->bindParam(':fec_crea', $fec_crea);
            $result->bindParam(':fec_actu', $fec_actu);
            $result->bindParam(':act', $act);
            $result->execute();
        }catch(Exception $e){
            echo "Error: ".$e->getMessage()."<br><br>";
        }
    }

    public function edit(){
        try{
            $sql = "UPDATE pagina 
                    SET idmod = :idmod, nompag = :nompag, ruta = :ruta, icono = :icono, 
                        orden = :orden, fec_crea = :fec_crea, fec_actu = :fec_actu, act = :act 
                    WHERE idpag = :idpag";
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);

            $idpag = $this->getIdpag();
            $idmod = $this->getIdmod();
            $nompag = $this->getNompag();
            $ruta = $this->getRuta();
            $icono = $this->getIcono();
            $orden = $this->getOrden();
            $fec_crea = $this->getFec_crea();
            $fec_actu = $this->getFec_actu();
            $act = $this->getAct();

            $result->bindParam(':idpag', $idpag);
            $result->bindParam(':idmod', $idmod);
            $result->bindParam(':nompag', $nompag);
            $result->bindParam(':ruta', $ruta);
            $result->bindParam(':icono', $icono);
            $result->bindParam(':orden', $orden);
            $result->bindParam(':fec_crea', $fec_crea);
            $result->bindParam(':fec_actu', $fec_actu);
            $result->bindParam(':act', $act);
            $result->execute();
        }catch(Exception $e){
            echo "Error: ".$e->getMessage()."<br><br>";
        }
    }

    public function del(){
        try{
            $sql = "DELETE FROM pagina WHERE idpag = :idpag";
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idpag = $this->getIdpag();
            $result->bindParam(':idpag', $idpag);
            $result->execute();
        }catch(Exception $e){
            echo "Error: ".$e->getMessage()."<br><br>";
        }
    }

    public function getPagPadre(){
        try{
            $sql = "SELECT idpag, nompag FROM pagina WHERE idmod IS NULL AND act = 1 ORDER BY orden ASC";
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            $res = $result->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        }catch(Exception $e){
            echo "Error: ".$e->getMessage()."<br><br>";
        }
    }
}

?>
