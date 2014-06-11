<?php

class MagicHelper
{

	public static function getClassesWithCronInstaller()
	{
		$crons = self::getClassesWithInstaller('__cron');
		Cache::getInstance()->set('crons.installer', $crons, 0);
		return $crons;
	}

	public static function getClassesWithCronInstallerFromCache()
	{
		$crons = Cache::getInstance()->get('crons.installer');
		if($crons === false)
			return self::getClassesWithCronInstaller();
		return $crons;
	}

	public static function getClassesWithInstaller($installer = '__install')
	{
		// -- Include all classes:
		$classes = FileSystem::search(ORION_MAIN_DIR, '/^(class|application|module|base|database|record|table|interface)\.(.*)\.php$/', true);
		foreach($classes as $file)
		{
			try{ require_once($file); }
			catch(Exception $e) {}
		}

		$result = array();
		$classes = get_declared_classes();
		foreach($classes as $class)
		{
			if(method_exists($class, $installer))
				$result[] = $class;
		}
		return $result;
	}

}