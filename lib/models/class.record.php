<?php

class Record
{

    // Each record is identified by an instance id, to ensure they have an unique id before being persistent.
    // We keep track of every instantiated record by counting each of them to be able to provide this unique id.
    protected static $instance_count = 0;

    private $instanceId, $name, $table, $values = array(), $relations = array(), $initial_values = array(), $initial_relations = array();

    public function __construct($_name, $_table, $_id = 0)
    {
        static::$instance_count ++;
        $this->instanceId = static::$instance_count;

        $this->name = $_name;
        $this->table = $_table;

        // Load record from database
        if(intval($_id) > 0)
        {
            $result = $this->table->fetchOneBy('id', $_id, QueryResultType::PDO_ARRAY);
            if($result === null)
                throw(new UnfoundRecordException("The record from table ".$this->getTable()->getName()." with id ".$_id." does not exist."));
            self::createObjectFromArray($this, $result, '');
        }
    }

    /**
     * Returns record's table
     * @return mixed
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Make this record persistent
     * @param bool $update_loaded_related_objects If sets to true, related objects through relations are also updated
     * @param array $exceptions List of record that will not be updated through relation updating. This must be an array of key=>pair arrays under the form array('table' => $tableName, 'id' => $recordId)
     * @param array $relation_exceptions List of relations that will not be updated. This must be an array of relation identifiers (Relation::getIdentifier)
     */
    public function save($update_loaded_related_objects = true, $exceptions = array(), $relation_exceptions = array())
    {
        // Test if current record is into the exceptions list
        foreach($exceptions as $elem)
        {
            if($elem['table'] === $this->table->getName() && $elem['id'] === $this->instanceId)
                return;
        }

        //
        // Make rows persistent
        //
        $query = new Query($this->getTable()->getDatabase());
        $fields = array();
        foreach($this->getTable()->getRows() as $key=>$value)
        {
            $fields[$key] = Row::formatValue($value->getType(), $this->__get($key));
        }
        $insert_id = $query->insert($this->getTable(), $fields, true)->execute();
        $this->__set('id', $insert_id);

        // Add current record to exception list, to be sure we won't enter an infinite loop
        $exceptions[] = array('table' => $this->table->getName(), 'id' => $this->instanceId);

        //
        // Dispatch relations
        //
        foreach($this->getTable()->getRelations() as $key=>$value)
        {
            if(isset($this->relations[$key]))
                $value->dispatch($this, $this->__get($key), $this->initial_values);
        }

        //
        // Triggering update events
        //
        foreach($this->getTable()->getRelations() as $key=>$value)
        {
            // Apply update effect to relation
            $value->update($this, $this->initial_values, $update_loaded_related_objects, $exceptions, $relation_exceptions);
        }

        //
        // Make relations persistent
        //
        foreach($this->getTable()->getRelations() as $key=>$value)
        {
            // Update loaded related objects if needed
            if($update_loaded_related_objects && isset($this->relations[$key]))
            {
                // One to many or many to many relations
                if(is_array($this->relations[$key]))
                {
                    foreach($this->relations[$key] as $item)
                        $item->save($update_loaded_related_objects, $exceptions, $relation_exceptions);
                }
                // OneToOne or ManyToMany relations
                else
                    $this->relations[$key]->save($update_loaded_related_objects, $exceptions, $relation_exceptions);
            }
        }

        //
        // Save relations
        //
        foreach($this->getTable()->getRelations() as $key=>$value)
        {
            if(isset($this->relations[$key]))
            {
                $distant = $this->__get($key);
                $relation_id = $value->getIdentifier($this, $distant);
                if(!in_array($relation_id, $relation_exceptions))
                {
                    $value->save($this, $distant, $this->initial_values, $this->initial_relations);
                    $relation_exceptions[] = $relation_id;
                }
            }
        }

        //
        // Keep track of value change
        //
        $this->initial_values = array();
        $this->initial_relations = array();

    }

    /**
     * Delete this record
     */
    public function delete()
    {
        foreach($this->getTable()->getRelations() as $value)
        {
            // Apply deletion effect to relation
            $value->delete($this, $this->initial_values);
        }

        $this->getTable()->deleteRecord($this->__get('id'));
    }

    /**
     * Fill record value from an associative array
     * @param $record
     * @param $array
     * @param string $fields_alias
     * @param array $eagerLoading
     * @param bool $only_one
     * @return mixed
     */
    static public function createObjectFromArray($record, $array, $fields_alias = '', $eagerLoading = array(), $only_one = true)
    {
        if(is_array($eagerLoading) && sizeof($eagerLoading) > 0)
        {
            $className = get_class($record);
            $record = static::createObjectFromArrayWithEagerLoading(new $className(), $array, $eagerLoading, $fields_alias === '' ? $record->getTable()->getName() : $fields_alias, $only_one);
            return $record;
        }

        $tblName = $fields_alias === '' ? '' : ($fields_alias);
        $rows = array_merge($record->getTable()->getRows(), array('id'));
        foreach($rows as $key=>$value)
        {
            if(isset($array[$tblName.$key]))
            {
                if(!isset($record->values[$key]))
                {
                    $record->initial_values[$key] = $array[$tblName.$key];
                }
                $record->values[$key] = $array[$tblName.$key];
            }
        }
        return $record;
    }

