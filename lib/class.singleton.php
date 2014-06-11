<?php

abstract class Singleton
{

	private static $instances;

	/**
	 * @return $this
	 */
	final public static function getInstance()
	{
		$className = get_called_class();

		if (isset(self::$instances[$className]) == false)
		{
			self::$instances[$className] = new static();
		}
		return self::$instances[$className];
	}

	protected function  __construct()
	{
	}

}