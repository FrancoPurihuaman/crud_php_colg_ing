<?php namespace App\Libraries;

/**
 * Funciones basicas para un modelo
 * (guardar, listar, actualizar, eliminar, listar uniendo tablas)
 * 
 * Autor: Franco Purihuaman
 * Licencia:
 */

use App\Libraries\DataBase;

class Model {

    protected $table;                           //Esta propiedad debe ser redefinida obligatoriamente
    protected $tableAlias = "";                 //Esta propiedad puede ser redefiida para formar JOINS con alias
    protected $fields;                          //Campos de tabla. El valor de esta propiedad se asigna automaticamente
    protected $protectedFields = [];            //Esta propiedad puede ser redefinida, para impedir que se muestren dichos campos
    protected $oDataBase;                       //Objeto de la clase 'DataBase'

    // Propiedades auxiliares
    protected $sql_statement = "";
    protected $sql_restriction = "";
    protected $sql_join = "";
    protected $values_to_link = [];
    protected $valueName_to_link = 'bindParam';
    protected $valueNumber_to_link = 0;

    public function __construct(){
        $this->oDataBase = new DataBase;
        $this->fields = $this->getFieldsDatabase();
    }

    /**
     * Obtener el nombre de la tabla
     * 
     * @return string nombre de tabla asociada
     */
    public function getTable(){
        return $this->table;
    }

    /**
     * Obtener los campos de la tabla
     * 
     * @return array campos de tabla asociada
     */
    public function getFields(){
        return $this->fields;
    }

    /**
     * Este método puede ser usado para limpiar los campos auxiliares
     */
    private function cleanAuxiliaryProperties(){
        $this->sql_statement = "";
        $this->sql_restriction = "";
        $this->sql_join = "";
        $this->values_to_link = [];
        $this->valueNumber_to_link = 0;
    }

    /**
     * Este método puede ser usado para construir una consulta SQL con la cláusula SELECT
     * 
     * Para obtener los registros ejecutar el método 'get()'
     * Para agregar restricciones de consulta ejecutar el metodo WHERE u otros según se requiera
     * NOTA:    Cuando este método es usado en conbinación con el método JOIN, se debe especificar
     *          los campos a selecionar con el alias de la tabla. ['ALIAS.FIELD_1', 'ALIAS.FIELD_2']
     * 
     * @param array Campos a mostrar, sino se especifica se obtendran todos los campos (a excepción de los protegidos)
     * @param boolean Este parametro indica si se mostraran los campos protegidos
     * @return object $this
     */
    public function select($fieldsToShow = [], $showProtectedFields = false)
    {
        //Filtrar campos protegidos
        $fieldsToShow =  $this->getSqlFieldsString($showProtectedFields, $fieldsToShow);

        $this->sql_statement = "SELECT {$fieldsToShow} FROM {$this->table} ";
        return $this;
    }

    /**
     * Este método puede ser usado para ejecutar una sentencia SELECT y obtener los registros
     * 
     * @return array arreglo de registros obtenidos de la base de datos
     */
    public function get()
    {
        if(!empty($this->sql_join))
        {
            $this->sql_statement .= " {$this->tableAlias} {$this->sql_join} ";
        }
        if(!empty($this->sql_restriction))
        {
            $this->sql_statement .= " {$this->sql_restriction} ";
        }

        $this->oDataBase->prepare($this->sql_statement);

        // Vincular valores
        if(!empty($this->values_to_link)){
            foreach($this->values_to_link as $key => $value){
                $this->oDataBase->bindValue(":{$key}", $value);
            }
        }
        
        $resultSet = $this->oDataBase->fetchAll();
        $this->oDataBase->close();

        //Limpiar campos auxiliares
        $this->cleanAuxiliaryProperties();

        return $resultSet;
    }


    /**
     * Este método puede ser usado para ejecutar una sentencia SELECT
     * y obtener el primer registro que coincida con el criterio de búsqueda
     * 
     * @return mixed (arreglo u objeto según lo configurado en el controlado de base de datos)
    *                  registro obtenido de la base de datos
     */
    public function first(){
        $resultSet = $this->get();
        if(!empty($resultSet) ){
            $resultSet = $resultSet[0];
        }
        return $resultSet;
    }