    /**
     * Fill record value and do eager loading from an array containing associative arrays
     * @param $record
     * @param $array
     * @param $eagerLoading
     * @param string $current_alias
     * @param bool $first_only
     * @param array $condition
     * @return array
     */
    static public function createObjectFromArrayWithEagerLoading($record, $array, $eagerLoading, $current_alias = '', $first_only = false, $condition = array())
    {
        // Look for instances of get_class($record) objects into $array
        $objects = array();
        $className = get_class($record);
        $found_ids = array();
        foreach($array as $line)
        {
            // Checks if record is not null and not already added
            if($line[$current_alias.'_id'] != '' && !in_array($line[$current_alias.'_id'], $found_ids))
            {
                $found_ids[] = $line[$current_alias.'_id'];

                // Instantiate objects
                if(sizeof($condition) == 0)
                    $objects[] = static::createObjectFromArray(new $className(), $line, $current_alias.'_');
                elseif(sizeof($condition) == 2)
                {
                    $field = $condition[0];
                    if($line[$field] === $condition[1])
                        $objects[] = static::createObjectFromArray(new $className(), $line, $current_alias.'_');
                }
            }
        }

        // Do eager loading for all instances
        foreach($objects as $instance)
        {
            if(is_array($eagerLoading))
            {
                foreach($eagerLoading as $key=>$value)
                {
                    $relationName = is_array($value) ? $key : $value;
                    $relationObject = $record->getTable()->getRelation($relationName);
                    $relationAlias = $current_alias.'_to_'.$relationName;
                    $objectName = $relationObject->getTable2()->getName();
                    $field1 = $relationObject->getField1();
                    $relationCondition = array($current_alias.'_'.$field1, $instance->$field1);

                    // Go one level deeper by doing eager load for that relation
                    $instance->$relationName = $relationObject->formatEagerLoadingResult(static::createObjectFromArrayWithEagerLoading(new $objectName(), $array, $value, $relationAlias, false, $relationCondition));
                }
            }
        }

        // If we only want one result, return first instance
        if($first_only)
        {
            return $objects[0];
        }

        return $objects;
    }

    /**
     * Returns true if "$key" is already loaded
     * @param $key
     * @return bool
     */
    public function hasPreviouslyLoaded($key)
    {
        return isset($this->relations[$key]) || isset($this->values[$key]);
    }

    /**
     * Return the indexes of a given relation
     * @param $key
     * @param $field
     * @return array
     */
    public function getRelationIndexes($key, $field)
    {
        if(!isset($this->relations[$key]))
            return array();

        if(!is_array($this->relations[$key]))
        {
            $value = array();
            foreach($this->relations[$key] as $elem)
                $value[] = $elem;
            return $value;
        }

        return array($this->relations[$key]->$field);
    }

    /**
     * Force relations values to be fetch back from the database on next __get()
     */
    public function updateRelations()
    {
        $this->relations = array();
    }

    /*************************
     * Magics functions
     *************************/

    /**
     * @param string $name
     * @return mixed
     * @throws UnknownPropertyException
     */
    public function &__get($name)
    {
        // Look for fields
        foreach($this->getTable()->getRows() as $key=>$value)
        {
            if($key === $name)
            {
                $this->values[$key] = isset($this->values[$key]) ? $this->values[$key] : null;
                return $this->values[$key];
            }
        }

        // Look for relations
        foreach($this->getTable()->getRelations() as $key=>$value)
        {
            if($key === $name)
            {
                // Load the relation from database if needed
                if(!isset($this->relations[$key]))
                    $this->relations[$key] = $value->loadFromDatabase($this);
                return $this->relations[$key];
            }
        }

        throw(new UnknownPropertyException("The property [".$name."] does not exist on record[".get_class($this)."]."));
    }

    /**
     * @param string $name
     * @param $newvalue
     * @throws UnknownPropertyException
     * @throws ObjectTypeIsNotValidException
     * @internal param mixed $value
     */
    public function __set($name, $newvalue)
    {
        // Look for fields
        foreach($this->getTable()->getRows() as $key=>$value)
        {
            if($key === $name)
            {
                // If not set yet, saved value as initial_value
                if(!isset($this->values[$key]))
                {
                    $this->initial_values[$key] = $newvalue;
                }
                $this->values[$key] = $newvalue;
                return;
            }
        }
        // Look for relations
        foreach($this->getTable()->getRelations() as $key=>$value)
        {
            if($key === $name)
            {
                if($newvalue != null && !$value->isObjectTypeValid($newvalue))
                    throw(new ObjectTypeIsNotValidException("This relation (".get_class($this).'->'.$key.") cannot accept value of type [".get_class($newvalue)."]"));
                if(get_class($value) === 'OneToOneRelation' || get_class($value) === 'ManyToOneRelation')
                {
                    $f2 = $value->getField2();
                    if(isset($newvalue->$f2))
                    {
                        if($f2 === 'id')
                            $this->values[$value->getField1()] = $newvalue->$f2;
                        else
                        {
                            if(get_class($value) === 'OneToOneRelation' && isset($this->values[$value->getField1()]))
                                $newvalue->$f2 = $this->values[$value->getField1()];
                        }
                    }
                }
                if(!isset($this->initial_relations[$key]))
                    $this->initial_relations[$key] = $this->__get($key);
                $this->relations[$key] = $newvalue;
                return;
            }
        }

        throw(new UnknownPropertyException("The property [".$name."] does not exist for ".get_class($this)));
    }

    /**
     * Return this record as string
     * @return string
     */
    public function __toString()
    {
        return OrionTools::print_r($this, 6, true, false);
    }

}