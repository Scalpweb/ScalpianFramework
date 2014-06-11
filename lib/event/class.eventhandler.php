<?php

class EventHandler
{

	static private $_listeners = array();

	/**
	 * Add new event listener
	 * @param $type
	 * @param $listener
	 * @param int $priority
	 * @throws InvalidArgumentException
	 */
	static public function addListener($type, callable $listener, $priority = 0)
	{
		if (!is_string($type))
			throw(new InvalidArgumentException('Type must be a string'));
		if ($type === '')
			throw(new InvalidArgumentException('Type cannot be empty'));

		if (!isset(static::$_listeners[$type]))
			static::$_listeners[$type] = array();

		if (!isset(static::$_listeners[$type][$priority]))
			static::$_listeners[$type][$priority] = array();

		static::$_listeners[$type][$priority][] = $listener;
	}

	/**
	 * Trigger an event
	 * @param $type
	 * @param $source
	 * @throws Exception
	 */
	static public function trigger($type, $source)
	{
		Logger::getInstance()->log(LoggerEntry::MESSAGE, 'EventHandler', 'Triggering event: ' . $type);
		if (isset(static::$_listeners[$type]))
		{
			foreach (static::$_listeners[$type] as $priority_level => $value)
			{
				foreach (static::$_listeners[$type][$priority_level] as $listener)
				{
					$result = call_user_func($listener, $source);
					// Break is the listener returns false
					if ($result === false)
						return;
				}
			}
		}
	}

}