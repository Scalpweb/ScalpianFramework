<?php

class FileSystemCacheLayer implements CacheLayer
{

    static private $cachePath = ORION_CACHE_DIR;

    static public function get($key)
    {
        $key = static::fixKey($key);
	    if(!FileSystem::checkFile(self::$cachePath.'/'.$key.'.cache'))
		    return false;
        return unserialize(FileSystem::readFile(self::$cachePath.'/'.$key.'.cache'));
    }

    static public function set($key, $value, $ttl)
    {
        $value = serialize($value);
        $key = static::fixKey($key);
        return FileSystem::writeFile(self::$cachePath.'/'.$key.'.cache', $value, false);
    }

    static public function exists($key)
    {
        $key = static::fixKey($key);
        return FileSystem::checkFile(self::$cachePath.'/'.$key.'.cache');
    }

    static public function delete($key)
    {
        $key = static::fixKey($key);
        return FileSystem::deleteFile(self::$cachePath.'/'.$key.'.cache');
    }

    static private function fixKey($key)
    {
        return strtr($key, "\\/ ?{}[]|=+()$#@!%^&*:;<>,`'\"",
                            "-----------------------------");
    }

}