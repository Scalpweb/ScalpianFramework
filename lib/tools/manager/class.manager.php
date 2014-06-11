<?php

class Manager extends Singleton
{

	private $database = '';

	/**
	 * Get manager database
	 * @return bool|string
	 */
	public function getDatabase()
	{
		if ($this->database == '')
			$this->database = Configuration::getInstance()->get('Manager/Database', true);
		if ($this->database === false)
		{
			$dbs = CacheHelper::get('Manager/Databases', 'Database::findDatabases', array(), CachePeriod::ONE_DAY);
			if (sizeof($dbs) > 0)
				$this->database = $dbs[0];
		}
		return $this->database === false ? null : Database::getDatabase($this->database);
	}

	/**
	 * List all applications
	 * @return array
	 */
	public static function getApplications()
	{
		$app = array();
		$list = FileSystem::listDirectory(ORION_APP_DIR);
		foreach ($list as $dir)
		{
			if (class_exists(ucfirst($dir) . 'Application'))
			{
				$classname = ucfirst($dir) . 'Application';
				$app[] = new $classname();
			}
		}
		return $app;
	}

	/**
	 * List all modules
	 * @return array
	 */
	public static function getModules()
	{
		$apps = static::getApplications();
		$modules = array();
		foreach ($apps as $app)
		{
			$list = FileSystem::listDirectory(ORION_APP_DIR . '/' . strtolower($app->getName()) . '/modules');
			foreach ($list as $dir)
			{
				if (class_exists(ucfirst($dir) . ucfirst(strtolower($app->getName())) . 'Module'))
				{
					$classname = ucfirst($dir) . ucfirst(strtolower($app->getName())) . 'Module';
					$modules[] = new $classname($app, $dir);
				}
			}
		}
		return $modules;
	}

	/**
	 * List all actions
	 * @return array
	 */
	public static function getActions()
	{
		$modules = static::getModules();
		$actions = array();
		foreach ($modules as $mod)
		{
			foreach (get_class_methods($mod) as $method)
			{
				if (substr($method, 0, 6) == 'action')
					$actions[] = $method;
			}
		}
		return $actions;
	}

	/**
	 * List all tables
	 * @return array
	 */
	public static function getTables()
	{
		$tables = array();
		$dbs = Database::findDatabases();
		foreach ($dbs as $db)
		{
			$db = Database::getDatabase($db);
			foreach ($db->getTables() as $table)
				$tables[] = $table->getName();
		}
		return $tables;
	}

	/**
	 * Count rows from all databases
	 * @return int
	 */
	public static function getRowCount()
	{
		$result = 0;
		$dbs = Database::findDatabases();
		foreach ($dbs as $db)
		{
			$query = new Query(Database::getDatabase($db));
			$count = $query->fromString("SELECT SUM(TABLE_ROWS) AS cnt FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . $db . "';")->execute(QueryResultType::PDO_ARRAY, true);
			$result += $count['cnt'];
		}
		return $result;
	}

	/**
	 * Returns true if the manager is currently using default configuration
	 */
	public static function isUsingDefaultConfiguration()
	{
		return !User::getInstance()->isUsingDatabase() && User::getInstance()->getBasicUserLogin() == 'admin' && User::getInstance()->getBasicUserPassword() == 'admin';
	}

}