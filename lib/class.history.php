<?php

class History
{

	static private $count;

	public static function setupListener()
	{
		static::$count = Configuration::getInstance()->get('History/MaxPages', true, 10);
		EventHandler::addListener(EventTypes::ORION_BEFORE_DISPATCH, array('History', 'record'));
	}

	/**
	 * Saves the current URL into history
	 */
	public static function record()
	{
		for ($i = static::$count; $i > 0; $i--)
		{
			if (Session::getInstance()->exists('orion-history' . ($i - 1)))
			{
				Session::getInstance()->set('orion-history' . $i, Session::getInstance()->get('orion-history' . ($i - 1)));
			}
		}
		Session::getInstance()->set('orion-history0', $_SERVER['REQUEST_URI']);
	}

	/**
	 * Get an url from history
	 * @param $id
	 * @return string
	 */
	public static function get($id)
	{
		return Session::getInstance()->exists('orion-history' . $id) ? Session::getInstance()->get('orion-history' . $id) : '/';
	}

}