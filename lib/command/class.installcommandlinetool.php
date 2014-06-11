<?php

class InstallCommandLineTool implements ICommandLineTool
{

	public function execute($options)
	{
		CommandLineHandler::line('');
		CommandLineHandler::line('Orion Framework CLI - Install');
		CommandLineHandler::line('');

		// -- Execute all __install methods:
		$classes = MagicHelper::getClassesWithInstaller();
		foreach($classes as $class)
		{
			CommandLineHandler::line('+++ Installing ['.$class.']');
			call_user_func(array($class, '__install'));
		}
	}

	public function getName()
	{
		return 'Install';
	}

	public function getDescription()
	{
		return 'Call __install methods on each classes, and store cron installers list on cache.';
	}

}