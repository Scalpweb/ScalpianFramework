<?php

class Query
{

    private $database, $reference, $selects = array(), $froms = array(), $firstFrom = '', $wheres = array(), $joins = array(), $limit = '', $order = '', $inserts = array(), $insert_table = '';
    private $insert_low_priority = false, $insert_delayed = false, $insert_ignore = false, $insert_replace_if_exists = false;
    private $querystring = '', $querystring_arguments = array(), $fromQueryString = false;

    public function __construct($database, $reference = '')
    {
        // Set query name
        if($reference == '')
            $reference = uniqid();
        $this->reference = $reference;
        $this->database = $database;
    }

    /**
     * Executes the query and returns result formatted as specified
     * @param int $result_type
     * @param bool $firstOnly
     * @param string $record_class
     * @param string $fields_alias
     * @param array $eagerLoading
     * @throws UnknownTable
     * @throws UnvalidResultTypeException
     * @throws QueryNotValidException
     * @return mixed
     */
    public function execute($result_type = QueryResultType::PDO_OBJECT, $firstOnly = false, $record_class = '', $fields_alias = '', $eagerLoading = array())
    {
        if($this->fromQueryString && $result_type == QueryResultType::RECORD_OBJECT)
            throw(new UnvalidResultTypeException("Record object result type cannot be used with query made from string. Please use query builder's functions or change result type."));

        // Retrieve result type
        if($result_type == QueryResultType::PDO_OBJECT && $record_class == '')
        {
            if(!class_exists(ucfirst($this->firstFrom).'Table'))
                throw(new UnknownTable("The table [".ucfirst($this->firstFrom)."Table] is not part of the project model schema, and then cannot be used as result type. Please specify another result type."));
            $record_class = $this->firstFrom;
        }

        // Concat query string
        if(!$this->fromQueryString)
            $this->querystring = $this->concatQueryString();

        $query = $this->database->getConnection()->prepare($this->querystring);
        Logger::getInstance()->log(LoggerEntry::DATABASE, 'Query', 'Execute ['.$this->querystring.']');

        EventHandler::trigger(EventTypes::ORION_BEFORE_QUERY, null);
        try
        {
            if($this->fromQueryString)
                $query->execute($this->querystring_arguments);
            else
                $query->execute();
        }
        catch(Exception $e)
        {
            throw(new QueryNotValidException("The query [".$this->querystring."] returned the following error: ".$e));
        }
        EventHandler::trigger(EventTypes::ORION_AFTER_QUERY, null);

        if($this->isSelectQuery() || $this->fromQueryString)
        {
            switch($result_type)
            {
                case QueryResultType::NONE:
                    break;

                // Returns as RECORD object
                case QueryResultType::RECORD_OBJECT:
                    $result = $query->fetchAll(PDO::FETCH_ASSOC);
                    Logger::getInstance()->log(LoggerEntry::DATABASE, 'Query', 'Results count: ['.sizeof($result).']');
                    $final = array();
                    if(is_array($eagerLoading) && sizeof($eagerLoading) > 0)
                    {
                        $final = Record::createObjectFromArray(new $record_class(), $result, $record_class, $eagerLoading, false);
                    }
                    else
                    {
                        foreach($result as $line)
                        {
                            $final[] = Record::createObjectFromArray(new $record_class(), $line, $fields_alias);
                        }
                    }

                    if($firstOnly)
                        return sizeof($final) > 0 ? $final[0] : null;
                    return $final;

                // Returns as PDO object
                case QueryResultType::PDO_OBJECT:
                    $all = $query->fetchAll(PDO::FETCH_OBJ);
                    Logger::getInstance()->log(LoggerEntry::DATABASE, 'Query', 'Results count: ['.sizeof($all).']');
                    if($firstOnly)
                    {
                        return sizeof($all) > 0 ? $all[0] : null;
                    }
                    return $all;

                // Returns as PDO array
                case QueryResultType::PDO_ARRAY:
                    $all = $query->fetchAll(PDO::FETCH_ASSOC);
                    Logger::getInstance()->log(LoggerEntry::DATABASE, 'Query', 'Results count: ['.sizeof($all).']');
                    if($firstOnly)
                    {
                        return sizeof($all) > 0 ? $all[0] : null;
                    }
                    return $all;
            }
            return null;
        }
        elseif($this->isInsertQuery())
        {
            return $this->database->getConnection()->lastInsertId();
        }
        return true;
    }