    /**
     * Guarda un registro
     * 
     * @param array Arreglo asociativo ['campo' => 'valor']
     * @return boolean 'true' si guaró con éxito
     */
    public function insert($data)
    {
        $this->sql_statement = "INSERT INTO {$this->table} ";
        
        $fields = "";
        $linkedFields = "";
        foreach($data as $key => $value){
            $fields .=  ", {$key}";

            $this->valueNumber_to_link++;
            $linkedFields .= ", :{$this->valueName_to_link}_{$this->valueNumber_to_link}";
            $this->values_to_link["{$this->valueName_to_link}_{$this->valueNumber_to_link}"] = $value;
        }

        //Eliminar la coma inicial
        $fields =  substr($fields, 1);
        $linkedFields = substr($linkedFields, 1);
        
        $this->sql_statement .= " ({$fields}) VALUES ({$linkedFields})";
        
        $this->oDataBase->prepare($this->sql_statement);

        // Vincular valores
        foreach($this->values_to_link as $key => $value){
            $this->oDataBase->bindValue(":{$key}", $value);
        }
        
        $success = $this->oDataBase->execute();
        $this->oDataBase->close();

        //Limpiar campos auxiliares
        $this->cleanAuxiliaryProperties();

        if($success){
            return true;
        }else{
            return false;
        }
    }


    /**
     * Actualiza un registro
     * 
     * Puede restringir la consulta UPDATE con el método 'where'
     * NOTA: Asegurese de ejecutar primero el método 'where' y luego 'update'
     * 
     * @param array Arreglo asociativo ['campo' => 'valor']
     * @return boolean 'true' si actulizó con éxito
     */
    public function update($data)
    {
        $this->sql_statement = "UPDATE {$this->table} SET ";

        //Agregando campos
        $fields = "";
        foreach($data as $key => $value){
            $this->valueNumber_to_link++;
            $fields .=  ", {$key} = :{$this->valueName_to_link}_{$this->valueNumber_to_link}";
            $this->values_to_link["{$this->valueName_to_link}_{$this->valueNumber_to_link}"] = $value;
        }
        //Eliminar la primera coma
        $fields = substr($fields, 1);

        $this->sql_statement .= " {$fields} ";

        //Agregar restricciones
        if(!empty($this->sql_restriction))
        {
            $this->sql_statement .= " {$this->sql_restriction} ";
        }
        
        $this->oDataBase->prepare($this->sql_statement);

        // Vincular condiciones
        if(!empty($this->values_to_link))
        {
            foreach($this->values_to_link as $key => $value)
            {
                $this->oDataBase->bindValue(":{$key}", $value);
            }
        }
        
        $success = $this->oDataBase->execute();
        $this->oDataBase->close();

        //Limpiar campos auxiliares
        $this->cleanAuxiliaryProperties();

        if($success){
            return true;
        }else{
            return false;
        }
        
    }

    /**
     * Elimina un registro
     * 
     * Puede restringir la consulta DELETE con el método 'where'
     * NOTA: Asegurese de ejecutar primero el método 'where' y luego 'delete'
     * 
     * @return boolean 'true' si el registro se eliminó
     */
    public function delete()
    {
        $this->sql_statement = "DELETE FROM {$this->table} ";
        
        //Agregar restricciones
        if(!empty($this->sql_restriction))
        {
            $this->sql_statement .= " {$this->sql_restriction} ";
        }
        
        $this->oDataBase->prepare($this->sql_statement);
        
        // Vincular condiciones
        if(!empty($this->values_to_link))
        {
            foreach($this->values_to_link as $key => $value)
            {
                $this->oDataBase->bindValue(":{$key}", $value);
            }
        }
        
        $success = $this->oDataBase->execute();
        $this->oDataBase->close();

        //Limpiar campos auxiliares
        $this->cleanAuxiliaryProperties();

        if($success){
            return true;
        }else{
            return false;
        }
    }


    /**
     * Este método puede ser usado para unir tablas con la cláusula INNER JOIN
     * 
     * @param string nombre de tabla a unir
     * @param string nombre de campo de union de la primera tabla
     * @param string operador de union. '='
     * @param string nombre de campo de union de la segunda tabla
     * @param object $this
     */
    public function join($table, $unionField_1, $operator, $unionField_2)
    {
        $this->sql_join .= " INNER JOIN {$table} ON {$unionField_1} {$operator} {$unionField_2} ";
        return $this;
    }



