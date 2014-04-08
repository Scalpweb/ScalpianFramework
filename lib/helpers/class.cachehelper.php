<?php

class CacheHelper
{

    static public function get($key, $fct, $args = array(), $ttl = 0)
    {
        if(Cache::getInstance()->exists($key))
            return Cache::getInstance()->get($key);
        $result = call_user_func ($fct, $args);
        Cache::getInstance()->set($key, $result, $ttl);
        return $result;
    }

}