<?php

class FeedCacheCommandLineTool implements ICommandLineTool
{

	public function execute($options)
	{
		CommandLineHandler::line('');
		CommandLineHandler::line('Orion Framework CLI - FeedCache');
		CommandLineHandler::line('');

		Loader::getInstance()->feedCache();
	}

	public function getName()
	{
		return 'FeedCache';
	}

	public function getDescription()
	{
		return 'Look for all classes and store their paths into cache';
	}

}