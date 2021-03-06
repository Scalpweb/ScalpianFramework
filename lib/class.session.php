<?php

class Session extends Singleton
{

	public function exists($ref)
	{
		return isset($_SESSION[$ref]);
	}

	public function set($ref, $value)
	{
		$_SESSION[$ref] = $value;
	}

	public function get($ref)
	{
		if (!isset($_SESSION[$ref]))
			trigger_error("Unknown session variable: " . $ref);
		return $_SESSION[$ref];
	}

	public function delete($ref)
	{
		if($this->exists($ref))
			unset($_SESSION[$ref]);
	}

}