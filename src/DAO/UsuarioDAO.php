<?php

namespace App\DAO;

use PDO;
use App\Modelo\Usuario;

class UsuarioDAO {

    /**
     * @var $bd Conexión a la Base de Datos
     */
    private PDO $bd;

    /**
     * Constructor de la clase UsuarioDAO
     * 
     * @param PDO $bd Conexión a la base de datos
     * 
     * @returns UsuarioDAO
     */
    public function __construct(PDO $bd) {
        $this->bd = $bd;
    }

    public function crea(Usuario $usuario): bool {
        $sql = "INSERT INTO usuarios (nombre, clave, email) values(:nombre, :pwd, :email)";
        $sth = $this->bd->prepare($sql);
        $resultado = $sth->execute([":nombre" => $usuario->getNombre(),
            ":pwd" => $usuario->getPwd(),
            ":email" => $usuario->getEmail()]);
        return $resultado;
    }

    public function modifica(Usuario $usuario) {
        $sql = "UPDATE usuarios SET nombre = :nombre, clave = :pwd, email = :email";
        $sth = $this->bd->prepare($sql);
        $resultado = $sth->execute([":nombre" => $usuario->getNombre(),
            ":pwd" => $usuario->getPwd(),
            ":email" => $usuario->getEmail()]);
        return $resultado;
    }

    public function elimina(int $id) {
        $sql = "DELETE FROM usuarios WHERE id = :id";
        $sth = $this->bd->prepare($sql);
        $resultado = $sth->execute([":id" => $id]);
        return ($resultado);
    }

    /**
     * Recupera un objeto usuario dado su nombre de usuario y pwd
     * 
     * @param string $nombre Nombre de usuario
     * @param string $pwd pwd del usuario
     * 
     * @returns Usuario que corresponde a ese nombre y pwd o null en caso contrario
     */
    public function recuperaPorCredencial(string $nombre, string $pwd): ?Usuario {
        //$this->bd->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
        $sql = 'SELECT * FROM usuarios WHERE nombre=:nombre and clave=:pwd';
        $sth = $this->bd->prepare($sql);
        $sth->execute([":nombre" => $nombre, ":pwd" => $pwd]);
        $sth->setFetchMode(PDO::FETCH_CLASS, Usuario::class);
        $usuario = $sth->fetch();
        return ($usuario ?: null);
    }

    public function recuperaPorNombre(string $nombre): ?Usuario {
        //$this->bd->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
        $sql = 'SELECT * FROM usuarios WHERE nombre=:nombre';
        $sth = $this->bd->prepare($sql);
        $sth->execute([":nombre" => $nombre]);
        $sth->setFetchMode(PDO::FETCH_CLASS, Usuario::class);
        $usuario = $sth->fetch();
        return ($usuario ?: null);
    }

    function recuperaTodo() {
        $this->bd->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
        $sql = "SELECT * FROM usuarios ORDER BY nombre";
        $sth = $this->bd->prepare($sql);
        $sth->execute();
        $sth->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, Usuario::class);
        $usuarios = $sth->fetchAll();
        return $usuarios;
    }

    function creaRol() {
        $sql = "ALTER TABLE usuarios ADD COLUMN rol VARCHAR(20)";
        $sth = $this->bd->prepare($sql);
        $resultado = $sth->execute();
        return $resultado;
    }
}
