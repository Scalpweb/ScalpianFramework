<?php

class Table
{

    protected static $db_name = '';
    protected static $tbl_name = '';

    private $name, $database, $rows = array(), $relations = array(), $indexes = array(), $engine, $charset, $collate;

    public function __construct($_name, $_database, $_engine = 'MyISAM', $_charset = 'utf8', $_collate = "")
    {
        $this->name = $_name;
        $this->database = $_database;
        $this->engine = $_engine;
        $this->charset = $_charset;
        $this->collate = $_collate;
    }

    static public function getTable($name)
    {
        $classname = ucfirst($name).'Table';
        if(!class_exists($classname))
            throw(new TableNotFoundException("This table does not exist: ".$classname));
        $db = $classname::getStaticDatabaseName();
        return Database::getDatabase($db)->getTable($name);
    }

    static public function getStaticTableName() { return static::$tbl_name; }
    static public function getStaticDatabaseName() { return static::$db_name; }

    /**
     * Add new row to table
     * @param $_name
     * @param $_type
     * @param bool $is_null
     * @param string $default
     * @param bool $auto_increment
     * @param bool $primary
     * @param string $length
     * @param string $comment
     * @param string $reference
     * @param bool $unsigned
     * @param bool $zerofill
     * @param bool $binary
     * @param bool $ascii
     * @param bool $unicode
     * @param string $enum
     * @param string $set
     * @throws
     * @return mixed
     */
    public function addRow($_name, $_type, $is_null = false, $default = '', $auto_increment = false, $primary = false,
                           $length = '', $comment = '', $reference = '', $unsigned = false, $zerofill = false, $binary = false,
                           $ascii = false, $unicode = false, $enum = '', $set = '')
    {
        if(isset($this->rows[$_name]))
            throw(RowAlreadyExistsException("A row named [".$_name."] already exists."));
        $this->rows[$_name] = new Row($_name, $_type);

        if($is_null)            $this->rows[$_name]->setNull(true);
        if($default != '')      $this->rows[$_name]->setDefault($default);
        if($auto_increment)     $this->rows[$_name]->setAutoIncrement(true);
        if($primary)            $this->rows[$_name]->setPrimary(true);
        if($comment != '')      $this->rows[$_name]->setComment($comment);
        if($reference != '')    $this->rows[$_name]->setReference($reference);
        if($length)             $this->rows[$_name]->setLength($length);
        if($unsigned)           $this->rows[$_name]->setUnsigned(true);
        if($zerofill)           $this->rows[$_name]->setZerofill(true);
        if($binary)             $this->rows[$_name]->setBinary(true);
        if($ascii)              $this->rows[$_name]->setAscii(true);
        if($unicode)            $this->rows[$_name]->setUnicode(true);
        if($enum != '')         $this->rows[$_name]->setEnum($enum);
        if($set != '')          $this->rows[$_name]->setSet($set);

        return $this->rows[$_name];
    }

    /**
     * Prepare a query on this table
     */
    public function query()
    {
        $query = new Query($this->getDatabase());
	    $query->addFrom($this);
	    return $query;
    }

    /**
     * Send SQL table creation query
     */
    public function createTable()
    {
        $query = new Query($this->getDatabase());
        $query->fromString("DROP TABLE IF EXISTS ".$this->getName())->execute(QueryResultType::NONE);

        $sql = 'CREATE TABLE '.$this->getName().' (';
        $t = 0;
        foreach($this->getRows() as $row)
        {
            $sql .= ($t === 0 ? '' : ', ').$row->getDefinitionQuery();
            $t++;
        }
        $sql .= ') ENGINE='.$this->engine.' DEFAULT CHARACTER SET='.$this->charset.($this->collate != '' ? ' COLLATE '.$this->collate : '');
        $query->fromString($sql)->execute(QueryResultType::NONE);

        foreach($this->getIndexes() as $index)
        {
            $query->fromString("CREATE ".$index['type']." ".$index['name']." ON ".$this->getName()."(".$index['field'].")")->execute(QueryResultType::NONE);
        }
    }

    /**
     * Get table size on disk
     */
    public function getSize()
    {
        $query = new Query($this->getDatabase());
        $result = $query->fromString("SELECT SUM(data_length + index_length) as lg FROM information_schema.tables WHERE table_name = '".$this->getName()."' AND table_schema = '".$this->getDatabase()->getName()."' GROUP  BY table_name")->execute(QueryResultType::PDO_ARRAY, true);
        return $result['lg'];
    }

