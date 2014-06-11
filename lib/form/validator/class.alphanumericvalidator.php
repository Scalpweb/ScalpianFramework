<?php

class AlphaNumericValidator extends Validator
{

	public function validate()
	{
		return ctype_alnum($this->field->getValue()) ? true : 'NOT_ALPHANUMERIC';
	}

}