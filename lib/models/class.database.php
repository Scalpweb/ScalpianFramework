<?php

class Database
{

    const DATABASE_XML_PREFIX = 'schema';

    static public $CHARSETS   = array('big5','dec8','cp850','hp8','koi8r','latin1','latin2','swe7','ascii','ujis','sjis','hebrew','tis620','euckr','koi8u','gb2312','greek','cp1250','gbk','latin5','armscii8','utf8','ucs2','cp866','keybcs2','macce','macroman','cp852','latin7','cp1251','cp1256','cp1257','binary','geostd8','cp932','eucjpms');
    static public $COLLATIONS = array('armscii8_general_ci','ascii_general_ci','big5_chinese_ci','cp850_general_ci','cp852_general_ci','cp866_general_ci','cp932_japanese_ci','cp1250_croatian_ci','cp1250_czech_cs','cp1250_general_ci','cp1250_polish_ci','cp1251_bulgarian_ci','cp1251_general_ci','cp1251_general_cs','cp1251_ukrainian_ci','cp1256_general_ci','cp1257_general_ci','cp1257_lithuanian_ci','dec8_swedish_ci','eucjpms_japanese_ci','euckr_korean_ci','gb2312_chinese_ci','gbk_chinese_ci','geostd8_general_ci','greek_general_ci','hebrew_general_ci','hp8_english_ci','keybcs2_general_ci','koi8r_general_ci','koi8u_general_ci','latin1_danish_ci','latin1_general_ci','latin1_general_cs','latin1_german1_ci','latin1_german2_ci','latin1_spanish_ci','latin1_swedish_ci','latin2_croatian_ci','latin2_czech_cs','latin2_general_ci','latin2_hungarian_ci','latin5_turkish_ci','latin7_estonian_cs','latin7_general_ci','latin7_general_cs','macce_general_ci','macroman_general_ci','sjis_japanese_ci','swe7_swedish_ci','tis620_thai_ci','ujis_japanese_ci','utf8_czech_ci','utf8_danish_ci','utf8_esperanto_ci','utf8_estonian_ci','utf8_general_ci','utf8_hungarian_ci','utf8_icelandic_ci','utf8_latvian_ci','utf8_lithuanian_ci','utf8_persian_ci','utf8_polish_ci','utf8_roman_ci','utf8_romanian_ci','utf8_sinhala_ci','utf8_slovak_ci','utf8_slovenian_ci','utf8_spanish2_ci','utf8_spanish_ci','utf8_swedish_ci','utf8_turkish_ci','utf8_unicode_ci');
    static public $ENGINES    = array('MyISAM', 'InnoDB');
    static public $INDEXES    = array('PRIMARY', 'INDEX', 'UNIQUE', 'SPATIAL', 'FULLTEXT');

    static private $databases = array();

    private $host, $name, $user, $port, $password, $pdo_connection = null, $connection_options, $charset, $collate;
    private $tables = array();

    public function __construct($_name, $_host, $_user, $_password, $_port = '', $_charset = 'utf8', $_collate = '')
    {
        $this->name = $_name;
        $this->host = $_host;
        $this->user = $_user;
        $this->port = $_port;
        $this->charset = $_charset;
        $this->collate = $_collate;
        $this->password = $_password;

        // By default, set encoding to UTF-8 and enable exception
        $this->connection_options = array(
            PDO::MYSQL_ATTR_INIT_COMMAND    => "SET NAMES utf8",
            PDO::ATTR_ERRMODE               => PDO::ERRMODE_EXCEPTION
        );
    }

    public function getName()       { return $this->name; }
    public function getUser()       { return $this->user; }
    public function getHost()       { return $this->host; }
    public function getPassword()   { return $this->password; }
    public function getPort()       { return $this->port; }
    public function getCharset()    { return $this->charset; }
    public function getCollate()    { return $this->collate; }

