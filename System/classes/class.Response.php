<?php

class Response
{
    private $headers = array();
    private $response;
    private $responseCode = "200 OK";
    
    public function __construct($response, $headers = null)
    {
        $this->response = $response;
        $this->AddHeaderString(implode(" ", array($_SERVER["SERVER_PROTOCOL"], &$this->responseCode)));
        if(is_array($headers))
        {
            $this->headers = $headers;
        }
    }
    
    public function __toString()
    {
        return $this->Send();
    }
    
    public function AddHeader($param, $value)
    {
        $this->headers[$param] = $value;
        return $this;
    }
    
    public function AddHeaderString($string)
    {
        $this->headers[] = $string;
        return $this;
    }
    
    public function DeleteHeader($param)
    {
        if(isset($this->headers[$param]))
        {
            unset($this->headers[$param]);
        }
        return $this;
    }
    
    public function GetResponseContent()
    {
        return $this->response;
    }
    
    public function SetResponseContent($response)
    {
        $this->response = $response;
    }
    
    public function SetResponseCode($code, $description = null)
    {
        $this->responseCode = $code ." ". $description== null? "": $description;
    }
    
    public function Send()
    {
        foreach($this->headers as $k => $v)
        {
            if(!is_int($k))
            {
                header("$k: $v");
            }
            else
            {
                header("$v");
            }
        }
        return $this->response;
    }
    
}
?>
