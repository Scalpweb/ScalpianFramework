<?php

class ManyToManyRelation extends Relation
{

    public function getType() { return RelationType::ManyToMany; }

    /**
     * Tests if a given object type is valid for this type of relation
     * @param $object
     * @return bool
     * @todo Finish this
     */
    public function isObjectTypeValid($object)
    {
        return is_array($object);
    }

    /**
     * Make sure the relation is working both ways
     * @param $record
     * @param $distant
     */
    public function dispatch($record, $distant)
    {
        // Dispatching not needed in ManyToManyRelation
    }

    /**
     * Make relation persistent
     * @param $record
     * @param $distant
     * @param $initial_values
     * @param $initial_relations
     */
    public function save($record, $distant, $initial_values, $initial_relations)
    {
        $field1 = $this->getField1();
        $field2 = $this->getField2();
        $relation_name = $this->getAlias();

         // Delete relations
        $query = new Query($record->getTable()->getDatabase());
        $query->fromString("DELETE FROM ".$this->getAlias()." WHERE ".$this->getAlias1Inner()." = ".$initial_values[$field1])->execute(QueryResultType::NONE);

        // Build relations:
        foreach($record->$relation_name as $element)
        {
            $query = new Query($record->getTable()->getDatabase());
            $query->fromString("INSERT INTO ".$this->getAlias()." SET id = NULL, ".$this->getAlias1Inner()." = ".$record->$field1.", ".$this->getAlias2Inner()." = ".$element->$field2)->execute(QueryResultType::NONE);
        }
    }

    /**
     * Update the relation on item update
     * @param $record
     * @param $initial_values
     * @param $update_loaded_related_objects
     * @param $exceptions
     * @param $relation_exceptions
     */
    public function update($record, $initial_values, $update_loaded_related_objects, $exceptions, $relation_exceptions)
    {
        $field = $this->getField2();
        $init_field = $this->getField1();

        // If we need to delete the relations:
        if($this->getOnUpdateAction() === RelationActions::CASCADE)
        {
            $query = new Query($record->getTable()->getDatabase());
            $query->fromString("UPDATE ".$this->getAlias()." SET ".$this->getAlias().'.'.$this->getAlias1Inner()." = ".$record->$init_field." WHERE ".$this->getAlias().'.'.$this->getAlias1Inner().' = '.$initial_values[$init_field])->execute(QueryResultType::NONE);
            return;
        }

        // If we need to reset the relations:
        if($this->getOnUpdateAction() === RelationActions::SET_DEFAULT)
        {
            $items = $this->getTable2()->fetchAllBy($field, $record->__get($init_field));
            foreach($items as $item)
            {
                $query = new Query($record->getTable()->getDatabase());
                $query->fromString("UPDATE ".$this->getAlias()." SET ".$this->getAlias().'.'.$this->getAlias1Inner()." = DEFAULT WHERE ".$this->getAlias().'.'.$this->getAlias1Inner().' = '.$initial_values[$init_field])->execute(QueryResultType::NONE);
            }
            return;
        }

        // If we need to nullify the relations:
        if($this->getOnUpdateAction() === RelationActions::SET_NULL)
        {
            $items = $this->getTable2()->fetchAllBy($field, $record->__get($init_field));
            foreach($items as $item)
            {
                $query = new Query($record->getTable()->getDatabase());
                $query->fromString("UPDATE ".$this->getAlias()." SET ".$this->getAlias().'.'.$this->getAlias1Inner()." = NULL WHERE ".$this->getAlias().'.'.$this->getAlias1Inner().' = '.$initial_values[$init_field])->execute(QueryResultType::NONE);
            }
            return;
        }
    }

