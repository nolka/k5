<?php

class Request
{
    public $Method;
    public $Path;
    public $Callback;
    
    public $Referer;
    
    public $IsSecureConnection;
    
    public $RemoteAddr;
    public $RemoteHost;
    public $RemotePort;
    public $MyAddr;
    public $MyPort;
    public $MyProtocol;
    
    public $Filters = array();
    
    private $data;
    
    private $pathSeparator = '/';
    
    public function __construct($method, $path, $callback)
    {
        $reqMethod = $method;
        $this->Method = $reqMethod;
        $this->Path = $path;
        $this->Callback = $callback;
        $this->data = Arguments::Request();
        $this->Referer = $_SERVER['HTTP_REFERER'];
        $this->IsSecureConnection = (bool)$_SERVER['HTTPS'];
        $this->RemoteAddr = $_SERVER['REMOTE_ADDR'];
        $this->RemoteHost = $_SERVER['REMOTE_HOST'];
        $this->RemotePort = $_SERVER['REMOTE_PORT'];
        $this->MyAddr = $_SERVER['SERVER_ADDR'];
        $this->MyPort = $_SERVER['SERVER_PORT'];
        $this->MyProtocol = $_SERVER['SERVER_PROTOCOL'];
                
        if($this->Method == "ALL")
        {
            $this->Method = "GET";
            Application::GetInstance()->Requests[$this->Method][] = clone $this;
            $this->Method = "POST";
            Application::GetInstance()->Requests[$this->Method][] = clone $this;
            $this->Method = "PUT";
            Application::GetInstance()->Requests[$this->Method][] = clone $this;
            $this->Method = "DELETE";
            Application::GetInstance()->Requests[$this->Method][] = clone $this;
            $this->Method = "AJAX";
            Application::GetInstance()->Requests[$this->Method][] = clone $this;
        }
        else
        {
            $this->Method = $reqMethod;
            Application::GetInstance()->Requests[$this->Method][] = $this;
        }
    }
    
    public function Filter($name, $filter)
    {
        $this->Filters[$name] = $filter;
        return $this;
    }
    
    private function isCorrectPart($pathPart)
    {
        return $pathPart != '';
    }
    
    public function SetPathSeparator($ps)
    {
        $this->pathSeparator = $ps;
    }
    
    public function GetPathSeparator()
    {
        return $this->pathSeparator;
    }
    
    public function GetRequest()
    {
        return $this->data;
    }
    
    public function Run()
    {
        $args = array();
        
        if ($this->Method != '' && $_SERVER['REQUEST_METHOD'] != $this->Method) 
            return false;
        
        $pathParts = explode($this->GetPathSeparator(), $this->Path);
        $pathParts = array_values(array_filter($pathParts, array('Request', 'isCorrectPart')));
        
        $urlParts = explode($this->GetPathSeparator(), $_SERVER['REQUEST_URI']);
        $urlParts = array_values(array_filter($urlParts, array('Request', 'isCorrectPart')));
        
        if(count($pathParts) != count($urlParts))
        {
            return false;
        }
        
        #dump($this);
        $args[] = $this;
        for($i =  0; $i < count($pathParts); $i++)
        {
            // Проверяем, является ли переменной данная часть пути
            // Переменные пути оформляются в фигурные скобки, что и проверяет регулярное выражение
            if (preg_match('/^\{(.*)\}$/', $pathParts[$i], $match))
            {
                
               #dump($this);
                #echo "$pathParts[$i] is variable! <br />";
                
                $argName = $match[1];
                $argVal = urldecode($urlParts[$i]);
                
                // если в названии переменной есть двоеточие, значит в ее названии кроется еще и фильтр
                // он как раз после двоеточия и следует
                if(strpos($match[1], ":"))
                {
                    // получаем фильтр
                    $argData = explode(":", $match[1]);
                    $argName = $argData[0];
                    $this->Filter($argName, $argData[1]);
                    unset($argData);
                }
                
                if(count($this->Filters) > 0 && isset($this->Filters[$argName]) && $this->Filters[$argName] !== null)
                {
                        $filter = $this->Filters[$argName];
                        //echo "+++ '$var' => '$argName'<br />";
//                        if($var == $argName)
//                        {
                            #echo "'$argName' => '$argVal' ($filter)<br />";
                            if(($filter == "i" || $filter == "int") && !preg_match('/^[-+]?[0-9]*([eE][-+]?[0-9]+)?$/', $argVal))
                            {
                                #echo "is not int<br />";
                                throw new Exception("Argument $argName should be an a integer, but it`s value is: '$argVal'!");
                            }else
                            if(($filter == "s" || $filter == "str") && !is_string($argVal))
                            {
                                #echo "is not str<br />";
                                throw new Exception("Argument $argName should be an a string, but it`s value is: '$argVal'!");
                            }else
                            if(($filter == "f" || $filter == "float") && !preg_match('/^([+-]?(((\d+(\.)?)|(\d*\.\d+))([eE][+-]?\d+)?))$/',$argVal))
                            {
                                throw new Exception("Argument $argName should be an a float, but it`s value is: '$argVal'!");
                            }else
                            if( preg_match("/\/(.*)\/[igm]?/", $filter) && !preg_match($filter, $argVal))
                            {
                                #echo "is not match to rex<br />";
                                throw new Exception("Argument $argName sdoes not match to regexp: '$filter'!");
                            }
                        
                        else
                        {
                            $args[$argName] = $argVal;
                        }
                        
                }
                else
                {
                    $args[$argName] = $argVal;
                }
            }
            // если квадратные скобки, то обрабатывать этот URL будет объект класса,
            // имя которого в скобках. Обрабатывать будет тот метод, что идет следом за
            // именем класса. Следом за методом передаются параметры, предназначенные для метода
//            else if(preg_match('/^\[(.*)\]$/', $pathParts[$i], $match)) 
//            {
//                
//            }
            else
            {
                if ($urlParts[$i] != $pathParts[$i])
                    return false;
            }
        }
        
        // если метод POST, аргументы в функцию будут переданы из POST полей
        if($this->Method == "POST")
        {
            $args = array_merge($args, $this->GetRequest());
        }
        
        // если коллбэк простой метод
        if(is_callable($this->Callback))
        {
            return call_user_func_array($this->Callback, $args);
        }
        
        // если коллбэк в классе передан
        if(strpos($this->Callback, "."))
        {
            $methodInfo = explode(".", $this->Callback);
            $className = $methodInfo[0];
            $methodName = $methodInfo[1];
            if(class_exists($className))
            {
                $obj = new $className();
                if(method_exists($obj, $methodName))
                {
                    return call_user_func_array(array($obj, $methodName), $args);
                }
            }
        }
        
        //TODO доделать монтирование классов как обработчики УРЛов
//        if(is_subclass_of($this->Callback, 'SubApp'))
//        {
//            return call_user_func_array(array($this->Callback, 'Run'), $args);
//        }
//        else
//        {
//            throw new Exception("Callback class must be a subclass of 'SubApp'");
//        }
        
    }
    
    public function __get($key)
    {
        if(isset($this->data[$key]) && $this->data[$key] !== null)
        {
            return $this->data[$key];
        }
        else
        {
            return null;
        }
    }
    
}


?>
