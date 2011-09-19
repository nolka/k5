<?php

class SystemConfig 
{
    public static $Settings;
    private static $sql;

    public static function Init($params)
    {
        try
        {
            $sql = new Database($params['db_user'],$params['db_password'], $params['db_db']);
            if($sql)
            {
                $sql->SetCharset("utf8");
//                $sql->Query("SELECT * FROM `SystemConfig` ORDER BY `s_param` ASC");
//                while($r = $sql->Assoc())
//                {
//                    self::$Settings[$r['s_param']] = $r['s_value']; 
//                }
                
                self::$sql = $sql;
            }
            else
            {
                throw new Exception('Error initializing SystemConfig');
            }
        }
        catch(Exception $e)
        {
            throw new Exception('Exception in SystemConfig '.$e->getMessage());
        }
    }
    
    public static function Get($param) 
    {
        $path = explode("/", $param);
        if(count($path) == 1)
        {
            if(isset(self::$Settings[$param]))
            {
                return self::$Settings[$param];
            }
        }
        else
        {
            if(isset(self::$Settings[$path[0]][$path[1]]))
            {
                return self::$Settings[$path[0]][$path[1]];
            }
        }
        
        {
            return null;
        }
    }
    
    public static function Set($param, $val)
    {
        if(is_array($param))
        {
            self::$Settings = array_merge(self::$Settings, $param);
        }
        else
        {
            $path = explode("/", $param);
            if(count($path) == 1)
            {
                self::$Settings[$param] = $val;
            }
            else 
            {
                self::$Settings[$path[0]][$path[1]] = $val;
            }
        }
    }
   
    public static function Delete($param)
    {
        unset(self::$Settings[$param]);
    }
   
    public static function Save()
    {
        self::$sql->Query("TRUNCATE TABLE `x_settings`");
        foreach (self::$Settings as $k => $v)
        {
            self::$sql->Insert('x_settings', array('s_param' => $k, 's_value'=> $v));
        }
    }
    
    public static function GetDatabase()
    {
        return self::$sql;
    }
    
    public static function GetDatabaseInstance()
    {
        return self::$sql->GetInstance();
    }
    
}
?>