    /**
     * @return array
     */
    public function getIndexes()
    {
        return $this->indexes;
    }

    /**
     * @param $name
     * @param $type
     * @param $field
     */
    public function addIndex($name, $type, $field)
    {
        $this->indexes[] = array('name' => $name, 'type' => $type, 'field' => $field);
    }

    /**
     * Returns database object
     * @return mixed
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Returns the class name of a table's record
     * @return string
     */
    public function getRecordClass()
    {
        return strtolower($this->name);
    }

    /**
     * Return a record from the table given a certain field value
     * @param $row
     * @param $value
     * @param int $result_type
     * @param array $eagerLoading
     * @return null
     */
    static public function fetchOneBy($row, $value, $result_type = QueryResultType::RECORD_OBJECT, $eagerLoading = array())
    {
        if(is_array($eagerLoading) && sizeof($eagerLoading) > 0)
        {
            $result = static::fetchAllBy($row, $value, QueryResultType::RECORD_OBJECT, $eagerLoading);
            return $result[0];
        }

        $query = new Query(Database::getDatabase(static::$db_name));
        $record = $query->addSelect(Database::getDatabase(static::$db_name)->getTable(static::$tbl_name)->getMySQlSelectStringFromAlias(Database::getDatabase(static::$db_name)->getTable(static::$tbl_name)->getName()))
            ->addFrom(Database::getDatabase(static::$db_name)->getTable(static::$tbl_name)->getName(), Database::getDatabase(static::$db_name)->getTable(static::$tbl_name)->getName())
            ->addWhere($row.' = '.Row::formatValue(Database::getDatabase(static::$db_name)->getTable(static::$tbl_name)->rows[$row]->getType(), $value))
            ->limit(1)
            ->execute($result_type, true, $result_type == QueryResultType::RECORD_OBJECT ? Database::getDatabase(static::$db_name)->getTable(static::$tbl_name)->getName() : '');
        return $record;
    }

    /**
     * Return records from the table given a certain field value
     * @param $row
     * @param $value
     * @param int $result_type
     * @param array $eagerLoading
     * @return null
     */
    static public function fetchAllBy($row, $value, $result_type = QueryResultType::RECORD_OBJECT, $eagerLoading = array())
    {
        $table = Database::getDatabase(static::$db_name)->getTable(static::$tbl_name);
        $alias = $table->getName();
        $select = $table->getMySQlSelectStringFromAlias($alias, (is_array($eagerLoading) && sizeof($eagerLoading) > 0) ? $alias.'_' : '');

        // Create base query
        $query = new Query(Database::getDatabase(static::$db_name));
        $query->addSelect($select)
            ->addFrom($alias, $alias)
            ->addWhere($alias.'.'.$row.' = '.Row::formatValue($table->rows[$row]->getType(), $value));

        // Add eager loading
        if(is_array($eagerLoading) && sizeof($eagerLoading) > 0)
        {
            $query->doEagerLoading($eagerLoading, $table, $alias);
        }

        // Execute query
        $record = $query->execute($result_type, false, $result_type == QueryResultType::RECORD_OBJECT ? $alias : '', (is_array($eagerLoading) && sizeof($eagerLoading) > 0) ? $alias.'_' : '', $eagerLoading);
        return $record;
    }

    /**
     * Return all records from the table
     * @param array $eagerLoading
     * @param int $result_type
     * @return null
     */
    static public function fetchAll($eagerLoading = array(), $result_type = QueryResultType::RECORD_OBJECT)
    {
        $table = Database::getDatabase(static::$db_name)->getTable(static::$tbl_name);
        $alias = $table->getName();
        $select = $table->getMySQlSelectStringFromAlias($alias, (is_array($eagerLoading) && sizeof($eagerLoading) > 0) ? $alias.'_' : '');

        // Create base query
        $query = new Query(Database::getDatabase(static::$db_name));
        $query->addSelect($select)
            ->addFrom($alias, $alias);

        // Add eager loading
        if(is_array($eagerLoading) && sizeof($eagerLoading) > 0)
        {
            $query->doEagerLoading($eagerLoading, $table, $alias);
        }

        // Execute query
        $record = $query->execute($result_type, false, $result_type == QueryResultType::RECORD_OBJECT ? $alias : '', (is_array($eagerLoading) && sizeof($eagerLoading) > 0) ? $alias.'_' : '', $eagerLoading);
        return $record;
    }

