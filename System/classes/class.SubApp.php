<?php

// класс представляет из себя скелет для субприложений.
// нужен для приколу. А чо, перед пасанчиками стыдно не будет)
abstract class SubApp
{
    private $exposedFuncs = array();
    
    public function __construct()
    {
        
    }
    
    public function GetExposedFuncs()
    {
        return $this;
    }
    
    public function AddExposedFunc($func)
    {
        if(method_exists($this, $func))
        {
            //TODO: Короче, доделать, чтобы можно было классы монтировать как отдельные
            // подприложения (SubApplications)
            $this->exposedFuncs[] = $func;
        }
        else
        {
            throw new Exception("Exposed object must be function name or closure!");
        }
    }
    
    
}
?>
