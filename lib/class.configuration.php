<?php

class Configuration extends Singleton
{

	private $variables = array();

	public function init($configurationParameters = array())
	{
		$this->set("Routing/DefaultApplication", "public");
		$this->set("Routing/DefaultModule", "home");
		$this->set("Routing/DefaultAction", "index");
		$this->set("Routing/DefaultLayout", "default");
		$this->set("Cache/UseAPC", false);

		// Load configuration parameters:
		foreach ($configurationParameters as $key => $value)
		{
			if (is_string($key))
				Orion::getConfiguration()->getInstance()->set($key, $value);
			else
				throw(new WrongParameterTypeException("Configuration key invalid: the key must be a string."));
		}
	}

	public function getAll()
	{
		return $this->variables;
	}

	public function exists($key)
	{
		return isset($this->variables[$key]);
	}

	public function set($key, $value)
	{
		$this->variables[$key] = $value;
	}

	public function get($key, $no_error = false, $default = false)
	{
		if (!isset($this->variables[$key]))
		{
			if ($no_error)
				return $default;
			else
				throw(new MissingConfigurationVariableException("This configuration key is not found: " . $key));
		}
		return $this->variables[$key];
	}

}