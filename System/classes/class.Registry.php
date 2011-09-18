<?php

class Registry extends Singleton
{
    private $dataTree = array();
    
    public static $Db = null;
    
    public static function Get($key)
    {
        return (isset(self::$dataTree[$key]))? self::$dataTree[$key]: null;
    }
    
    public static function Set($key, $value)
    {
        self::$dataTree[$key] = $value;
    }
    
    public function __get($key)
    {
        return (isset($this->dataTree[$key]))? $this->dataTree[$key]: null;
    }
    
    public function __set($key, $alue)
    {
        $this->dataTree[$key] = $value;
    }
}
?>
