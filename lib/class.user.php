<?php

class User extends Singleton
{

    protected $username = 'admin';
    protected $password = 'admin';
    protected $credentials = array();

    protected $use_database = false, $main_table = '', $cookie_ttl, $login_field, $password_field, $credentials_table = '', $credentials_field = '', $isRegistered = false, $use_cookie = false;
    protected $user_id, $user_key, $user_object = null;

    public function __construct()
    {
        $this->use_cookie        = Configuration::getInstance()->get('User/UseCookie', true);
        $this->use_database      = Configuration::getInstance()->get('User/UseDatabase', true);
        $this->main_table        = Configuration::getInstance()->get('User/UserTable', true, 'users');
        $this->credentials_table = Configuration::getInstance()->get('User/CredentialsTable', true, 'credentials');
        $this->credentials_field = Configuration::getInstance()->get('User/CredentialsField', true, 'name');
        $this->cookie_name       = Configuration::getInstance()->get('User/CookieName', true, 'OrionUser');
        $this->username          = Configuration::getInstance()->get('User/BasicUsername', true, 'admin');
        $this->password          = Configuration::getInstance()->get('User/BasicPassword', true, 'admin');
        $this->credentials       = Configuration::getInstance()->get('User/BasicCredentials', true, array('manager'));
        $this->login_field       = Configuration::getInstance()->get('User/LoginField', true, 'login');
        $this->password_field    = Configuration::getInstance()->get('User/PasswordField', true, 'password');
        $this->cookie_ttl        = Configuration::getInstance()->get('User/CookieTtl', true, 604800);
        $this->validateSession();
    }

    /**
     * Disconnect user
     */
    public function doLogout()
    {
        Session::getInstance()->set('User/id', '');
        Session::getInstance()->set('User/key', '');
        if($this->use_cookie)
        {
            setcookie($this->cookie_name, '', 0, '/');
        }
    }

    /**
     * Save new flash message to session
     * @param $type
     * @param $message
     */
    public function flash($type, $message)
    {
        if(!Session::getInstance()->exists('flashes'))
            Session::getInstance()->set('flashes', array());
        $flashes = Session::getInstance()->get('flashes');
        $flashes[] = array('type' => $type, 'message' => $message);
        Session::getInstance()->set('flashes', $flashes);
    }

    /**
     * Returns all flash messages
     * @return array
     */
    public function getFlashes()
    {
        return Session::getInstance()->exists('flashes') ? Session::getInstance()->get('flashes') : array();
    }

    /**
     * Clear saved flash messages
     */
    public function clearFlashes()
    {
        Session::getInstance()->set('flashes', array());
    }

    /**
     * Try to login user
     * @param $username
     * @param $password
     * @return bool
     */
    public function doLogin($username, $password)
    {
        if($this->use_database)
        {
            $f1 = $this->login_field;
            $f2 = $this->password_field;
            $classname = $this->main_table.'Table';
            $query = new Query(Database::getDatabase($classname::getStaticDatabaseName()));
            $password = md5($password);
            $result = $query->addSelect('*')
                ->addFrom($this->main_table)
                ->addWhere($f1." = '".addslashes($username)."' AND ".$f2." = '".addslashes($password)."'")
                ->execute(QueryResultType::RECORD_OBJECT, true, $this->main_table);

            if(sizeof($result) > 0)
            {
                $this->isRegistered = true;
                $this->user_object = $result;
                $this->saveSession($this->user_object->id, $this->user_object->password);
                return true;
            }
            else
                return false;
        }
        elseif($this->username == $username && $this->password == $password)
        {
            $this->isRegistered = true;
            $this->saveSession($this->username, $this->password);
            return true;
        }
        return false;
    }

    /**
     * Makes session persistent
     */
    private function saveSession($id, $password)
    {
        Session::getInstance()->set('User/id', $id);
        Session::getInstance()->set('User/key', md5($id.'-'.$password));
        if($this->use_cookie)
        {
            setcookie($this->cookie_name, $id.'|'.$password, time() + $this->cookie_ttl, '/');
        }
    }

    /**
     * Validate user session
     */
    public function validateSession()
    {
        if($this->use_cookie)
            $this->readCookie();

        if(Session::getInstance()->exists('User/id') && Session::getInstance()->exists('User/key'))
        {
            $this->user_id = Session::getInstance()->get('User/id');
            $this->user_key = Session::getInstance()->get('User/key');
        }
        else return;

        if($this->use_database)
        {
            $userclassname = $this->main_table;
            try
            {
                $user = new $userclassname($this->user_id);
                if($user->id === $this->user_id && $this->user_key === md5($user->id.'-'.$user->password))
                {
                    $this->isRegistered = true;
                    $this->user_object = $user;
                }

            }
            catch(Exception $e) {}
        }
        elseif($this->user_id === $this->username && $this->user_key === md5($this->username.'-'.$this->password))
        {
            $this->isRegistered = true;
        }
    }

    /**
     * Tests if an user has a given credential
     * @param $credential
     * @return bool
     */
    public function hasAccess($credential)
    {
        if(!$this->isRegistered())
            return false;
        if(!$this->use_database)
            return in_array($credential, $this->credentials);

        $credclassname = $this->credentials_table;
        $credfield = $this->credentials_field;
        foreach($this->user_object->$credclassname as $cred)
        {
            if($cred->$credfield === $credential)
                return true;
        }
        return false;
    }

    /**
     * Read user information from cookie
     * @return bool
     */
    public function readCookie()
    {
        if(isset($_COOKIE[$this->cookie_name]))
        {
            $value = explode('|', $this->cookie_name);
            Session::getInstance()->set('User/Info', $value[0]);
            Session::getInstance()->set('User/key', $value[1]);
        }
    }

    /**
     * Returns the current user object
     * @return User table record or null
     */
    public function getUserObject()
    {
        return $this->user_object;
    }

    /**
     * tests if an user is registered
     * @return bool
     */
    public function isRegistered()
    {
        return $this->isRegistered;
    }

    /**
     * @return bool
     */
    public function isUsingDatabase()
    {
        return $this->use_database;
    }

    /**
     * @return string
     */
    public function getBasicUserLogin()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getBasicUserPassword()
    {
        return $this->password;
    }

}