    // ----------------------- CONDICIONES DE SELECCIÓN-----------------------


    /**
     * Este método puede ser usado para añadir una condición con la cláusula WHERE a la consulta
     * 
     * Si ya existe la cláusula WHERE en la consulta SQL se concatenará con la cláusula AND
     * @param string nombre del campo
     * @param string operador condicional. =, >, >=, <, <=, <>
     * @param mixed (string o numeric). Valor a evaluar contra la columna
     * @return object $this
     */
    public function where($field, $operator, $value)
    {
        $union = empty($this->sql_restriction) ? 'WHERE' : 'AND';
        //si una condición va al inicio de un grupo de condiciones no se agrega la clausula de unión
        $union = $this->checkStartRestrictionsGroup() ? '' : $union;

        $this->valueNumber_to_link++;
        $this->sql_restriction .= " {$union} {$field} {$operator} :{$this->valueName_to_link}_{$this->valueNumber_to_link} ";
        $this->values_to_link["{$this->valueName_to_link}_{$this->valueNumber_to_link}"] = $value;
        return $this;
    }

    /**
     * Este método puede ser usado para añadir condiciones con la cláusula WHERE a la consulta
     * pasando un arreglo de condiciones
     * 
     * Si ya existe la cláusula WHERE en la consulta SQL las condiciones se concatenarán
     * con la cláusula AND
     * @param array condiciones de consulta. [[field_1, condition_1, $value_1], 
     *                                          [field_2, condition_2, $value_2]]
     * @return object $this
     */
    public function whereArray($arrayConditions)
    {
        $union = empty($this->sql_restriction) ? 'WHERE' : 'AND';
        //si una condición va al inicio de un grupo de condiciones no se agrega la clausula de unión
        $union = $this->checkStartRestrictionsGroup() ? '' : $union;

        foreach($arrayConditions as $condition){
            $this->valueNumber_to_link++;
            $this->sql_restriction .= " {$union} {$condition[0]} {$condition[1]} :{$this->valueName_to_link}_{$this->valueNumber_to_link} ";
            $this->values_to_link["{$this->valueName_to_link}_{$this->valueNumber_to_link}"] = $condition[2];
            $union = 'AND';
        }
        return $this;
    }

    /**
     * Este método puede ser usado para añadir una condición con la cláusula WHERE a la consulta
     * 
     * Se concatenará a la sentencia SQL con la cláusula OR
     * @param string nombre del campo
     * @param string operador condicional. =, >, >=, <, <=, <>
     * @param mixed (string o numeric). Valor a evaluar contra la columna
     * @return object $this
     */
    public function orWhere($field, $operator, $value)
    {
        $this->valueNumber_to_link++;
        $this->sql_restriction .= " OR {$field} {$operator} :{$this->valueName_to_link}_{$this->valueNumber_to_link} ";
        $this->values_to_link["{$this->valueName_to_link}_{$this->valueNumber_to_link}"] = $value;
        
        return $this;
    }

    /**
     * Este método puede ser usado para añadir condiciones a con la cláusula WHERE a la consulta
     * pasando un arreglo de condiciones
     * 
     * Si ya existe la cláusula WHERE en la consulta SQL las condiciones se concatenarán
     * con la cláusula OR
     * @param array condiciones de consulta. [[field_1, condition_1, $value_1], 
     *                                          [field_2, condition_2, $value_2]]
     * @return object $this
     */
    public function orWhereArray($arrayConditions)
    {
        $union = empty($this->sql_restriction) ? 'WHERE' : 'OR';
        foreach($arrayConditions as $condition){
            $this->valueNumber_to_link++;
            $this->sql_restriction .= " {$union} {$condition[0]} {$condition[1]} :{$this->valueName_to_link}_{$this->valueNumber_to_link} ";
            $this->values_to_link["{$this->valueName_to_link}_{$this->valueNumber_to_link}"] = $condition[2];
            $union = 'OR';
        }
        return $this;
    }

