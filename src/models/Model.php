<?php

class Model {
    protected static $tableName = '';
    protected static $columns = [];
    protected $values = [];

    function __construct($arr){
        $this->loadFromArray($arr);
    }

    function loadFromArray($arr){
        if(isset($arr)){
            $conn = Database::getConnection();
            foreach($arr as $key => $value){
                $cleanValue = $value;
                if(isset($cleanValue)){
                    $cleanValue = strip_tags(trim($cleanValue));
                    $cleanValue = htmlspecialchars($cleanValue);
                    $cleanValue = mysqli_real_escape_string($conn, $cleanValue);
                    $cleanValue = str_replace(array("<","WHERE","where",">","=","?"), "", $cleanValue);
                }
                $this->$key = $cleanValue;
            }
            $conn->close();
        }
    }

    public function __get($name){
        return $this->values[$name];
    }

    public function __set($name, $value){
        $this->values[$name] = $value;
    }

    private static function getFilters($filters) {
        $sql = '';  
        if(count($filters) > 0){
            $sql .= " WHERE";
            foreach($filters as $key => $value){
                if($key == 'raw'){                    
                    $sql .= " $value"; 
                }else{
                    $value = static::getFormatedValue($value);
                    $sql .= " $key = $value"; 
                }
                if($key !== array_key_last($filters)){
                    $sql .= " and";
                }
            }
        }
        return $sql;
    }

    public static function getOne($filters = [], $columns = '*') {
        $class = get_called_class();
        $result = static::getResultSetFromSelect($filters, $columns);
        return isset($result) ? new $class($result->fetch_assoc()) : null;
    }

    public static function getResultSetFromSelect($filters = [],$columns = '*') {
        $sql = "SELECT $columns FROM ". static::$tableName. static::getFilters($filters);
        $result = Database::getResultFromQuery($sql);
        if($result->num_rows === 0){
            return null;
        }else{
            return $result;
        }
    }

    public static function getResultSetFromSelectBetween($quantItms, $filters = [], $start = 0, $order1 = 'id', $order2 = 'id',$columns = '*'){
        $sql = "SELECT $columns FROM ". static::$tableName. static::getFilters($filters). " order by $order1 desc, $order2 asc limit $quantItms offset $start";
        
        $result = Database::getResultFromQuery($sql);

        if($result->num_rows === 0){
            return null;
        }else{
            return $result;
        }
    }

    public static function getResultSetFromSelectBetweenAndText($quantItms, $filters = [], $search = '', $start = 0, $order1 = 'id', $order2 = 'id',$columns = '*'){
        $arraySearch = explode(" ", $search);
        $sql = "SELECT $columns FROM ". static::$tableName. static::getFilters($filters). " and";

        foreach($arraySearch as $key => $value){
            if($key !== array_key_last($arraySearch)){
                $sql .= " $order2 like '%$value%' or"; 
            }else{
                $sql .= " $order2 like '%$value%'"; 
            }
        }

        $sql .= " order by $order1 desc, $order2 asc limit $quantItms offset $start";

        $result = Database::getResultFromQuery($sql);
        
        if($result->num_rows === 0){
            return null;
        }else{
            return $result;
        }
    }

    public static function getResultCount($filters,$order, $search = null){
        $sql = "SELECT id FROM ". static::$tableName. static::getFilters($filters);

        if(!is_null($search)){
            $arraySearch = explode(" ", $search);
            foreach($arraySearch as $key => $value){
                if($key !== array_key_last($arraySearch)){
                    $sql .= " and $order like '%$value%' or"; 
                }else{
                    $sql .= " and $order like '%$value%'"; 
                }
            }
        }

        $count = Database::getResultFromQuery($sql);
        if($count->num_rows === 0){
            return 0;
        }else{
            return $count->num_rows;
        }
    }

    private static function getFormatedValue($value){
        if(is_null($value)){
            return "null";
        }else if (is_string($value)){
            return "'$value'";
        }else {
            return $value;
        }
    }

    public function insert(){
        $sql = "INSERT INTO ".static::$tableName." (";
        foreach(static::$columns as $column){
            $sql .= "$column,";
        }
        $sql[strlen($sql)-1] = ")";
        $sql .= " VALUES (DEFAULT,";
        foreach(static::$columns as $column){
            if($column !== 'id'){
                $sql .= static::getFormatedValue($this->$column).",";
            }
        }
        $sql[strlen($sql)-1] = ")";
        $id = Database::executeSQL($sql);
        $this->id = $id;
    }

    public function update(){
        $sql = "UPDATE ". static::$tableName. " SET ";
        foreach (static::$columns as $column){
            if(!is_null($this->$column) && $column != "id"){
                $sql .= "$column = " . static::getFormatedValue($this->$column).",";
            }
        }

        $sql[strlen($sql)-1] = " ";
        $sql .=  " WHERE id = '{$this->id}' LIMIT 1";
        $id = $this->id;
        Database::executeSQL($sql);
        return static::getOne(['id' => $id]);
    }

    public function delete(){
        $sql = "DELETE FROM ". static::$tableName ." WHERE id = ". $this->id ." LIMIT 1";
        Database::executeSQL($sql);
    }
}