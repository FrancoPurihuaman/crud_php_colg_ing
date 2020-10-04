<?php namespace App\Libraries;

use PDO;
use PDOException;

class DataBase{

    private $dataBaseHandle;    //Controlador de la base de datos
    private $statement;         //Objeto Statement

    public function __construct()
    {
        $host = DATABASE['host'] ?? 'localhost';
        $database = DATABASE['database'] ?? 'database';
        $user = DATABASE['username'] ?? 'root';
        $password =  DATABASE['password'] ?? 'password';
        $charset = DATABASE['charset'] ?? 'utf8';
        $dataSourceName = "mysql:host={$host};dbname={$database};charset={$charset}";

        try{
            $this->dataBaseHandle = new PDO($dataSourceName, $user, $password);
            $this->dataBaseHandle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->dataBaseHandle->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

        }catch(PDOException $e){
            echo $e->getMessage();
        }
    }

    public function getConnection(){
        return $this->dataBaseHandle;
    }

    public function query($sql){
        $this->statement = $this->dataBaseHandle->query($sql);
        return $this->statement->fetchAll();
    }

    public function prepare($sql){
        $this->statement = $this->dataBaseHandle->prepare($sql);
    }

    public function bindValue($param, $value, $type = null){
        if(is_null($type)){
            switch(true){
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }

        $this->statement->bindParam($param, $value, $type);
    }

    public function execute(){
        $succes = $this->statement->execute();
        return $succes;
    }

    public function fetchAll(){
        $this->execute();
        return $this->statement->fetchAll();
    }

    public function rowCount(){
        return $this->statement->rowCount();
    }

    public function close(){
        $this->statement->closeCursor();
        $this->dataBaseHandle = null;
    }
}