    /**
     * Este método verifica que un valor de columna esté en un intervalo de valores
     * 
     * Si ya existe la cláusula WHERE en la consulta SQL se concatenará con la cláusula AND
     * @param string nombre del campo
     * @param array arreglo con dos valores. [value_1, value_2]
     * @return object $this
     * 
     */
    public function whereBetween($field, $values)
    {
        $union = empty($this->sql_restriction) ? 'WHERE' : 'AND';
        //si una condición va al inicio de un grupo de condiciones no se agrega la clausula de unión
        $union = $this->checkStartRestrictionsGroup() ? '' : $union;

        $this->sql_restriction .= " {$union} {$field} {$this->between($values)} "; 
        return $this;
    }

    /**
     * Este método verifica que un valor de columna esté en un intervalo de valores
     * 
     * Se concatenará a la sentencia SQL con la cláusula OR
     * @param string nombre del campo
     * @param array arreglo con dos valores. [value_1, value_2]
     * @return object $this
     */
    public function orWhereBetween($field, $values)
    {
        $this->sql_restriction .= " OR {$field} {$this->between($values)} "; 
        return $this;
    }


    /**
     * Este método verifica que un valor de columna no esté en un intervalo de valores
     * 
     * Si ya existe la cláusula WHERE en la consulta SQL se concatenará con la cláusula AND
     * @param string nombre del campo
     * @param array arreglo con dos valores. [value_1, value_2]
     * @return object $this
     */
    public function whereNotBetween($field, $values)
    {
        $union = empty($this->sql_restriction) ? 'WHERE' : 'AND';
        //si una condición va al inicio de un grupo de condiciones no se agrega la clausula de unión
        $union = $this->checkStartRestrictionsGroup() ? '' : $union;

        $this->sql_restriction .= " {$union} {$field} NOT {$this->between($values)} ";
        return $this;
    }

    /**
     * Este método verifica que un valor de columna no esté en un intervalo de valores
     * 
     * Se concatenará a la sentencia SQL con la cláusula OR
     * @param string nombre del campo
     * @param array arreglo con dos valores. [value_1, value_2]
     * @return object $this
     */
    public function orWhereNotBetween($field, $values)
    {
        $this->sql_restriction .= " OR {$field} NOT {$this->between($values)} "; 
        return $this;
    }

    /**
     * Este método construye la declaración BETWEEN con los nombre de campos preparados
     * 
     * @param string nombre del campo
     * @param array arreglo con dos valores. [value_1, value_2]
     * @return string condicional between. BETWEEN :campo_1 AND :campo_2
     */
    private function between($values)
    {
        $sql = "";

        $this->valueNumber_to_link++;
        $sql .= " BETWEEN :{$this->valueName_to_link}_{$this->valueNumber_to_link} ";
        $this->values_to_link["{$this->valueName_to_link}_{$this->valueNumber_to_link}"] = $values[0];

        $this->valueNumber_to_link++;
        $sql .= " AND :{$this->valueName_to_link}_{$this->valueNumber_to_link} ";
        $this->values_to_link["{$this->valueName_to_link}_{$this->valueNumber_to_link}"] = $values[1];

        return $sql;
    }

    /**
     * Este método verifica que un valor de columna dada esté contenido dentro del arreglo dado
     * 
     * Si ya existe la cláusula WHERE en la consulta SQL se concatenará con la cláusula AND
     * @param string nombre de campo
     * @param array arreglo de valores.
     * @return object $this
     */
    public function whereIn($field, $values)
    {
        $union = empty($this->sql_restriction) ? 'WHERE' : 'AND';
        //si una condición va al inicio de un grupo de condiciones no se agrega la clausula de unión
        $union = $this->checkStartRestrictionsGroup() ? '' : $union;

        $this->sql_restriction .= " {$union} {$field} {$this->in($values)} ";
        return $this;
    }

    /**
     * Este método verifica que un valor de columna dada no esté contenido dentro del arreglo dado
     * 
     * Si ya existe la cláusula WHERE en la consulta SQL se concatenará con la cláusula AND
     * @param string nombre de campo
     * @param array arreglo de valores.
     * @return object $this
     */
    public function whereNotIn($field, $values)
    {
        $union = empty($this->sql_restriction) ? 'WHERE' : 'AND';
        //si una condición va al inicio de un grupo de condiciones no se agrega la clausula de unión
        $union = $this->checkStartRestrictionsGroup() ? '' : $union;

        $this->sql_restriction .= " {$union} {$field} NOT {$this->in($values)} ";
        return $this;
    }

