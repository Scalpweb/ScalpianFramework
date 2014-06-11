<?php

class CliErrorHandler implements IErrorHandler
{

	static public function handleError($errno, $errstr, $errfile = '', $errline = '', $errcontext = array())
	{
		CommandLineHandler::line("*******************************************");
		CommandLineHandler::line('Error ' . $errno . ':');
		CommandLineHandler::line($errstr);

		if ($errfile !== '') CommandLineHandler::line('In: ' . $errfile);
		if ($errline !== '') CommandLineHandler::line('At line: ' . $errline);
		CommandLineHandler::line("*******************************************");
	}

}