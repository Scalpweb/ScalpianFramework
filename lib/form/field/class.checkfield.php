<?php

class CheckField extends Field
{

    protected $value = false;

    /**
     * Get element html
     * @param $attributes html attributes
     * @return string
     */
    public function getHtml($attributes)
    {
        $attributes_str = '';
        $this->attributes = OrionTools::mergeAttributes($this->attributes, $attributes);
        foreach($this->attributes as $key=>$val)
            $attributes_str .= $key.'="'.addslashes($val).'" ';
        $html = '<input type="checkbox" '.($this->getValue() ? 'checked="checked "' : '').'name="'.$this->getName().'" value="1" '.$attributes_str.' />';
        return $html;
    }

    /**
     * Get field value
     * @return bool
     */
    public function getValue()
    {
        return $this->value === false || $this->value === '' ? false : true;
    }

}