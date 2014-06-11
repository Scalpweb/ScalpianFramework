<?php

class CaptchaField extends Field
{

	/**
	 * Validate field's value
	 */
	public function validate()
	{
		$formReference = $this->getForm()->getName();
		if (!CaptchaHelper::checkCaptcha($formReference, $this->getValue()))
		{
			$this->valid = false;
			return 'NOT_VALID_CAPTCHA';
		}
		CaptchaHelper::clearSession($formReference);

		$validated = parent::validate();
		return $validated;
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

		$html = '<img ' . $attributes_str . ' id="captch-field-' . $this->getName() . '" src="/Batch/Image/Captcha/' . $this->getForm()->getName() . '" /><a href="" onclick="jQuery(\'#captch-field-' . $this->getName() . '\').attr(\'src\', \'/Batch/Image/Captcha/' . $this->getForm()->getName() . '\'); return false;">[Refresh]</a><br />';
		$html .= '<input type="text" placeholder="' . $this->getText() . '" name="' . $this->getName() . '" ' . $attributes_str . 'value="' . ($this->getValue()) . '" />';
		return $html;
	}

}