    /**
     * Este método verifica que un valor de columna dada esté contenido dentro del arreglo dado
     * 
     * Se concatenará a la sentencia SQL con la cláusula OR
     * @param string nombre de campo
     * @param array arreglo de valores.
     * @return object $this
     */
    public function orWhereIn($field, $values)
    {
        $this->sql_restriction .= " OR {$field} {$this->in($values)} ";
        return $this;
    }

    /**
     * Este método verifica que un valor de columna dada no esté contenido dentro del arreglo dado
     * 
     * Se concatenará a la sentencia SQL con la cláusula OR
     * @param string nombre de campo
     * @param array arreglo de valores.
     * @return object $this
     */
    public function orWhereNotIn($field, $values)
    {
        $this->sql_restriction .= " OR {$field} NOT {$this->in($values)} ";
        return $this;
    }

    /**
     * Este método construye la declaración IN con los nombre de campos preparados
     * 
     * @param string nombre del campo
     * @param array arreglo de valores. [value_1, value_2, value_3]
     * @return string condicional in. IN (:campo_1 AND, :campo_2, :campo_3)
     */
    private function in($values)
    {
        $sql = "";
        foreach($values as $value){
            $this->valueNumber_to_link++;
            $sql .= ", :{$this->valueName_to_link}_{$this->valueNumber_to_link} ";
            $this->values_to_link["{$this->valueName_to_link}_{$this->valueNumber_to_link}"] = $value;
        }
        $sql = substr($sql,2);
        $sql = " IN ({$sql}) ";

        return $sql;
    }

    /**
     * Este método verifica que el valor de una columna dada sea igual a NULL
     * 
     * Si ya existe la cláusula WHERE en la consulta SQL se concatenará con la cláusula AND
     * @param string nombre de campo
     * @return object $this
     */
    public function whereNull($field)
    {
        $union = empty($this->sql_restriction) ? 'WHERE' : 'AND';
        //si una condición va al inicio de un grupo de condiciones no se agrega la clausula de unión
        $union = $this->checkStartRestrictionsGroup() ? '' : $union;

        $this->sql_restriction .= " {$union} {$field} IS NULL "; 
        return $this;
    }

    /**
     * Este método verifica que el valor de una columna dada no sea igual a NULL
     * 
     * Si ya existe la cláusula WHERE en la consulta SQL se concatenará con la cláusula AND
     * @param string nombre de campo
     * @return object $this
     */
    public function whereNotNull($field)
    {
        $union = empty($this->sql_restriction) ? 'WHERE' : 'AND';
        //si una condición va al inicio de un grupo de condiciones no se agrega la clausula de unión
        $union = $this->checkStartRestrictionsGroup() ? '' : $union;

        $this->sql_restriction .= " {$union} {$field} IS NOT NULL "; 
        return $this;
    }

    /**
     * Este método verifica que el valor de una columna dada sea igual a NULL
     * 
     * Se concatenará a la sentencia SQL con la cláusula OR
     * @param string nombre de campo
     * @return object $this
     */
    public function orWhereNull($field)
    {
        $this->sql_restriction .= " OR {$field} IS NULL "; 
        return $this;
    }

    /**
     * Este método verifica que el valor de una columna dada no sea igual a NULL
     * 
     * Se concatenará a la sentencia SQL con la cláusula OR
     * @param string nombre de campo
     * @return object $this
     */
    public function orWhereNotNull($field)
    {
        $this->sql_restriction .= " OR {$field} IS NOT NULL "; 
        return $this;
    }



    //---------------------- PROPIAS DE MYSQL -----------------------------
    
    /**
     * Este método puede ser usado para comparar el valor de una columna contra una fecha
     * 
     * Si ya existe la cláusula WHERE en la consulta SQL se concatenará con la cláusula AND
     * @param string nombre de campo
     * @param string operador condicional. =, >, >=, <, <=, <>
     * @param string Fecha a comparar. formato 'yyyy-mm-dd'
     * @return object this
     */
    public function whereDate($field, $operator, $date)
    {
        $union = empty($this->sql_restriction) ? 'WHERE' : 'AND';
        //si una condición va al inicio de un grupo de condiciones no se agrega la clausula de unión
        $union = $this->checkStartRestrictionsGroup() ? '' : $union;

        $this->valueNumber_to_link++;
        $this->sql_restriction .= " {$union} DATE({$field}) {$operator} ':{$this->valueName_to_link}_{$this->valueNumber_to_link}' ";
        $this->values_to_link["{$this->valueName_to_link}_{$this->valueNumber_to_link}"] = $date;
        return $this;
    }

