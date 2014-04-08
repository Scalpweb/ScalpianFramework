<?php

class SubmitField extends Field
{

    private $text = '';

    public function __construct($_form, $_name, $_text)
    {
        $this->text = $_text;
        parent::__construct($_form, $_name);
    }

    /**
     * Get element html
     */
    public function getHtml($attributes)
    {
        $attributes_str = '';
        $this->attributes = OrionTools::mergeAttributes($this->attributes, $attributes);
        foreach($this->attributes as $key=>$val)
            $attributes_str .= $key.'="'.addslashes($val).': ';
        return '<input type="submit" name="'.$this->getName().'" '.$attributes_str.'value="'.($this->text).'" />';
    }

}