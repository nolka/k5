<?php

class DatabaseConnectionException extends Exception {}
class DatabaseQueryException extends Exception {}
class DatabaseFetchMode
{
    const Assoc = 0;
    const Object = 1;
}

class Database
{
    private $res = null;
    private $queryRes = null;
    
    private $logMessages = array();
    
    private $rawQuery;
    private $rawQueryLen = 0;
    private $charId = -1;
    
    private $fetchMode = DatabaseFetchMode::Assoc;
    private $fetchClass = null;
    private $fetchClassArgs = null;
    
    private $preparedQuery = array();
    
    public function __construct($username, $password, $dbname, $host = 'localhost')
    {
        $this->res = mysqli_connect($host, $username, $password, $dbname);
        if(mysqli_error($this->res))
        {
            throw new DatabaseConnectionException("Error occurred when i try to connect to database: (".mysqli_errno($this->res).") ".mysqli_error($this->res));
        }
        
    }
    
    public function Query($query_string)
    {
        $this->rawQuery = $query_string;
        $this->rawQueryLen = strlen($query_string);
        $this->preparedQuery = array();
        $this->charId = -1;
        $this->toLog('raw query: '.$query_string.'; args: '.  json_encode(func_get_args()));
        if(func_num_args() >= 2)
        {
            $query_string = call_user_func_array(array($this, 'Prepare'), func_get_args());
        }
        $this->toLog('Prepared query: '.$query_string);
        $this->queryRes = mysqli_query($this->res, $query_string);
        if(!$this->queryRes)
        {
            throw new DatabaseQueryException("Database query failed: (".mysqli_errno($this->res).") ".mysqli_error($this->res)."<br />".$this->LogAsHtml());
        }
        return $this;
    }
    
    public function LogAsHtml()
    {
        return implode('<br />', $this->logMessages);
    }
    
    public function LogAsText()
    {
        return implode("\n", $this->logMessages);
    }
    
    function Select($what)
    {
        $this->rawQuery = "SELECT SQL_CALC_FOUND_ROWS ".$this->escape($what);
        return $this;
    }
    
    function From($from)
    {
        $this->rawQuery .= " FROM `".$this->escape($from)."` ";
        return $this;
    }
    
    function Filter($filter = array())
    {
        $this->rawQuery .= " WHERE ";
        $filtersCount = count($filter);
        $i = 0;
        foreach($filter as $k=> $v)
        {
            if(is_int($k))
            {
                $this->rawQuery .= $this->escape($v);
            }
            else
            {
                $this->rawQuery .= "`".$this->escape($k)."`=";
                if(is_int($v) || is_float($v) || is_numeric($v))
                {
                    $this->rawQuery .= $this->escape($v);
                }
                else
                {
                    $this->rawQuery .= "'".$this->escape($v)."'";
                }
            }
            $i++;
            if($i< $filtersCount)
            {
                $this->rawQuery .= " AND ";
            }
        }
        return $this;
    }
    
    /**
     *
     * @param mixed $what Field to order
     * @param string $order ASC or DESC
     * @return Database 
     */
    function OrderBy($what, $order = "ASC")
    {
        $this->rawQuery .= " ORDER BY `{$this->escape($what)}` {$this->escape($order)}";
        return $this;
    }
    
    function Limit($count, $offset = null)
    {
        $this->rawQuery .= " LIMIT ".(($offset === null)? "" :$offset.",").$count." ";
        return $this;
    }
    
    function Exec()
    {
        if(func_num_args() > 0)
        {
            $query = $this->Prepare($this->rawQuery, func_get_args());
        }
        else
        {
            $query = $this->rawQuery;
        }
        $this->toLog("Prepared query: ". $query);
        return $this->Query($query);
    }
    
    function Assoc($table_name)
    {
        return $this->Select("*")->From($table_name);
    }
    
    function Object($class, $constructor_args = null)
    {
        $this->fetchClassArgs =  $constructor_args;
        if(is_object($class))
        {
            $class = get_class($class);
        }
        if(func_num_args()>1)
        {
            $args = func_get_args();
            array_shift($args);
        }
        $this->fetchClass = $class;
        return $this->Select("*")->From($class);
    }
    