    /**
     * Este método puede ser usado para comparar el valor de una columna contra un año especifico
     * 
     * Si ya existe la cláusula WHERE en la consulta SQL se concatenará con la cláusula AND
     * @param string nombre de campo
     * @param string año a comparar. '2020'
     * @return object $this
     */
    public function whereYear($field, $year)
    {
        $union = empty($this->sql_restriction) ? 'WHERE' : 'AND';
        //si una condición va al inicio de un grupo de condiciones no se agrega la clausula de unión
        $union = $this->checkStartRestrictionsGroup() ? '' : $union;

        $this->valueNumber_to_link++;
        $this->sql_restriction .= " {$union} YEAR({$field}) = ':{$this->valueName_to_link}_{$this->valueNumber_to_link}' ";
        $this->values_to_link["{$this->valueName_to_link}_{$this->valueNumber_to_link}"] = $year;
        return $this;
    }

    /**
     * Este método puede ser usado para comparar el valor de una columna contra un mes especifico de un año
     * 
     * Si ya existe la cláusula WHERE en la consulta SQL se concatenará con la cláusula AND
     * @param string nombre de campo
     * @param string número de mes a comparar. '2'
     * @return object $this
     */
    public function whereMonth($field, $month)
    {
        $union = empty($this->sql_restriction) ? 'WHERE' : 'AND';
        //si una condición va al inicio de un grupo de condiciones no se agrega la clausula de unión
        $union = $this->checkStartRestrictionsGroup() ? '' : $union;

        $this->valueNumber_to_link++;
        $this->sql_restriction .= " {$union} MONTH({$field}) = ':{$this->valueName_to_link}_{$this->valueNumber_to_link}' ";
        $this->values_to_link["{$this->valueName_to_link}_{$this->valueNumber_to_link}"] = $month;
        return $this;
    }

    /**
     * Este método puede ser usado para comparar el valor de una columna contra un día especifico de un mes
     * 
     * SSi ya existe la cláusula WHERE en la consulta SQL se concatenará con la cláusula AND
     * @param string nombre de campo
     * @param string número de día a comparar. '5'
     * @return object $this
     */
    public function whereDay($field, $day)
    {
        $union = empty($this->sql_restriction) ? 'WHERE' : 'AND';
        //si una condición va al inicio de un grupo de condiciones no se agrega la clausula de unión
        $union = $this->checkStartRestrictionsGroup() ? '' : $union;

        $this->valueNumber_to_link++;
        $this->sql_restriction .= " {$union} MONTH({$field}) = ':{$this->valueName_to_link}_{$this->valueNumber_to_link}' ";
        $this->values_to_link["{$this->valueName_to_link}_{$this->valueNumber_to_link}"] = $day;
        return $this;
    }

    //------------------- FIN FUNCIONES PROPIAS DE MYSQL---------------------

    
    /**
     * Este método puede ser usado para comparar el valor de dos columnas
     * 
     * Si ya existe la cláusula WHERE en la consulta SQL se concatenará con la cláusula AND
     * @param string nombre del primer campo
     * @param string operador condicional. =, >, >=, <, <=, <>
     * @param string nombre del segundo campo
     * @return object $this
     */
    public function whereColumn($field_1, $operator, $field_2)
    {
        $union = empty($this->sql_restriction) ? 'WHERE' : 'AND';
        //si una condición va al inicio de un grupo de condiciones no se agrega la clausula de unión
        $union = $this->checkStartRestrictionsGroup() ? '' : $union;

        $this->sql_restriction .= " {$union} {$field_1} {$operator} {$field_2} ";
        return $this;
    }


    /**
     * Este método puede ser usado para comparar el valor de dos columnas
     * 
     * Se concatenará a la sentencia SQL con la cláusula OR
     * @param string nombre del primer campo
     * @param string operador condicional. =, >, >=, <, <=, <>
     * @param string nombre del segundo campo
     * @return object $this
     */
    public function orWhereColumn($field_1, $operator, $field_2)
    {
        $this->sql_restriction .= " OR {$field_1} {$operator} {$field_2} ";
        return $this;
    }

