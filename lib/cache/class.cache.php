<?php

class Cache extends Singleton
{

	private $cacheLayer;

	public function __construct()
	{
		if (Configuration::getInstance()->exists("Cache/Classname"))
		{
			try
			{
				$classname = Configuration::getInstance()->exists("Cache/Classname");
				if (!class_exists($classname))
					throw(new UnvalidClassnameException("The class name configured as cache object does not exist."));
				if (!in_array('IException', class_implements((string)$classname)))
					throw(new MissingInterfaceException("The class name configured as cache object must implements the IException interface."));
				$this->cacheLayer = new $classname();
			} catch (Exception $e)
			{
				throw($e);
			}
		} else
		{
			if (Configuration::getInstance()->get("Cache/UseAPC", true))
				$this->cacheLayer = new ApcCacheLayer();
			else
				$this->cacheLayer = new FileSystemCacheLayer();
		}
	}

	/**
	 * Gets a value from cache
	 * @param $key
	 * @return mixed
	 */
	public function get($key)
	{
		Logger::getInstance()->log(LoggerEntry::CACHE, 'Cache', 'Get key from cache: ' . $key);
		return $this->cacheLayer->get($key);
	}

	/**
	 * Saves a value into cache
	 * @param $key
	 * @param $value
	 * @param $ttl
	 * @return bool
	 */
	public function set($key, $value, $ttl = 0)
	{
		Logger::getInstance()->log(LoggerEntry::CACHE, 'Cache', 'Set key: ' . $key . ' with ttl: ' . $ttl);
		return $this->cacheLayer->set($key, $value, $ttl);
	}

	/**
	 * Tests if a key exists inside the cache
	 * @param $key
	 * @return bool
	 */
	public function exists($key)
	{
		$result = $this->cacheLayer->exists($key);
		Logger::getInstance()->log(LoggerEntry::CACHE, 'Cache', 'Try to find key [' . $key . '] into cache: ' . ($result ? 'true' : 'false'));
		return $result;
	}

	/**
	 * Delete a value from cache
	 * @param $key
	 * @return bool
	 */
	public function delete($key)
	{
		return $this->cacheLayer->delete($key);
	}

}