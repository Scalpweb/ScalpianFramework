<?php

class RequiredValidator extends Validator
{

	public function validate()
	{
		return $this->field->getValue() !== '' ? true : 'FIELD_REQUIRED';
	}

}