    /**
     * Este método puede ser usado para iniciar un grupo de restricciones
     * con la cláusula AND
     * 
     * NOTA: Recuerda finalizar un grupo de restricciones con el método 'endWhereGroup()'
     * 
     * @return object $this
     */
    public function groupRestrictions()
    {
        $this->sql_restriction = " {$this->sql_restriction} AND (";
        return $this;
    }

    /**
     * Este método puede ser usado para iniciar un grupo de restricciones
     * con la cláusula OR
     * 
     * NOTA: Recuerda finalizar un grupo de restricciones con el método 'endWhereGroup()'
     * 
     * @return object $this
     */
    public function orGroupRestrictions()
    {
        $this->sql_restriction = " {$this->sql_restriction} OR (";
        return $this;
    }


    /**
     * Este método puede ser usado para finalizar un grupo de restricciones
     * 
     * @return object $this
     */
    public function endGroupRestrictions()
    {
        $this->sql_restriction = " {$this->sql_restriction}) ";
        return $this;
    }


    /**
     * Este método puede ser usado para verificar si una condición se concatenará
     * al inicio de un grupo de condiciones. AND (field_1 = value_1 ...
     * 
     * @return object $this
     */
    private function checkStartRestrictionsGroup()
    {
        $checked = false;
        $sql = trim($this->sql_restriction);
        $finalCharacter = substr($sql, -1);
        if($finalCharacter == "("){
            $checked = true;
        }
        return $checked;
    }


    /**
     * Este método puede ser usado para agrupar los resultados de consulta.
     * 
     * @param array arreglo de nombres de campos
     * @return object $this
     */
    public function groupBy($arrayFields){
        $sqlFields = "";
        foreach($arrayFields as $field){
            $sqlFields .= ", {$field} ";
        }
        //Eliminar la primera coma
        $sqlFields = substr($sqlFields, 1);

        $this->sql_restriction .= $sqlFields;
        return $this;
    }


    /**
     * Este método puede ser usado para agregar una condición de grupo con
     * la cláusula HAVING a la consulta
     * 
     * Si ya existe la cláusula WHERE en la consulta SQL se concatenará con la cláusula AND
     * @param string nombre del campo de la función de grupo. AVG(salary)
     * @param string operador de comparación. =, >, >=, <, <=, <>
     * @return object $this
     */
    public function having($groupFunction, $operator, $value)
    {
        $union = strpos($this->sql_restriction, 'HAVING') === false ? 'HAVING' : 'AND';
        //si una condición va al inicio de un grupo de condiciones no se agrega la clausula de unión
        $union = $this->checkStartRestrictionsGroup() ? '' : $union;

        $this->valueNumber_to_link++;
        $this->sql_restriction .= " {$union} {$groupFunction} {$operator} :{$this->valueName_to_link}_{$this->valueNumber_to_link} ";
        $this->values_to_link["{$this->valueName_to_link}_{$this->valueNumber_to_link}"] = $value;

        return $this;
    }


    /**
     * Este método puede ser usado para agregar una condición de grupo con
     * la cláusula HAVING a la consulta
     * 
     * Se concatenará a la sentencia SQL con la cláusula OR
     * @param string nombre del campo de la función de grupo. AVG(salary)
     * @param string operador de comparación. =, >, >=, <, <=, <>
     * @return object $this
     */
    public function orHaving($groupFunction, $operator, $value)
    {
        $this->valueNumber_to_link++;
        $this->sql_restriction .= " OR {$groupFunction} {$operator} :{$this->valueName_to_link}_{$this->valueNumber_to_link} ";
        $this->values_to_link["{$this->valueName_to_link}_{$this->valueNumber_to_link}"] = $value;
        return $this;
    }

    /**
     * Este método puede ser usado para comparar el valor de dos columnas de funciones de grupo
     * 
     * Si ya existe la cláusula HAVING en la consulta SQL se concatenará con la cláusula AND
     * @param string nombre del primer campo de función de grupo
     * @param string operador condicional. =, >, >=, <, <=, <>
     * @param string nombre del segundo campo de función de grupo
     * @return object $this
     */
    public function havingColumn($groupFunction_1, $operator, $groupFunction_2)
    {
        $union = strpos($this->sql_restriction, 'HAVING') === false ? 'HAVING' : 'AND';
        //si una condición va al inicio de un grupo de condiciones no se agrega la clausula de unión
        $union = $this->checkStartRestrictionsGroup() ? '' : $union;

        $this->sql_restriction .= " {$union} {$groupFunction_1} {$operator} {$groupFunction_2} ";
        return $this;
    }


