<?php

class Session
{
    static private $session_lifetime = '7200';
    
    private $session = null;
    private $account = null;
    private static $instance = null;
    
    private $loginSeccessed;
    private $isLoggedIn;
    

    public static function GetInstance()
    {
        if (self::$instance === null)
        {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function __construct()
    {
        session_start();

        if (!array_key_exists('xtrsession', $_SESSION))
        {
            $_SESSION['xtrsession'] = array();
        }

        $this->session =& $_SESSION['xtrsession'];

        $this->CheckSession();
    }

    public function Logout()
    {
        $this->session['account'] = $this->account = null;
        $this->account = null;
        return true;
    }
    
    public function Login($login, $passwd)
    {
        $am = new AccountManager();
        $this->account = $am->GetAccount($login, $passwd);
        $am->Free();
        if($this->account == null)
        {
            return $this->loginSeccessed = false;
        }
        $this->session['account'] = ($this->account != null) ? serialize($this->account): null;
        $this->session['lastVisit'] = time();
        $this->isLoggedIn = true;
        return $this->isLoggedIn;
    }

    public function GetAccount()
    {
        return $this->account;
    }

    public function IsAuthorized()
    {
        return (boolean) $this->account;
    }
    
    public function CheckSession()
    {
        if(isset($this->session['account']))
        {
            $this->session['lastVisit'] = time();
            $this->account = ($this->session['account'] != null) ? unserialize( $this->session['account'] ) : null;
            return true;
        }
        return false;
    }

    public function __set($name, $value)
    {
        $this->session[$name] = $value;
    }

    public function __get($name)
    {
        return array_key_exists($name, $this->session) ? $this->session[$name] : null;
    }

}

?>