    /**
     * Add eagar loading to query
     * @param $eager
     * @param $table
     * @param string $baseName
     * @internal param $baseClass
     * @return $this
     */
    public function doEagerLoading($eager, $table, $baseName = '')
    {
        foreach($eager as $key=>$value)
        {
            // Do relation
            $relationName = is_array($value) ? $key : $value;
            $relationObject = $table->getRelation($relationName);
            $alias = $baseName.'_to_'.$relationName;
            $previous_alias = $baseName === '' ? '' : ($baseName.'.');

            $relationObject->insertIntoQuery($this, $baseName, $relationName, $previous_alias);
            $this->addSelect($relationObject->getTable2()->getMySQlSelectStringFromAlias($baseName.'_to_'.$relationName, $baseName.'_to_'.$relationName.'_'));

            // Go next level is needed
            if(is_array($value))
            {
                $this->doEagerLoading($value, $relationObject->getTable2(), $alias);
            }
        }
        return $this;
    }

    /**
     * Escapes an int
     * @param $value
     * @return int
     */
    static public function escapeInt($value)    { return intval($value); }

    /**
     * Escapes a float
     * @param $value
     * @return float
     */
    static public function escapeFloat($value)  { return floatval($value); }

    /**
     * Escapes a string
     * @param $value
     * @return string
     */
    static public function escapeString($value) { return addslashes($value); }

    /**
     * Tests if current query is a SELECT
     * @return bool
     */
    private function isSelectQuery()
    {
        return sizeof($this->selects) > 0;
    }

    /**
     * Tests if current query is an INSERT
     * @return bool
     */
    private function isInsertQuery()
    {
        return $this->insert_table != null;
    }

    /**
     * Concats query string from data stored by query builder's functions
     * @throws QueryNotValidException
     * @return string
     */
    private function concatQueryString()
    {
        $query = '';

        // Priority to select
        if($this->isSelectQuery())
        {
            // Create select query
            $query .= 'SELECT '.$this->formatSelects();
            $query .= ' FROM '.$this->formatFroms();
            if(sizeof($this->joins) > 0)    $query .= $this->formatJoins();
            if(sizeof($this->wheres) > 0)   $query .= ' WHERE '.$this->formatWheres();
            if($this->limit != '')          $query .= ' LIMIT '.$this->limit;
            if($this->order != '')          $query .= ' ORDER BY '.$this->order;
        }

        // Second priority to insert
        if($this->isInsertQuery())
        {
            if($this->insert_replace_if_exists && $this->insert_ignore)
                throw(new QueryNotValidException("REPLACE and IGNORE cannot be used together"));

            // Create insert query
            $query .= $this->insert_replace_if_exists ? 'REPLACE INTO' : 'INSERT INTO';
            if($this->insert_low_priority)  $query .= ' LOW_PRIORITY';
            if($this->insert_delayed)       $query .= ' DELAYED';
            if($this->insert_ignore)        $query .= ' IGNORE';
            $query .= ' '.$this->insert_table.' SET';
            $count = 0;
            foreach($this->inserts as $key=>$value)
            {
                $query .= ($count > 0 ? ',' : '').' '.$key.'='.$value;
                $count ++;
            }
        }

        return $query;
    }

    /**
     * Formats the JOIN group into one string
     * @return string
     */
    private function formatJoins()
    {
        $value = '';
        foreach($this->joins as $join)
        {
            $value .= ' '.$join['type'];
            $value .= ' '.$join['table'];
            if($join['alias'] != '')            $value .= ' AS '.$join['alias'];
            if($join['use_index'] != '')        $value .= ' USE INDEX '.$join['use_index'];
            if($join['ignore_index'] != '')     $value .= ' IGNORE INDEX '.$join['ignore_index'];
            if($join['on_statement'] != '')     $value .= ' ON '.$join['on_statement'];
            if($join['using_statement'] != '')  $value .= ' USING '.$join['using_statement'];
        }
        return $value;
    }

    /**
     * Formats the SELECT group items into one string
     * @return string
     */
    private function formatSelects()
    {
        $value = '';
        foreach($this->selects as $name=>$alias)
        {
            if($value != '')
                $value .= ', ';
            $value .= $name.($alias !== $name ? ' AS '.$alias : '');
        }
        return $value;
    }

    /**
     * Formats the FROM group items into one string
     * @return string
     */
    private function formatFroms()
    {
        $value = '';
        foreach($this->froms as $name=>$alias)
        {
            if($value != '')
                $value .= ', ';
            $value .= $name.($alias !== '' ? ' AS '.$alias : '');
        }
        return $value;
    }

    /**
     * Formats the WHERE group items into one string
     * @return string
     */
    private function formatWheres()
    {
        $value = '';
        foreach($this->wheres as $val)
        {
            if($value != '')
                $value .= ' AND ';
            $value .= $val;
        }
        return $value;
    }

    /**
     * Sets query from string
     * @param $query
     * @param string $arguments
     * @return $this
     */
    public function fromString($query, $arguments = '')
    {
        $this->fromQueryString = true;
        $this->querystring = $query;
        return $this;
    }