    /**
     * Este método puede ser usado para comparar el valor de dos columnas de funciones de grupo
     * 
     * Si ya existe la cláusula HAVING en la consulta SQL se concatenará con la cláusula OR
     * @param string nombre del primer campo de función de grupo
     * @param string operador condicional. =, >, >=, <, <=, <>
     * @param string nombre del segundo campo de función de grupo
     * @return object $this
     */
    public function orHavingColumn($groupFunction_1, $operator, $groupFunction_2)
    {
        $this->sql_restriction .= " OR {$groupFunction_1} {$operator} {$groupFunction_2} ";
        return $this;
    }


    /**
     * Este método puede ser usado para ordenar los resultados de la consulta 
     * por una columna dada
     * 
     * @param string nombre de campo
     * @param string dirección de ordenamiento. ASC o DESC
     * @return object $this
     */
    public function orderBy($field, $direction = 'ASC')
    {   
        $this->sql_restriction .= " ORDER BY {$field} {$direction} ";
        return $this;
    }


    /**
     * Este método puede ser usado para hacer un desplazamiento y
     * limitar la cantidad de registros a selecionar.
     * 
     * @param string desplazamiento
     * @param string cantidad de registros a seleccionar
     * @return object $this
     */
    public function limit($displasment, $amount){
        $this->sql_restriction .= " LIMIT {$displasment},{$amount} ";
        return $this;
    }

    // --------------------- FIN CONDICIONES DE SELECCIÓN-----------------------



    //--------------------- FUNCIONES DE AYUDA --------------------------------

    /**
     * Filtrar campos protegidos
     * 
     * @param array arreglo de campos ['campo_1', 'campo_2', 'campo_3'], sino se asigna se obtendran todos los campos (excepto los protegidos)
     * @return array arreglo de campos filtrados 
     */
    private function filterFieldstoShow($fields = [])
    {
        $fields = empty($fields)? $this->fields: $fields;

        $filteredFields = [];
        if(!empty($this->protectedFields)){
            foreach($fields as $field){
                $field = trim($field);
                $allowedField = true;
                foreach($this->protectedFields as $protectdField){
                    $protectdField = trim($protectdField);
                    if($field == $protectdField){
                        $allowedField = false;
                        break;
                    }
                }

                if($allowedField){
                    $filteredFieldsFields[] = $field;
                }
            }
        }else{
            $filteredFields = $fields;
        }

        return $filteredFields;
    }

    /**
     * Obtener cadena de campos para una sentencia 'SELECT'
     * 
     * @param boolean TRUE, si los campos protegidos se mostraran de lo contrario FALSE
     * @param array arreglo de campos ['campo_1', 'campo_2', 'campo_3'], sino se asigna se obtendran todos los campos (excepto los protegidos)
     * @return string cadena de campos "campo_1, campo_2, campo_3"
     */
    private function getSqlFieldsString($showProtectedFields, $fields = [])
    {
        $sqlFields = '';
        $fields = empty($fields)? $this->fields: $fields;
        
        if($showProtectedFields){
            $fields = $this->filterFieldstoShow($fields);
        }

        foreach($fields as $field){
            $sqlFields .= ", {$field}";
        }

        $sqlFields = substr($sqlFields, 2);
        $sqlFields = " {$sqlFields} ";

        return $sqlFields;
    }


    /**
     * Obtener todos los campos de una tabla
     * 
     * Sino se pasa la tabla, se obtendra los campos de la tabla del modelo instanciado
     * 
     * @param string tabla de la cual se obtendran sus campos
     * @return array arreglo de campos
     */
    private function getFieldsDatabase($table = '')
    {
        $table = empty($table)?$this->table : $table;
        $fieldsDatabase = [];
        $sql = "SHOW COLUMNS FROM ".DATABASE['database'].".".$table;
        $auxFields = $this->oDataBase->query($sql);
        foreach($auxFields as $auxField) {
            $fieldsDatabase[] = $auxField->Field;
        }
        return $fieldsDatabase;
    }

    //--------------------- FIN FUNCIONES DE AYUDA ----------------------------
}