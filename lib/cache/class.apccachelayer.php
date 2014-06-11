<?php

class ApcCacheLayer implements CacheLayer
{

	static private $prepender = 'orion_';

	static public function get($key)
	{
		return apc_fetch(self::$prepender . $key);
	}

	static public function set($key, $value, $ttl)
	{
		return apc_store(self::$prepender . $key, $value, $ttl);
	}

	static public function exists($key)
	{
		return apc_exists(self::$prepender . $key);
	}

	static public function delete($key)
	{
		return apc_delete(self::$prepender . $key);
	}

}