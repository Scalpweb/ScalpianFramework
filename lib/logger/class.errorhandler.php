<?php

class ErrorHandler implements IErrorHandler
{

	static public function handleError($errno, $errstr, $errfile = '', $errline = '', $errcontext = array())
	{
		OrionTools::linef('Error ' . $errno . ':', 0, true);
		OrionTools::linef($errstr, 0, true);

		if ($errfile !== '') OrionTools::linef('In: ' . $errfile, 0, true);
		if ($errline !== '') OrionTools::linef('At line: ' . $errline, 0, true);
		if (sizeof($errcontext) > 0)
		{
			OrionTools::linef('Context: ', 0, true);
			foreach ($errcontext as $context)
				OrionTools::linef($context, 0, true);
		}
	}

}