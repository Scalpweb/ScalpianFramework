<?php

abstract class Field
{

	protected $form, $value, $legend, $name, $validators = array(), $attributes = array(), $valid = true;

	public function __construct($_form, $_name)
	{
		$this->form = $_form;
		$this->name = $_name;
	}

	/**
	 * Add new validator to field
	 * @param $validator
	 */
	final public function addValidator($validator)
	{
		$validator->init($this->form, $this);
		$this->validators[] = $validator;
	}

	/**
	 * Validate field's value
	 */
	public function validate()
	{
		if (sizeof($this->validators) === 0)
			return true;
		foreach ($this->validators as $validator)
		{
			$element = $validator->validate();
			if ($element !== true)
			{
				$this->valid = false;
				return $element;
			}
		}
		return true;
	}

	/**
	 * Returns true if no error have been tagged by validators
	 * @return bool
	 */
	public function isValid()
	{
		return $this->valid;
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
		foreach ($this->attributes as $key => $val)
			$attributes_str .= $key . '="' . addslashes($val) . '" ';
		return '<input type="text" placeholder="' . $this->getText() . '" name="' . $this->getName() . '" ' . $attributes_str . 'value="' . ($this->getValue()) . '" />';
	}

	/**
	 * Sets an html attribute
	 * @param $key
	 * @param $value
	 * @return $this
	 */
	public function setAttribute($key, $value)
	{
		$this->attributes[$key] = $value;
		return $this;
	}

	/**
	 * Set field value
	 * @param $val
	 * @return $this
	 */
	public function setValue($val)
	{
		$this->value = $val;
		return $this;
	}

	/**
	 * Get field value
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Get field text
	 * @return mixed
	 */
	public function getText()
	{
		return $this->getForm()->getText($this->getName());
	}

	/**
	 * Set legend value
	 * @param $val
	 * @return $this
	 */
	public function setLegend($val)
	{
		$this->legend = $val;
		return $this;
	}

	/**
	 * Get legend value
	 */
	public function getLegend()
	{
		return $this->legend;
	}

	/**
	 * Return field's name
	 * @return mixed
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get parent form
	 * @return Form
	 */
	public function getForm()
	{
		return $this->form;
	}

}