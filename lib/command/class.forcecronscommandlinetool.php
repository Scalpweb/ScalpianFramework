<?php

class ForceCronsCommandLineTool implements ICommandLineTool
{

	public function execute($options)
	{
		CommandLineHandler::line('');
		CommandLineHandler::line('Orion Framework CLI - Force crons');
		CommandLineHandler::line('');

		CommandLineHandler::line('+++ Saving cron installers');
		$installers = MagicHelper::getClassesWithCronInstaller();
		CommandLineHandler::line('Installers found: '.sizeof($installers));

		CommandLineHandler::line('');
		CommandLineHandler::line('+++ Execute crons');
		CronHandler::getInstance()->force(true);
	}

	public function getName()
	{
		return 'ForceCrons';
	}

	public function getDescription()
	{
		return 'Force each cron executions';
	}

}