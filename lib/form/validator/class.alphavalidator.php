<?php

class AlphaValidator extends Validator
{

	public function validate()
	{
		return ctype_alpha($this->field->getValue()) ? true : 'NOT_ALPHA';
	}

}