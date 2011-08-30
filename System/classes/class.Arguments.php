<?php

class Arguments {

    public static function Get($keyname)
    {
        if(isset($_GET[$keyname]))
        {
            return $_GET[$keyname];
        }
        else
        {
            return null;
        }
    }

    public static function Post($keyname)
    {
        if(isset($_POST[$keyname]))
        {
            return $_POST[$keyname];
        }
        else
        {
            return null;
        }
    }

    public static function Key($keyname)
    {
        return $_REQUEST[$keyname];
    }
    
    public static function GetArray()
    {
        if($_SERVER['REQUEST_METHOD'] == "GET")
        {
            return $_GET;
        }
        else
        {
            return $_POST;
        }
    }
    
    public static function Request()
    {
        return $_REQUEST;
    }
}
?>