    /**
     * Delete the relation on item update
     * @param $record
     * @param $initial_values
     */
    public function delete($record, $initial_values)
    {
        $field = $this->getField2();
        $init_field = $this->getField1();

        // If we need to delete the relations:
        if($this->getOnDeleteAction() === RelationActions::CASCADE)
        {
            $query = new Query($record->getTable()->getDatabase());
            $query->fromString("DELETE FROM ".$this->getAlias()." WHERE ".$this->getAlias().'.'.$this->getAlias1Inner().' = '.$record->$init_field)->execute(QueryResultType::NONE);
            return;
        }

        // If we need to reset the relations:
        if($this->getOnDeleteAction() === RelationActions::SET_DEFAULT)
        {
            $items = $this->getTable2()->fetchAllBy($field, $record->__get($init_field));
            foreach($items as $item)
            {
                $query = new Query($record->getTable()->getDatabase());
                $query->fromString("UPDATE ".$this->getAlias()." SET ".$this->getAlias().'.'.$this->getAlias1Inner()." = DEFAULT WHERE ".$this->getAlias().'.'.$this->getAlias1Inner().' = '.$record->$init_field)->execute(QueryResultType::NONE);
            }
            return;
        }

        // If we need to nullify the relations:
        if($this->getOnDeleteAction() === RelationActions::SET_NULL)
        {
            $items = $this->getTable2()->fetchAllBy($field, $record->__get($init_field));
            foreach($items as $item)
            {
                $query = new Query($record->getTable()->getDatabase());
                $query->fromString("UPDATE ".$this->getAlias()." SET ".$this->getAlias().'.'.$this->getAlias1Inner()." = NULL WHERE ".$this->getAlias().'.'.$this->getAlias1Inner().' = '.$record->$init_field)->execute(QueryResultType::NONE);
           }
            return;
        }
    }

    /**
     * Loads relation's record from database for a given record
     * @param $record
     * @return mixed|void
     * @throws WrongRelationTypeException
     * @todo Finish this
     */
    public function loadFromDatabase($record)
    {
        $distant_field = $this->getField1();
        if($record->$distant_field == '')
            return $this->getDefaultValue();
        $query = new Query($record->getTable()->getDatabase());
        $query->addSelect($this->getTable2()->getMySQlSelectStringFromAlias($this->getTable2()->getName()))
            ->addFrom($this->getAlias())
            ->addWhere($this->getAlias().'.'.$this->getAlias1Inner().'='.$record->$distant_field)
            ->addJoin(JoinType::LEFT_JOIN, $this->getTable2()->getName(), $this->getTable2()->getName(), $this->getTable2()->getName().'.'.$this->getField1().'='.$this->getAlias().'.'.$this->getAlias2Inner());
        return $query->execute(QueryResultType::RECORD_OBJECT, false, $this->getTable2()->getRecordClass(), '');
    }

    /**
     * @return null|void
     */
    public function getDefaultValue()
    {
        return array();
    }

    /**
     * Format a result of eager loading to make it compatible with the relation
     * @param $result
     * @return array
     */
    public function formatEagerLoadingResult($result)
    {
        if(!is_array($result))
            return array($result);
        return $result;
    }

    /**
     * Modify a query to insert this relation as eager loading
     * @param $query
     * @param $baseName
     * @param $relationName
     * @param $previous_alias
     */
    public function insertIntoQuery(&$query, $baseName, $relationName, $previous_alias)
    {
        $alias = $baseName.'_to_'.$relationName;
        // Add junction table
        $query->addJoin(JoinType::LEFT_JOIN, $this->getAlias(), $alias.'_junction', $alias.'_junction.'.$this->getAlias1Inner().' = '.$previous_alias.$this->getField1());
        // Add linked object table
        $query->addJoin(JoinType::LEFT_JOIN, $this->getTable2()->getName(), $alias, $alias.'.'.$this->getField2().' = '.$alias.'_junction.'.$this->getAlias2Inner());
    }

    /**
     * Returns the name of the filed on junction table for left table
     * @return string
     */
    private function getAlias1Inner()
    {
        return $this->getField1().'_'.$this->getTable1()->getName();
    }

    /**
     * Returns the name of the filed on junction table for right  table
     * @return string
     */
    private function getAlias2Inner()
    {
        return $this->getField2().'_'.$this->getTable2()->getName();
    }

}