<?php

class MultiSelectField extends Field
{

    private $possible_values = array();

    public function __construct($_form, $_name, $_possible_values)
    {
        $this->form = $_form;
        $this->name = $_name;
        $this->possible_values = $_possible_values;
    }

    /**
     * Validate field's value
     */
    public function validate()
    {
        if(!parent::validate())
            return false;
        foreach($this->possible_values as $k => $v)
            if(in_array($k, $this->getValue()))
                return true;
        $this->valid = false;
        return false;
    }

    /**
     * Get element html
     * @param $attributes html attributes
     * @return string
     */
    public function getHtml($attributes)
    {
        $attributes_str = '';
        $this->attributes = OrionTools::mergeAttributes($this->attributes, $attributes);
        $this->attributes = OrionTools::mergeAttributes($this->attributes, array('class' => 'multiple'));
        foreach($this->attributes as $key=>$val)
            $attributes_str .= $key.'="'.addslashes($val).'" ';
        $html = '<select multiple="multiple" name="'.$this->getName().'" '.$attributes_str.'>';
        foreach($this->possible_values as $k=>$v)
        {
            $html .= '<option value="'.$k.'"'.(in_array($k, $this->getValue()) ? ' selected="selected"' : '').'>'.$v.'</option>';
        }
        return $html.'</select>';
    }

}