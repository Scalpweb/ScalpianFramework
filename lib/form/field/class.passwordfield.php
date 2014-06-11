<?php

class PasswordField extends Field
{


	public function getHtml($attributes)
	{
		$attributes_str = '';
		$this->attributes = OrionTools::mergeAttributes($this->attributes, $attributes);
		foreach ($this->attributes as $key => $val)
			$attributes_str .= $key . '="' . addslashes($val) . '" ';
		return '<input type="password" placeholder="' . $this->getText() . '" name="' . $this->getName() . '" ' . $attributes_str . ' value="' . ($this->getValue()) . '" />';
	}

}