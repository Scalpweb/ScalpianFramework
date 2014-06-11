<?php

class LengthValidator extends Validator
{

	private $minimum;

	public function __construct($minimum)
	{
		$this->minimum = $minimum;
	}

	public function validate()
	{
		return strlen($this->field->getValue()) > $this->minimum ? true : 'LENGTH_REQUIRED';
	}

}