    function getItems($one = true, $sort_order = "ASC", $order_by = null)
    {
        if($order_by !== null && $sort_order !== null)
        {
            if($one)
            {
                $this->OrderBy($order_by, $sort_order)->Limit(1);
            }
            else
            {
                $this->OrderBy($order_by, $sort_order);
            }
        }
        else
        {
            if($one)
                $this->Limit(1);
        }
        $queryResult = $this->Query($this->rawQuery);
        $dataResult = null;
        if($this->fetchMode == DatabaseFetchMode::Assoc)
        {
            if($one)
            {
                $dataResult = mysqli_fetch_assoc($this->queryRes);
            }
            else
            {
                $dataResult = array();
                while($res = mysqli_fetch_assoc($this->queryRes))
                {
                    $dataResult[] = $res;
                }
            }
            $this->toLog(json_encode($dataResult));
        }
        else
        {
            if($one)
            {
                $dataResult = mysqli_fetch_object($this->queryRes, $this->fetchClass, $this->fetchClassArgs);
            }
            else
            {
                $dataResult = array();
                while($res = mysqli_fetch_object($this->queryRes, $this->fetchClass, $this->fetchClassArgs));
                {
                    $dataResult[] = $res;
                }
            }
            $this->toLog(json_encode($dataResult));
        }
        return $dataResult;
    }
    
    function First($order_by = null)
    {
        return $this->getItems(true, "ASC", $this->escape($order_by));
    }
    
    function Last($order_by = null)
    {
        return $this->getItems(true, "DESC", $this->escape($order_by));
    }
    
    function All($order_by = null)
    {
        return $this->getItems(false, null, ($order_by === null)? null:$this->escape($order_by));
    }

    
    
    function AsObject($class_name)
    {
        $this->fetchMode = DatabaseFetchMode::Object;
        #return 
    }
    
    function AsArray($what)
    {
        $this->fetchMode = DatabaseFetchMode::Assoc;
        return $this->getItems(false, null, null);
    }

    public function Prepare($query_string)
    {
        $this->toLog("Preparing query: ".$query_string.'; length: '.  strlen($query_string));
        $argOffset = 1;
        for($i=0; $i <= $this->rawQueryLen; $i++)
        {
            $char = $this->getNextChar();
            if($char != '?')
            {
                $this->preparedQuery[] = $char;
            }
            else
            {
                $nextchar = $this->getNextChar(false);
                if($nextchar == "i")
                {
                    $this->preparedQuery[] = intval($this->escape(func_get_arg($argOffset++))); 
                    $this->getNextChar();
                }
                else
                if($nextchar == "f")
                {
                    $this->preparedQuery[] = floatval($this->escape(func_get_arg($argOffset++)));
                    $this->getNextChar();
                }else
                if($nextchar == "k")
                {
                    $this->preparedQuery[] = "`".($this->escape(func_get_arg($argOffset++)))."`";
                    $this->getNextChar();
                }
                else
                {
                    $this->preparedQuery[] = "'".($this->escape(func_get_arg($argOffset++)))."'";
                }   
            }
        }
        return implode('', $this->preparedQuery);
    }
    
    private function toLog($message)
    {
        $this->logMessages[] = $message;
    }
    
    private function escape($value)
    {
        return mysqli_real_escape_string($this->res, $value);
    }
    
    private function getNextChar($update_offset = true)
    {
        $this->charId++;
        if(isset($this->rawQuery[$this->charId]))
        {
            return $this->rawQuery[($update_offset)? $this->charId : $this->charId--];
        }
        else
        {
            return null;
        }
    }
    
    private function getPrevChar($update_offset = true)
    {
        $this->charId--;
        if(isset($this->rawQuery[$this->charId]))
        {
            return $this->rawQuery[($update_offset)? $this->charId : $this->charId++];
        }
        else
        {
            return null;
        }
    }
    
    public function SetCharset($charset = 'utf8')
    {
        mysqli_set_charset($this->res, $charset);
    }
    
    public function AffectedRows()
    {
        return mysqli_affected_rows($this->res);
    }
    
    public function RowsCount()
    {
        return mysqli_num_rows($this->res);
    }
    
    public function FieldsCount()
    {
        return mysqli_field_count($this->res);
    }
    
    public function Close()
    {
        mysqli_free_result($this->queryRes);
        mysqli_close($this->res);
    }
    
}

?>
