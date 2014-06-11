<?php

interface IErrorHandler
{

	static public function handleError($errno, $errstr, $errfile = '', $errline = '', $errcontext = array());

}