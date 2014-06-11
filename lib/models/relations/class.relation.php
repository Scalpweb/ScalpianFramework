<?php

class Relation
{

	private $table1, $table2, $field1, $field2, $alias, $on_delete_action, $on_update_action;

	public function __construct($tableOne, $tableTwo, $field1, $field2, $alias, $on_delete_action = RelationActions::NO_ACTION, $on_update_action = RelationActions::NO_ACTION)
	{
		$this->table1 = $tableOne;
		$this->table2 = $tableTwo;
		$this->field1 = $field1;
		$this->field2 = $field2;
		$this->alias = $alias;
		$this->on_delete_action = $on_delete_action;
		$this->on_update_action = $on_update_action;
	}

	final static public function getMirrorType($type)
	{
		switch ($type)
		{
			case RelationType::ManyToMany:
				return RelationType::ManyToMany;
			case RelationType::ManyToOne:
				return RelationType::OneToMany;
			case RelationType::OneToMany:
				return RelationType::ManyToOne;
			case RelationType::OneToOne:
				return RelationType::OneToOne;
		}
		throw(new WrongRelationTypeException("This type of relation is unknown"));
	}

	/**
	 * Returns the relation uniq identifier
	 * @param $record
	 * @param $distant
	 * @return string
	 */
	final public function getIdentifier($record, $distant)
	{
		$els = array($this->getTable1()->getName(),
			$this->getTable2()->getName(),
			$this->getField1(),
			$this->getField2(),
			$this->getAlias(),
			$this->getTable1()->getName() . '_' . $record->__get($this->getField1()));
		$distant_value = $distant;
		if (is_array($distant_value))
		{
			$str = '';
			foreach ($distant_value as $element)
			{
				$str .= ($str === '' ? '' : '-') . $element->__get('id');
			}
			$els[] = $str;
		} else
		{
			$els[] = $this->getTable2()->getName() . '_' . $distant_value->__get('id');
		}
		sort($els);
		return implode('_', $els);
	}

	/**
	 * Loads relation's record from database for a given record
	 * @param $record
	 * @throws WrongRelationTypeException
	 */
	public function loadFromDatabase($record)
	{
		throw(new WrongRelationTypeException("This relation type does not support database loading."));
	}

	/**
	 * Tests if a given object type is valid for this type of relation
	 * @param $object
	 * @return bool
	 */
	public function isObjectTypeValid($object)
	{
		return true;
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
		$alias = $baseName . '_to_' . $relationName;
		$query->addJoin(JoinType::LEFT_JOIN, $this->getTable2(), $alias, $alias . '.' . $this->getField2() . ' = ' . $previous_alias . $this->getField1());
	}

	/**
	 * Returns a list of record ids from table2 that table1 can be linked too
	 */
	public function getPossibleValues()
	{
		$list = array();
		foreach ($this->table2->fetchAll() as $record)
		{
			$str = $record->id;
			foreach ($this->table2->getRows() as $row)
				if ($row->getType() === RowType::VARCHAR)
					$str .= ' - ' . $record->__get($row->getName());
			$list[$record->id] = $str;
		}
		return $list;
	}

	public function getDefaultValue()
	{
		return null;
	}

	public function getTable1()
	{
		return $this->table1;
	}

	public function getTable2()
	{
		return $this->table2;
	}

	public function getField1()
	{
		return $this->field1;
	}

	public function getField2()
	{
		return $this->field2;
	}

	public function getAlias($real = false)
	{
		return $real ? ($this->alias == $this->table2->getName() ? '' : $this->alias) : $this->alias;
	}

	public function getOnDeleteAction()
	{
		return $this->on_delete_action;
	}

	public function getOnUpdateAction()
	{
		return $this->on_update_action;
	}

}