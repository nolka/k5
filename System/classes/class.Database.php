<?php

class DatabaseConnectionException extends Exception {}

class Database
{
    private $res = null;
    
    private $logMessages = array();
    
    private $rawQuery;
    private $rawQueryLen = 0;
    private $charId = -1;
    
    
    private $preparedQuery = array();
    
    public function __construct($username, $password, $dbname, $host = 'localhost')
    {
        $this->res = mysqli_connect($host, $username, $password, $dbname);
        if(mysqli_error($this->res))
        {
            throw new DatabaseConnectionException("Error occurred when i try to connect to database: (".mysqli_errno().") ".mysqli_error());
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
    }
    
    private function toLog($message)
    {
        $this->logMessages[] = $message;
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
        $this->rawQuery = "SELECT ".$this->escape($what);
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
                if(is_int($v) || is_float($v))
                {
                    $this->rawQuery .= "'".$this->escape($v);
                }
                else
                {
                    $this->rawQuery .= "'".$this->escape($v)."'";
                }
            }
            $i++;
            if($i< $filtersCount)
            {
                $this->rawQuery .= ", AND ";
            }
        }
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
        $this->Query($query);
    }
    
    function Assoc()
    {
        
    }
    
    function Object()
    {
        
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
                }
                else
                {
                    $this->preparedQuery[] = "'".($this->escape(func_get_arg($argOffset++)))."'";
                }   
            }
        }
        return implode('', $this->preparedQuery);
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
    
}

?>
