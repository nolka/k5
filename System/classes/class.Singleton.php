<?php

class Singleton
{
    private static $instance = null;
    
    public static function GetInstance()
    {
        if(self::$instance == null)
        {
            self::$instance = new self;
        }
        return self::$instance;
    }
    
}

?>
