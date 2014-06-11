<?php

class RelationFactory
{

	/**
	 * Create a new relation between 2 tables
	 * @param $type
	 * @param $tableOne
	 * @param $tableTwo
	 * @param $field1
	 * @param $field2
	 * @param $alias
	 * @param $on_delete
	 * @param $on_update
	 * @throws WrongRelationTypeException
	 * @throws UnknownRelationException
	 * @return bool|ManyToManyRelation|ManyToOneRelation|OneToManyRelation|OneToOneRelation
	 */
	static public function create($type, $tableOne, $tableTwo, $field1, $field2, $alias, $on_delete, $on_update)
	{
		if ($type === RelationType::ManyToMany && $alias === '')
			throw(new WrongRelationTypeException("You must set an alias for a ManyToManYRelation"));
		switch ($type)
		{
			case RelationType::OneToOne:
				return new OneToOneRelation($tableOne, $tableTwo, $field1, $field2, $alias, $on_delete, $on_update);
			case RelationType::OneToMany:
				return new OneToManyRelation($tableOne, $tableTwo, $field1, $field2, $alias, $on_delete, $on_update);
			case RelationType::ManyToOne:
				return new ManyToOneRelation($tableOne, $tableTwo, $field1, $field2, $alias, $on_delete, $on_update);
			case RelationType::ManyToMany:
				return new ManyToManyRelation($tableOne, $tableTwo, $field1, $field2, $alias, $on_delete, $on_update);
			default:
				throw(new UnknownRelationException("Unknown relation type: [" . $type . "]"));
				return false;
		}
	}

}