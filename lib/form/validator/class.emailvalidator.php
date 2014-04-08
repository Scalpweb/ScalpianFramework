<?php

class EmailValidator extends Validator
{

    public function validate()
    {
        return filter_var($this->field->getValue(), FILTER_VALIDATE_EMAIL) ? true : 'EMAIL_NOT_VALID';
    }

}