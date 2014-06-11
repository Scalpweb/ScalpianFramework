<?php

interface CacheLayer
{
	static public function get($key);

	static public function set($key, $value, $ttl);

	static public function exists($key);

	static public function delete($key);
}