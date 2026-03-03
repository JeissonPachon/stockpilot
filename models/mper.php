<?php
require_once('conexion.php');

class Mper {
    private $idper;
    private $nomper;
    private $ver;
    private $crear;
    private $editar;
    private $eliminar;
    private $act;

    // Getters
    function getIdper() {
        return $this->idper;
    }
    function getNomper() {
        return $this->nomper;
    }
    function getVer() {
        return $this->ver;
    }
    function getCrear() {
        return $this->crear;
    }
    function getEditar() {
        return $this->editar;
    }
    function getEliminar() {
        return $this->eliminar;
    }
    function getAct() {
        return $this->act;
    }

    // Setters
    function setIdper($idper) {
        $this->idper = $idper;
    }
    function setNomper($nomper) {
        $this->nomper = $nomper;
    }
    function setVer($ver) {
        $this->ver = $ver;
    }
    function setCrear($crear) {
        $this->crear = $crear;
    }
    function setEditar($editar) {
        $this->editar = $editar;
    }
    function setEliminar($eliminar) {
        $this->eliminar = $eliminar;
    }
    function setAct($act) {
        $this->act = $act;
    }

    // Obtener todos los registros
    public function getAll() {
        try {
            $sql = "SELECT idper, nomper, ver, crear, editar, eliminar, act FROM perfil";
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $result->execute();
            $res = $result->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "<br><br>";
        }
    }

    // Obtener un registro por ID
    public function getOne() {
        try {
            $sql = "SELECT idper, nomper, ver, crear, editar, eliminar, act 
                    FROM perfil WHERE idper = :idper";
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idper = $this->getIdper();
            $result->bindParam(':idper', $idper);
            $result->execute();
            $res = $result->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "<br><br>";
        }
    }

    // Guardar un nuevo registro
    public function save() {
        try {
            $sql = "INSERT INTO perfil (nomper, ver, crear, editar, eliminar, act) 
                    VALUES (:nomper, :ver, :crear, :editar, :eliminar, :act)";
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);

            $nomper = $this->getNomper();
            $ver = $this->getVer();
            $crear = $this->getCrear();
            $editar = $this->getEditar();
            $eliminar = $this->getEliminar();
            $act = $this->getAct();

            $result->bindParam(':nomper', $nomper);
            $result->bindParam(':ver', $ver);
            $result->bindParam(':crear', $crear);
            $result->bindParam(':editar', $editar);
            $result->bindParam(':eliminar', $eliminar);
            $result->bindParam(':act', $act);

            $result->execute();
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "<br><br>";
        }
    }

    // Editar un registro existente
    public function edit() {
        try {
            $sql = "UPDATE perfil 
                    SET nomper = :nomper, ver = :ver, crear = :crear, 
                        editar = :editar, eliminar = :eliminar, act = :act 
                    WHERE idper = :idper";
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);

            $idper = $this->getIdper();
            $nomper = $this->getNomper();
            $ver = $this->getVer();
            $crear = $this->getCrear();
            $editar = $this->getEditar();
            $eliminar = $this->getEliminar();
            $act = $this->getAct();

            $result->bindParam(':idper', $idper);
            $result->bindParam(':nomper', $nomper);
            $result->bindParam(':ver', $ver);
            $result->bindParam(':crear', $crear);
            $result->bindParam(':editar', $editar);
            $result->bindParam(':eliminar', $eliminar);
            $result->bindParam(':act', $act);

            $result->execute();
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "<br><br>";
        }
    }

    // Eliminar un registro
    public function del() {
        try {
            $sql = "DELETE FROM perfil WHERE idper = :idper";
            $modelo = new Conexion();
            $conexion = $modelo->get_conexion();
            $result = $conexion->prepare($sql);
            $idper = $this->getIdper();
            $result->bindParam(':idper', $idper);
            $result->execute();
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "<br><br>";
        }
    }
}

?>

