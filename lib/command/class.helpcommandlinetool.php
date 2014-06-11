<?php

class HelpCommandLineTool implements ICommandLineTool
{

	public function execute($options)
	{
		CommandLineHandler::line('');
		CommandLineHandler::line('Orion Framework CLI - Help');
		CommandLineHandler::line('');

		$filelist = new DirectoryIterator(ORION_LIB_DIR . '/command');
		foreach ($filelist as $file)
		{
			if ($file->isDot())
				continue;

			if (!$file->isDir() && substr($file->getFilename(), -19) === "commandlinetool.php")
			{
				require_once(ORION_LIB_DIR . '/command/' . $file->getFilename());
			}
		}

		foreach (get_declared_classes() as $c)
		{
			if (strtolower(substr($c, -15)) === "commandlinetool")
			{
				$command = new $c(array());
				CommandLineHandler::line('- ' . $command->getName());
				CommandLineHandler::line('Description : ' . $command->getDescription(), 1);
				CommandLineHandler::line('');
			}
		}

	}

	public function getName()
	{
		return 'Help';
	}

	public function getDescription()
	{
		return 'Show this message.';
	}

}