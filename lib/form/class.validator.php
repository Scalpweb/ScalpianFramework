<?php

abstract class Validator
{

    protected $form, $field;

    final public function init($_form, $_field)
    {
        $this->form = $_form;
        $this->field = $_field;
    }

    /**
     * Do field validation
     * @return bool
     */
    public function validate()
    {
        return true;
    }

    /**
     * Get field's parent form
     * @return mixed
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Get parent field
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

}