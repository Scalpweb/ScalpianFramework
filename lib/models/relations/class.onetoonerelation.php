<?php

class OneToOneRelation extends Relation
{

    public function getType() { return RelationType::OneToOne; }

    /**
     * Tests if a given object type is valid for this type of relation
     * @param $object
     * @return bool
     */
    public function isObjectTypeValid($object)
    {
        return strtolower(get_class($object)).'table' === strtolower(get_class($this->getTable2()));
    }

    /**
     * Make sure the relation is working both ways
     * @param $record
     * @param $distant
     */
    public function dispatch($record, $distant)
    {
        $field1 = $this->getField1();
        $field2 = $this->getField2();
        $relation_name = $record->getTable()->getName();

        if($field2 !== 'id')
            $distant->$field2 = $record->$field1;
        $distant->$relation_name = $record;
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
        $f1 = $this->getField1();
        $f2 = $this->getField2();

        if($distant->$f2 !== $record->$f1 && $f2 !== 'id')
        {
            $query = new Query($this->getTable1()->getDatabase());
            $query->fromString("UPDATE ".$this->getTable2()->getName()." SET ".$f2." = '".addslashes($record->$f1)."' WHERE ".$f2." = ".$initial_values[$f1])->execute(QueryResultType::NONE);
            $distant->$f2 = $record->$f1;
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
        // If the value of the index did not change we leave
        $field = $this->getField2();
        $init_field = $this->getField1();
        if(!isset($initial_values[$init_field]) || $initial_values[$init_field] === $record->$init_field)
            return;

        // We cannot apply the changes if the targeted field is 'id'
        if($field === 'id')
            return;

        // If we need to update the relations:
        if($this->getOnUpdateAction() === RelationActions::CASCADE)
        {
            $items = $this->getTable2()->fetchAllBy($this->getField2(), $initial_values[$init_field]);
            foreach($items as $item)
            {
                $item->$field = $record->$init_field;
                $item->save($update_loaded_related_objects, $exceptions, $relation_exceptions);
            }
            return;
        }

        // If we need to reset the relations:
        if($this->getOnUpdateAction() === RelationActions::SET_DEFAULT)
        {
            $items = $this->getTable2()->fetchAllBy($field, $initial_values[$init_field]);
            foreach($items as $item)
            {
                $item->$field = $this->getTable2()->getRow($field)->getDefault();
                $item->save($update_loaded_related_objects, $exceptions, $relation_exceptions);
            }
            return;
        }

        // If we need to delete the relations:
        if($this->getOnUpdateAction() === RelationActions::SET_NULL)
        {
            $items = $this->getTable2()->fetchAllBy($field, $initial_values[$init_field]);
            foreach($items as $item)
            {
                $item->$field = null;
                $item->save($update_loaded_related_objects, $exceptions, $relation_exceptions);
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
            $items = $this->getTable2()->fetchAllBy($this->getField2(), $record->__get($init_field));
            foreach($items as $item)
                $item->delete();
            return;
        }

        // We cannot apply the changes if the targeted field is 'id'
        if($field === 'id')
            return;

        // If we need to reset the relations:
        if($this->getOnDeleteAction() === RelationActions::SET_DEFAULT)
        {
            $items = $this->getTable2()->fetchAllBy($field, $record->__get($init_field));
            foreach($items as $item)
            {
                $item->$field = $this->getTable2()->getRow($field)->getDefault();
                $item->save();
            }
            return;
        }

        // If we need to nullify the relations:
        if($this->getOnDeleteAction() === RelationActions::SET_NULL)
        {
            $items = $this->getTable2()->fetchAllBy($field, $record->__get($init_field));
            foreach($items as $item)
            {
                $item->$field = null;
                $item->save();
            }
            return;
        }
    }

    /**
     * Loads relation's record from database for a given record
     * @param $record
     * @return mixed|void
     * @throws WrongRelationTypeException
     */
    public function loadFromDatabase($record)
    {
        $distant_field = $this->getField1();
        if($record->$distant_field == '')
            return $this->getDefaultValue();
        $query = new Query($record->getTable()->getDatabase());
        $query->addSelect($this->getTable2()->getMySQlSelectStringFromAlias($this->getAlias()))
            ->addFrom($this->getTable2(), $this->getAlias())
            ->addWhere($this->getField2().'='.$record->$distant_field)
            ->limit(1);
        return $query->execute(QueryResultType::RECORD_OBJECT, true, $this->getTable2()->getRecordClass(), '');
    }

    /**
     * @return null|void
     */
    public function getDefaultValue()
    {
        return null;
    }

    /**
     * Format a result of eager loading to make it compatible with the relation
     * @param $result
     * @return array
     */
    public function formatEagerLoadingResult($result)
    {
        if(is_array($result))
            return $result[0];
        return $result;
    }

}