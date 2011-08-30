<?php

class ApiResponse extends Response
{
    public function __construct($response)
    {
        parent::__construct($response);
        $this->AddHeader('Content-Type', 'text/xml');
        #dump($this);
    }
}
?>