    /**
     * Change database charset
     * @param $new
     */
    public function changeCharset($new)
    {
        $this->charset = $new;
        $query = new Query($this);
        $query->fromString("ALTER DATABASE ".$this->getName()." CHARACTER SET ".$new)->execute(QueryResultType::NONE);
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
     * Get database size on disk
     */
    public function getSize()
    {
        $query = new Query($this);
        $result = $query->fromString("SELECT SUM(data_length + index_length) as lg FROM information_schema.tables WHERE table_schema = '".$this->getName()."' GROUP  BY table_schema")->execute(QueryResultType::PDO_ARRAY, true);
        return $result['lg'];
    }

    /**
     * Add new table to database
     * @param $table
     * @throws UnvalidObjectException
     */
    public function addTable($table)
    {
        if(!is_a($table, 'Table'))
            throw(new UnvalidObjectException("This variable must be a table object."));
        $this->tables[$table->getName()] = $table;
    }

    /**
     * Return tables array
     * @return array
     */
    public function getTables()
    {
        return $this->tables;
    }

    /**
     * Return table names array
     * @return array
     */
    public function getTablesNames()
    {
        $result = array();
        foreach($this->tables as $table)
            $result[] = $table->getName();
        return $result;
    }

    /**
     * Return given table
     * @param $key
     * @param bool $error
     * @throws TableNotFoundException
     * @return mixed
     */
    public function getTable($key, $error = true)
    {
        if(!isset($this->tables[$key]))
        {
            if($error)
                throw(new TableNotFoundException("Table not found: ".$key));
            else
                return null;
        }
        return $this->tables[$key];
    }

    /**
     * Execute a query from string
     * @param $query Query string to prepare
     * @param $arguments Arguments
     * @return mixed+
     */
    public function executeQueryFromString($query, $arguments)
    {
        $query = $this->pdo_connection->prepare($query);
        return $query->execute($arguments);
    }

    /**
     * Execute a query from a query object
     * @param $query
     * @return mixed
     */
    public function executeQuery($query)
    {
        $prepared = $this->pdo_connection->prepare($query->getString());
        $query->bindParameters($prepared);
        return $prepared->execute();
    }

    /**
     * Add new connection option
     * @param $key
     * @param $value
     */
    public function setConnectionOption($key, $value)
    {
        $this->connection_options[$key] = $value;
    }

    /**
     * Start the connection between PHP and MySQL
     */
    public function connect()
    {
        try
        {
            $this->pdo_connection = new PDO($this->getDns(), $this->user, $this->password, $this->connection_options);
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
        return 'mysql:host='.$this->host.';dbname='.$this->name.(intval($this->port) > 0 ? ';port='.$this->port : '');
    }

    /**
     * Send SQL database creation query
     * @param bool $create_tables
     */
    public function createDatabase($create_tables = true)
    {
        if(sizeof($this->getTables()) === 0)
            $this->init();

        $query = new Query(new PDODirectConnection($this->host, $this->user, $this->password, $this->port));
        $query->fromString("CREATE DATABASE IF NOT EXISTS ".$this->name." DEFAULT CHARACTER SET ".$this->charset.($this->collate !== "" ? " COLLATE ".$this->collate : ""))->execute(QueryResultType::NONE);

        if($create_tables)
        {
            foreach($this->getTables() as $table)
                $table->createTable();
        }
    }

    /**
     * Find all fixtures xml files
     */
    public function findFixturesFiles()
    {
        $files = array();
        $path = ORION_MODELS_DIR.'/'.strtolower($this->getName()).'/fixture';
        $filelist = new DirectoryIterator($path);
        foreach($filelist as $file)
        {
            if ($file->isDot())
                continue;

            if(!$file->isDir())
            {
                $files[] = $path.'/'.$file->getFilename();
            }
        }
        return $files;
    }

        /**
     * Find all databases by browsing the model directory
     */
    static public function findDatabases()
    {
        $databases = array();
        $filelist = new DirectoryIterator(ORION_MODELS_DIR);
        foreach($filelist as $file)
        {
            if ($file->isDot())
                continue;

            if($file->isDir() && FileSystem::checkFile(ORION_MODELS_DIR.'/'.$file->getFilename().'/'.static::DATABASE_XML_PREFIX.'.'.$file->getFilename().'.xml'))
            {
                $databases[] = $file->getFilename();
            }
        }
        return $databases;
    }

    /**
     * Get the content of the xml schema file for a given database
     * @param $db
     * @throws FileDoesNotExistException
     * @return bool|string
     */
    static public function getSchemaFileContent($db)
    {
        $path = ORION_MODELS_DIR.'/'.$db.'/'.static::DATABASE_XML_PREFIX.'.'.$db.'.xml';
        if(!FileSystem::checkFile($path))
            throw(new FileDoesNotExistException("Impossible to read database schema file. Supposed to be found at: ".$path));
        return FileSystem::readFile($path);
    }

    /**
     * Returns a given database
     * @param $key
     * @return mixed
     * @throws
     */
    static public function getDatabase($key)
    {
        if(isset(Database::$databases[$key]))
            return Database::$databases[$key];
        else
        {
            $classname = ucfirst($key).'Database';
            if(class_exists($classname))
            {
                Database::$databases[$key] = new $classname();
                Database::$databases[$key]->init();
                return Database::$databases[$key];
            }
            else
                throw(new DatabaseNotFoundException("Database not found: ".$key." (classname: ".$classname.")"));
        }
    }

}