    /**
     * Delete an item from it's ID
     * @param $id
     */
    public function deleteRecord($id)
    {
        $query = new Query($this->database);
        $query->fromString("DELETE FROM ".$this->getName()." WHERE id = ".intval($id)." LIMIT 1")->execute(QueryResultType::NONE);
    }

    /**
     * Get an array of all rows
     * @return array
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * Returns all row names
     * @return array
     */
    public function getRowNames()
    {
        $result = array();
        foreach($this->getRows() as $row)
            $result[] = $row->getName();
        return $result;
    }

    /**
     * Returns a given row
     * @param $key
     * @return mixed
     * @throws
     */
    public function getRow($key)
    {
        if(!isset($this->rows[$key]))
            throw(new RowNotFoundException("Row not found: ".$key));
        return $this->rows[$key];
    }

    /**
     * Add new relation to this table
     * @param $type
     * @param $target
     * @param $local_field
     * @param $distant_field
     * @param string $alias
     * @param string $on_delete_action
     * @param string $on_update_action
     * @throws WrongRelationTypeException
     */
    public function addRelation($type, $target, $local_field, $distant_field, $alias = '', $on_delete_action = RelationActions::NO_ACTION, $on_update_action = RelationActions::NO_ACTION)
    {
        if($type === RelationType::ManyToMany && $alias === '')
            throw(new WrongRelationTypeException("You must set an alias for a ManyToManyRelation"));
        $alias = $alias == '' ?  $target->getName() : $alias;
        $this->relations[$alias] = RelationFactory::create($type, $this, $target, $local_field, $distant_field, $alias, $on_delete_action, $on_update_action);
    }

    /**
     * Returns record count
     */
    public function count()
    {
        $query = new Query($this->getDatabase());
        $query = $query->addSelect('COUNT(id) as cnt')->addFrom($this)->execute(QueryResultType::PDO_ARRAY, true);
        return $query['cnt'];
    }

    /**
     * Returns relations array
     * @return array
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * Get one relation
     * @param $alias
     * @throws RelationNotFoundException
     * @return
     */
    public function getRelation($alias)
    {
        if(is_a($alias, 'table'))
            $alias = $alias->getName();
        if(isset($this->relations[$alias]))
            return $this->relations[$alias];
        throw(new RelationNotFoundException("Relation [".$alias."] does not exist"));
    }

    /**
     * Returns a valid MySQL SELECT statement string
     * @param $table_alias
     * @param $alias_prepend
     * @param array $exclude
     * @internal param $alias
     * @return string
     */
    public function getMySQlSelectStringFromAlias($table_alias = '', $alias_prepend = '', $exclude = array())
    {
        $value = array();
        $dot = $table_alias === '' ? '' : '.';
        $ppo = $alias_prepend === '' ? '' : ' AS '.$alias_prepend;
        foreach($this->getRows() as $key=>$row)
        {
            if(!in_array($key, $exclude))
                $value[] = $table_alias.$dot.$key.$ppo.($ppo === '' ? '' : $key);
        }
        return implode(',', $value);
    }

    /**
     * Returns table name
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns table charset
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Returns table engine
     * @return string
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Magic methods for dynamic calling
     * @param $name
     * @param $args
     * @return null|record object
     * @throws UnknownMethodException
     */
    static public function __callStatic($name, $args)
    {
        if(substr($name, 0, 10) === 'fetchOneBy')
        {
            return self::fetchOneBy(strtolower(substr($name, 10)), $args[0], sizeof($args > 1) ? $args[1] : QueryResultType::RECORD_OBJECT, sizeof($args > 2) ? $args[2] : array());
        }
        else if(substr($name, 0, 10) === 'fetchAllBy')
        {
            return self::fetchAllBy(strtolower(substr($name, 10)), $args[0], sizeof($args > 1) ? $args[1] : QueryResultType::RECORD_OBJECT, sizeof($args > 2) ? $args[2] : array());
        }
        throw(new UnknownMethodException("The method ".$name." does not exist in ".static::$tbl_name));
    }

}