<?php

class Loader extends Singleton
{

	// Priority on Plugin dir for plugins autoloading
	private $directories = array(ORION_PLUGINS_DIR, ORION_APP_DIR, ORION_LIB_DIR, ORION_MODELS_DIR);
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
		if (!$this->founds) $this->founds = array();
		if (!$this->unfounds) $this->unfounds = array();
	}

	/**
	 * Tests if a class exists, without including it
	 *
	 * @param $class
	 * @return bool
	 */
	public function classExists($class)
	{
		$oName = $class;
		$class = strtolower($class);

		if (in_array($class, $this->unfounds))
			return false;
		if (class_exists($oName, false))
			return true;
		if (array_key_exists($class, $this->founds))
			return true;

		$possible_names = array($class . '.php', 'class.' . $class . '.php', 'application.' . $class . '.php', 'module.' . $class . '.php', 'base.' . $class . '.php', 'database.' . $class . '.php', 'record.' . $class . '.php', 'table.' . $class . '.php', 'interface.' . $class . '.php');
		$path = FileSystem::find($possible_names, $this->directories);

		if ($path != false)
			return true;
		return false;
	}

	/**
	 * Recording a class path
	 *
	 * @param $className
	 * @param $path
	 */
	public function recordPath($className, $path)
	{
		$this->founds[strtolower($className)] = $path;
		$this->mustSaveToCache = true;
	}

	/**
	 * Load a class file from name
	 */
	public function autoload($class)
	{

		$oName = $class;
		$class = strtolower($class);
		if (!class_exists($oName, false) && !in_array($class, $this->unfounds))
		{

			// If we already know the path from a previous session, we load it
			if (array_key_exists($class, $this->founds))
			{
				require_once($this->founds[$class]);
			} // Else, we try to find a class file
			else
			{
				$possible_names = array($class . '.php', 'class.' . $class . '.php', 'application.' . $class . '.php', 'module.' . $class . '.php', 'base.' . $class . '.php', 'database.' . $class . '.php', 'record.' . $class . '.php', 'table.' . $class . '.php', 'interface.' . $class . '.php');
				$path = FileSystem::find($possible_names, $this->directories);

				if ($path != false)
				{
					require_once($path);
					$this->founds[$class] = $path;
				} else
				{
					$this->unfounds[] = $class;
				}

				// Mark session for future saving
				$this->mustSaveToCache = true;
			}
		}
	}

	/**
	 * Look for all classes and store their paths into cache:
	 */
	public function feedCache()
	{
		$this->clearCache();
		$classes = FileSystem::search(
			ORION_MAIN_DIR,
			'/^(class|application|module|base|database|record|table|interface)\.(.*)\.php$/',
			true
		);
		foreach($classes as $class)
		{
			$path = $class;
			$class = substr($class, strrpos($class, '\\'));
			$class = substr($class, strpos($class, '.') + 1, -4);
			$this->recordPath($class, $path);
		}
	}

	/**
	 * Clears found and unfound path lists from cache:
	 */
	public function clearCache()
	{
		$this->founds = array();
		$this->unfounds = array();
		Cache::getInstance()->delete('cache_founds');
		Cache::getInstance()->delete('cache_unfounds');
	}

	/**
	 * Saves found and unfound path lists to cache:
	 */
	public function saveCache()
	{
		if ($this->mustSaveToCache)
		{
			Cache::getInstance()->set('cache_founds', $this->founds);
			Cache::getInstance()->set('cache_unfounds', $this->unfounds);
		}
	}

	/**
	 * Feed cache on installation
	 */
	public static function __install()
	{
		self::getInstance()->feedCache();
		CommandLineHandler::line('Cache fed');
		CommandLineHandler::line('');
	}

}