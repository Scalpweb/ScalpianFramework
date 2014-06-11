<?php

class SameValidator extends Validator
{

	private $parent;

	public function __construct($parent)
	{
		$this->parent = $parent;
	}

	public function validate()
	{
		return $this->field->getValue() == $this->parent->getValue() ? true : 'DIFFERENT_VALUE';
	}

}