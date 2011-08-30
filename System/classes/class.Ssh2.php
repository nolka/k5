<?php

class SSH2
{
    private $rResource = null;
    public $IsAuthorized = false;


    public function __construct($sServer, $sServerPort)
    {
        $this->rResource = @ssh2_connect($sServer, $sServerPort);
    }


    public function __destruct()
    {
            
    }

    public function IsConnected()
    {
        return $this->rResource;
    }

    public function Authorize($sUsername, $sPassword, $privKeyFile = null, $passPhrase = null)
    {
        if(is_file($sPassword) && $privKeyFile !== null)
        {
            if($passPhrase === null)
            {
                $this->IsAuthorized = ssh2_auth_pubkey_file($this->rResource, $sUsername, $sPassword, $privKeyFile);
            }
            else
            {
                $this->IsAuthorized = ssh2_auth_pubkey_file($this->rResource, $sUsername, $sPassword, $privKeyFile, $passPhrase);
            }
        }
        else
        {
            $this->IsAuthorized = ssh2_auth_password($this->rResource, $sUsername, $sPassword);
            
        }
        return $this->IsAuthorized;
    }


    public function Exec($sCommand)
    {
        $aOutput = array();

        $rStream = ssh2_exec($this->rResource, $sCommand);
        $rErrorStream = ssh2_fetch_stream($rStream, SSH2_STREAM_STDERR);

        stream_set_blocking($rErrorStream, true);
        stream_set_blocking($rStream, true);

        $aOutput['output'] = stream_get_contents($rStream);
        $aOutput['error'] = stream_get_contents($rErrorStream);

        fclose($rErrorStream);
        fclose($rStream);

        return $aOutput;
    }

    public function getConfig()
    {
        $aStd = $this->Exec("cd ~ && cat server.cfg");
        $aConfig = explode(PHP_EOL, $aStd['output']);

        $aOutput = array();

        foreach($aConfig as $sLine)
        {
                $aTemp = explode(' ', $sLine, 2);

                if(isset($aTemp[1]))
                {			
                        $aOutput[$aTemp[0]] = trim($aTemp[1]);
                }
        }

        return $aOutput;
    }
        
}
