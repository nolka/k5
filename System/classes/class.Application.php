<?php

// xternalx main

class Application
{
    private static $instance = null;
    private static $method;
    
    private static $name = 'Authorization service';
    private static $version = '0.3 beta';
        
    private $path;
    
    // параметры приложения, передаваемые в конструкторе
    public static $Params = array ('url_root' => '/');
    // функция, вызываемая перед выполнением запроса
    private $beforeFunc;
    // после
    private $afterFunc;
    
    // список запросов
    public $Requests = array(
            "ALL" => array(),
            "GET" => array(), 
            "POST" => array(), 
            "PUT" => array(), 
            "DELETE" => array(),
            "AJAX" => array()
        );
    
    public function __construct($params = null)
    {
        self::$method = self::getRequestMethod();
        $this->path = $_SERVER['REQUEST_URI'];
        
        if($params !== null)
        {
            self::$Params = $params;
        }
        
    }
    
    public static function GetInstance()
    {
        if(self::$instance == null)
        {
            self::$instance = new Application();
        }
        
        return self::$instance;
    }
    
    public static function GetVersion()
    {
        return self::$version;
    }
    
    public static function GetName()
    {
        return self::$name;
    }
    
    public static function Init()
    {
        self::GetInstance();
    }
    
    private function doRequests()
    {
        try
        {
            $this->callFunc($this->beforeFunc);
            foreach($this->Requests[self::$method] as &$request)
            {

                $response = $request->Run();
                #dump($response);
                if($response) return $response;
            }
            $this->callFunc($this->afterFunc);

            return api_error(ApiErrorCode::MethodNotSpecified, 'No method specified');
        }
        catch(Exception $e)
        {
            return api_error(ApiErrorCode::ServerError, "Server error occurred: ".$e->getMessage());
        }
    }

    public function LoadPlugins($path)
    {
        if(!defined('PLUGIN_LOADER'))
            define('PLUGIN_LOADER', true);
        
        $possible_plugin_path = $path;
        foreach(glob($possible_plugin_path."*") as $plugin_name)
        {
            if(is_dir($plugin_name))
            {
                if(file_exists($plugin_name.'/init.php'))
                {
                    require_once$plugin_name.'/init.php';
                }
                else
                {
                    $this->LoadPlugins($plugin_name.'/');
                }
            }
            else
            if(is_file($plugin_name) && preg_match('/\.php$/', $plugin_name))
            {
                require_once $plugin_name;
            }
        }
    }
    
    private static function getRequestMethod()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') return 'AJAX';
        else if (!empty($_POST)) return 'POST';
        else return 'GET';
    }
    
    private function callFunc($callback)
    {
        if(is_callable($callback))
        {
            return call_user_func($callback);
        }
    }
    
    // Эта функция вызывается перед началом обработки запросов
    public function Before($callback)
    {
        $this->beforeFunc = $callback;
    }
    
    // эта функция вызывается после обработки запроса
    public function After()
    {
        $this->afterFunc = $callback;
    }
    
    // Создает новый обработчик УРЛов
    private  function addRequest($method, $path, $callback)
    {
        //new Request($method, $path, $callback);
        return new Request($method, self::$Params['url_root'].$path, $callback);
    }
    
    // метод для добавления УРЛов, доступных через GET
    public function Get($path, $callback)
    {
        return $this->addRequest("GET", $path, $callback);
    }
    
    // добавляет УРЛы, доступные через POST
    public  function Post($path, $callback)
    {
        return $this->addRequest("POST", $path, $callback);
    }
    
    
    public function All($path, $callback)
    {
        return $this->addRequest("All", $path, $callback);
    }
    
    public function Run($args = null)
    {
        // Выполняем обработку УРЛов и возвращаем ответ в браузер
        echo Application::GetInstance()->doRequests();
    }
    
}

//helper funcs

function errorHandler($errno, $errstr, $errfile, $errline)
{
    $tpl = <<<FUCK
   <table style="border: 1px solid red">
       <tr>
        <td>Code: </td> <td>{$errno}</td>
        </tr>
        <tr>
        <td>File: </td> <td>{$errfile}</td>
        </tr>
        <tr>
        <td>Line: </td> <td>{$errline}</td>
        </tr>
        <tr>
        <td>Description: </td> <td>{$errstr}</td>
       </tr>
       <tr>
        <td colspan="2">
            <pre>
{backtrace}
            </pre>
        </td>
       </tr>
   </table>
FUCK;
    ob_start();
    debug_print_backtrace();
    $tpl = str_replace('{backtrace}', ob_get_clean(), $tpl);
    echo $tpl;

}


function RegisterUrl($method, $path, $callback)
{
    return new Request($method, $path, $callback);
}

// SOME OPERATIONS

set_error_handler('errorHandler');

?>
