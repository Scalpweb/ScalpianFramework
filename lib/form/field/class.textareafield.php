<?php

class TextAreaField extends Field
{

	/**
	 * Get element html
	 * @param $attributes html attributes
	 * @return string
	 */
	public function getHtml($attributes)
	{
		$attributes_str = '';
		$this->attributes = OrionTools::mergeAttributes($this->attributes, $attributes);
		foreach ($this->attributes as $key => $val)
			$attributes_str .= $key . '="' . addslashes($val) . '" ';
		$html = '<textarea name="' . $this->getName() . '" ' . $attributes_str . '>' . $this->getValue() . '</textarea>';
		return $html;
	}

}