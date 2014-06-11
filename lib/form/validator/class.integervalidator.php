<?php

class IntegerValidator extends Validator
{

	public function validate()
	{
		if ($this->field->getValue() == '') return true;
		return intval($this->field->getValue()) === $this->field->getValue() ? true : 'NOT_INTEGER';
	}

}