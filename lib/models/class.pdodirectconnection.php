<?php

class PDODirectConnection
{

    private $host, $user, $password, $port, $pdo_connection = null;

    public function __construct($_host, $_user, $_password, $_port = '')
    {
        $this->host = $_host;
        $this->user = $_user;
        $this->password = $_password;
        $this->port = $_port;
    }

    /**
     * Returns pdo connection to mysql server
     * @return mixed
     */
    public function getConnection()
    {
        if($this->pdo_connection === null)
            $this->connect();
        return $this->pdo_connection;
    }

    /**
     * Start the connection between PHP and MySQL
     */
    public function connect()
    {
        try
        {
            $this->pdo_connection = new PDO($this->getDns(), $this->user, $this->password);
        }
        catch (Exception $e)
        {
            throw(new UnableToConnectToMySQLException("Impossible to connect to mysql: ".$e));
        }
    }

    /**
     * Returns DNS PDO string
     * @return string
     */
    private function getDns()
    {
        return 'mysql:host='.$this->host.';'.(intval($this->port) > 0 ? ';port='.$this->port : '');
    }

    public function getHost()       { return $this->host; }
    public function getUser()       { return $this->user; }
    public function getPassword()   { return $this->password; }

}