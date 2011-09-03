<?php

class SystemConfig 
{
    public static $Settings;
    private static $sql;

    public static function Init($params)
    {
        try
        {
            $sql = new MySQL();
            if(isset($params['db_error_handler']))
                $sql->SetErrorHandler($params['db_error_handler']);
            $sql->Connect($params['db_host'],$params['db_user'],$params['db_password'],$params['db_db']);
            if($sql)
            {
                $sql->SetEncoding("utf8");
//                $sql->Query("SELECT * FROM `SystemConfig` ORDER BY `s_param` ASC");
//                while($r = $sql->Assoc())
//                {
//                    self::$Settings[$r['s_param']] = $r['s_value']; 
//                }
                
                self::$sql = $sql;
            }
            else
            {
                echo '<error>Error initializing SystemConfig</error>';
            }
        }
        catch(Exception $e)
        {
            echo '<error>Exception in SystemConfig</error>';
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
