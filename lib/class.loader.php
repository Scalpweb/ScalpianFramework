<?php

class Loader extends Singleton
{

    // Priority on Plugin dir for plugins autoloading
    private $directories = array(ORION_PLUGINS_DIR, ORION_APP_DIR, ORION_LIB_DIR, ORION_MODELS_DIR, ORION_WEB_DIR);
    private $founds = array();
    private $unfounds = array();
    private $mustSaveToCache = false;

    /**
     * Registers the class loader
     */
    static public function init()
    {
        spl_autoload_register(array(self::getInstance(), 'autoload'));
        register_shutdown_function(array(self::getInstance(), 'saveCache'));
        self::getInstance()->loadFromCache();
    }

    /**
     * Loads known paths from cache
     */
    public function loadFromCache()
    {
        $this->founds = Cache::getInstance()->get('cache_founds');
        $this->unfounds = Cache::getInstance()->get('cache_unfounds');
        if(!$this->founds)      $this->founds = array();
        if(!$this->unfounds)    $this->unfounds = array();
    }

    /**
     * Load a class file from name
     */
    public function autoload($class)
    {

        if(!class_exists($class, false) && !in_array($class, $this->unfounds))
        {
            $class = strtolower($class);

            // If we already know the path from a previous session, we load it
            if(array_key_exists($class, $this->founds))
            {
                include($this->founds[$class]);
            }
            // Else, we try to find a class file
            else
            {
                $possible_names = array($class.'.php', 'class.'.$class.'.php', 'application.'.$class.'.php', 'module.'.$class.'.php', 'base.'.$class.'.php', 'database.'.$class.'.php', 'record.'.$class.'.php', 'table.'.$class.'.php', 'interface.'.$class.'.php');
                $path = FileSystem::find($possible_names, $this->directories);

                if($path != false)
                {
                    include($path);
                    $this->founds[$class] = $path;
                }
                else
                {
                    $this->unfounds[] = $class;
                }

                // Mark session for future saving
                $this->mustSaveToCache = true;
            }
        }
    }

    /**
     * Saves found and unfound path lists to cache:
     */
    public function saveCache()
    {
        if($this->mustSaveToCache)
        {
            Cache::getInstance()->set('cache_founds', $this->founds);
            Cache::getInstance()->set('cache_unfounds', $this->unfounds);
        }
    }

}