    /**
     * Adds a row into the query SELECT group
     * @param $name
     * @param $alias
     * @throws QueryStringAlreadySpecified
     * @throws RowAlreadyUsedException
     * @throws AliasAlreadyUsedException
     * @return $this
     */
    public function addSelect($name, $alias = '')
    {
        if(is_object($name))
            $name = $name->getName();

        // Check error
        if(isset($this->selects[$name]) && $alias == '')
            throw(new RowAlreadyUsedException("The row [".$name."] is already added to the query. Please use an alias."));
        if($alias != '' && in_array($alias, $this->selects))
            throw(new AliasAlreadyUsedException("The alias [".$alias."] is already used."));
        if($this->fromQueryString)
            throw(new QueryStringAlreadySpecified("The query string has already been specified. You can't access query builder's functions."));

        // Add table to FORM group
        $this->selects[$name] = $alias == '' ? $name : $alias;
        return $this;
    }

    /**
     * Set this query as an Insert query
     * @param $table
     * @param $fields
     * @param bool $replace_if_exists
     * @param bool $low_priority
     * @param bool $delayed
     * @param bool $ignore
     * @return $this
     * @throws UnvalidObjectException
     * @internal param bool $insert_low_priority
     * @internal param bool $insert_delayed
     * @internal param bool $insert_ignore
     */
    public function insert($table, $fields, $replace_if_exists = false, $low_priority = false, $delayed = false, $ignore = false)
    {
        if(is_object($table))
            $table = $table->getName();
        if(!is_array($fields))
            throw(new UnvalidObjectException('$fields must be an array.'));

        $this->insert_table = $table;
        $this->inserts = $fields;
        $this->insert_low_priority = $low_priority;
        $this->insert_delayed = $delayed;
        $this->insert_ignore = $ignore;
        $this->insert_replace_if_exists = $replace_if_exists;

        return $this;
    }

    /**
     * Adds a table into the query FORM group
     * @param $name
     * @param string $alias
     * @throws QueryStringAlreadySpecified
     * @throws TableAlreadyUsedException
     * @throws AliasAlreadyUsedException
     * @return $this
     */
    public function addFrom($name, $alias = '')
    {
        if(is_object($name))
            $name = $name->getName();

        // Check error
        if(isset($this->froms[$name]) && $alias == '')
            throw(new TableAlreadyUsedException("The table [".$name."] is already added to the query. Please use an alias."));
        if($alias != '' && in_array($alias, $this->froms))
            throw(new AliasAlreadyUsedException("The alias [".$alias."] is already used."));
        if($this->fromQueryString)
            throw(new QueryStringAlreadySpecified("The query string has already been specified. You can't access query builder's functions."));

        // Add table to FORM group
        $this->froms[$name] = $alias == '' ? $name : $alias;
        if($this->firstFrom == '')
            $this->firstFrom = $name;
        return $this;
    }

    /**
     * Adds a condition to the query WHERE group
     * @param $condition
     * @throws QueryStringAlreadySpecified
     * @return $this
     */
    public function addWhere($condition)
    {
        if($this->fromQueryString)
            throw(new QueryStringAlreadySpecified("The query string has already been specified. You can't access query builder's functions."));
        $this->wheres[] = $condition;
        return $this;
    }


    /**
     * Add a new join
     * @param $type
     * @param $table
     * @param $on_statement
     * @param $using_statement
     * @param string $alias
     * @param string $use_index
     * @param string $ignore_index
     * @return $this
     * @throws UnvalidObjectException
     * @throws UnknownJoinTypeException
     */
    public function addJoin($type, $table, $alias, $on_statement, $using_statement = '', $use_index = '', $ignore_index = '')
    {
        // @todo: verify join type
        if(is_object($table))
        {
            try
            {
                $table = $table->getName();
            }
            catch(Exception $e)
            {
                throw(new UnvalidObjectException("Table must be an object of type Table or a string"));
            }
        }

        $this->joins[] = array('type' => $type, 'table' => $table, 'on_statement' => $on_statement, 'using_statement' => $using_statement, 'alias' => $alias, 'use_index' => $use_index, 'ignore_index' => $ignore_index);
        return $this;
    }

    /**
     * Sets query LIMIT attribute
     * @param $start
     * @param string $end
     * @throws QueryStringAlreadySpecified
     * @internal param $a
     * @internal param $b
     * @return $this
     */
    public function limit($start, $end = '')
    {
        if($this->fromQueryString)
            throw(new QueryStringAlreadySpecified("The query string has already been specified. You can't access query builder's functions."));
        $this->limit = $start . ($end != '' ? ', '.$end : '');
        return $this;
    }

    /**
     * Sets query ORDER BY attribute
     * @param $value
     * @throws QueryStringAlreadySpecified
     * @return $this
     */
    public function orderBy($value)
    {
        if($this->fromQueryString)
            throw(new QueryStringAlreadySpecified("The query string has already been specified. You can't access query builder's functions."));
        $this->order = $value;
        return $this;
    }

}