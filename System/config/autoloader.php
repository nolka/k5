<?php

$include_pathes = array(
    SYSROOT.'classes/'
    );

class Autoloader
{
    private static $exclude_classes = array("Twig");
    
    protected static function FileExists($filename)
    {       
        foreach (explode(PATH_SEPARATOR, get_include_path()) as $path)
        {
            if ($path && $path[strlen($path) - 1] != '/')
            {
                $path .= '/';
            }
            #echo "{$path}{$filename}<br />";
            if (file_exists("{$path}{$filename}"))
            {
                return true;
            }
        }
        return false;
    }
    
    public static function Load($class_name)
    {
        foreach(self::$exclude_classes as $exclusion)
        {
            if(strpos(strtolower($class_name), strtolower($exclusion)) !== false)
                return false;
        }
        #echo 'try to load '.$class_name.'<br />';
        if(self::FileExists('class.'.$class_name.'.php'))
        {
            include_once('class.'.$class_name.'.php');
        }
        
        if(class_exists($class_name) || interface_exists($class_name))
        {
            if(method_exists($class_name, 'Construct'))
            {
                call_user_func( array( $class_name, 'Construct' ) );
            }
            
            if( method_exists( $class_name, 'Destruct' ) )
            {
                register_shutdown_function( array( $class_name, 'Destruct' ) );
            }
            return true;
        }
        else
        {
            return false;
        }
    }

}

set_include_path(join(PATH_SEPARATOR, array_merge($include_pathes, array(get_include_path()))));
spl_autoload_register(array('Autoloader', 'Load'));

?>

