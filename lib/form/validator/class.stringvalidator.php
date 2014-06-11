<?php

class StringValidator extends Validator
{

	public function validate()
	{
		return preg_match('/^([a-z0-9_]*)$/i', $this->field->getValue()) === 1 ? true : 'NOT_A_VALID_STRING';
	}

}