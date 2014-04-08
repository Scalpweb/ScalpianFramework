<?php

class NumericValidator extends Validator
{

    public function validate()
    {
        if($this->field->getValue() == '') return true;
        return is_numeric($this->field->getValue()) ? true : 'NOT_NUMERIC';
    }

}