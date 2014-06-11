<?php

class CommandLineHandler
{

	private $options = array();

	/**
	 * Executing command based on argument
	 */
	public function dispatch()
	{
		Logger::getInstance()->setErrorHandler(new CliErrorHandler());
		Logger::getInstance()->setCli(true);
		$this->readOptions();
		if (sizeof($this->options) < 1)
		{
			trigger_error('You must send at least one argument to specify which command to execute.', E_USER_ERROR);
			die();
		}

		$command = $this->options['c'] . 'CommandLineTool';
		if (class_exists($command, true))
		{
			$action = new $command($this->options);
			if ($action instanceof ICommandLineTool)
			{
				static::line('');
				static::line('Executing command: ' . $command);
				static::line('');
				$action->execute($this->options);
			} else
			{
				trigger_error('Invalid command: ' . $command, E_USER_ERROR);
				die();
			}
		} else
		{
			trigger_error('Unknown command: ' . $command, E_USER_ERROR);
			die();
		}
	}

	/**
	 * Read command line options
	 */
	private function readOptions()
	{
		$this->options = getopt('c:');
	}

	/**
	 * Echo a string into command line interface
	 * @param $str
	 * @param int $indent
	 */
	static public function line($str, $indent = 0)
	{
		echo OrionTools::linef($str, $indent);
	}

	/**
	 * Asking for user input through command line interface
	 * @param $str
	 * @return string
	 */
	static public function ask($str)
	{
		static::line($str);
		$handle = fopen("php://stdin", "r");
		return fgets($handle);
	}

	/**
	 * Asking yes/no question to user through command line interface
	 * @param $str
	 * @return bool
	 */
	static public function askYesNo($str)
	{
		return trim(static::ask($str . " (y/n) :")) === 'y';
	}

}