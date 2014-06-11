<?php

class CronHandler extends Singleton
{

	private $crons = array(), $lowestPriorityIndex = 50, $highestPriorityIndex = 50, $installed = false;

    public static function __install()
    {
        // -- Save cron installers list to cache:
        $installers = MagicHelper::getClassesWithCronInstaller();
        CommandLineHandler::line('Installers found: '.sizeof($installers));
	    CommandLineHandler::line('');
    }

	/**
	 * Add a cronjob to the list
	 *
	 * @param $appName
	 * @param $moduleName
	 * @param $actionName
	 * @param $minute
	 * @param $hour
	 * @param $dayOfMonth
	 * @param $month
	 * @param $dayOfWeek
	 * @param int $priorityIndex
	 */
	public function add($appName, $moduleName, $actionName, $minute, $hour, $dayOfMonth, $month, $dayOfWeek, $priorityIndex = 50)
	{
		$this->lowestPriorityIndex = min($this->lowestPriorityIndex, $priorityIndex);
		$this->highestPriorityIndex = max($this->highestPriorityIndex, $priorityIndex);
		if(!isset($this->crons[$priorityIndex]))
			$this->crons[$priorityIndex] = array();
		$this->crons[$priorityIndex][] = array(
			'app' => $appName,
			'module' => $moduleName,
			'action' => $actionName,
			'minute' => $minute,
			'hour' => $hour,
			'dayOfMonth' => $dayOfMonth,
			'month' => $month,
			'dayOfWeek' => $dayOfWeek
		);
	}

	/**
	 * Add a callable as a cronjob
	 *
	 * @param callable $callable
	 * @param $minute
	 * @param $hour
	 * @param $dayOfMonth
	 * @param $month
	 * @param $dayOfWeek
	 * @param int $priorityIndex
	 */
	public function addCallable(callable $callable, $minute, $hour, $dayOfMonth, $month, $dayOfWeek, $priorityIndex = 50)
	{
		$this->lowestPriorityIndex = min($this->lowestPriorityIndex, $priorityIndex);
		$this->highestPriorityIndex = max($this->highestPriorityIndex, $priorityIndex);
		if(!isset($this->crons[$priorityIndex]))
			$this->crons[$priorityIndex] = array();
		$this->crons[$priorityIndex][] = array(
			'callable' => $callable,
			'minute' => $minute,
			'hour' => $hour,
			'dayOfMonth' => $dayOfMonth,
			'month' => $month,
			'dayOfWeek' => $dayOfWeek
		);
	}

	/**
	 * Look for __cron magic methods on every classes
	 */
	public function installCrons($forceNoCache = false)
	{
		$this->installed = true;
		if($forceNoCache)
			$classes = MagicHelper::getClassesWithCronInstaller();
		else
			$classes = MagicHelper::getClassesWithCronInstallerFromCache();
		foreach($classes as $class)
		{
			call_user_func(array($class, '__cron'));
		}
	}

	/**
	 * Tests if a given reference matches a cron schedule
	 *
	 * @param $value
	 * @param $reference
	 * @return bool
	 */
	private function match($value, $reference)
	{
		// -- Simple match:
		if ($value === $reference || $value === '*')
			return true;

		// -- List match:
		$values = explode(',', $value);
		foreach ($values as $val)
		{
			if (intval($val) === $reference)
				return true;
		}

		// -- No match:
		return false;
	}

	/**
	 * Runs the scheduled crons
	 */
	public function run()
	{
		if(!$this->installed)
			$this->installCrons();
		for($pId = $this->lowestPriorityIndex; $pId < $this->highestPriorityIndex + 1; $pId ++)
		{
			if(!isset($this->crons[$pId]))
				continue;
			foreach ($this->crons[$pId] as $cron)
			{
				// -- Current cron should be executed ?
				if (
					$this->match($cron['minute'], intval(date('i')))
					&& $this->match($cron['hour'], date('G'))
					&& $this->match($cron['dayOfMonth'], date('j'))
					&& $this->match($cron['month'], date('n'))
					&& $this->match($cron['dayOfWeek'], date('N'))
				)
				{
					// -- Execute the action:
					if(isset($cron['callable']))
						call_user_func($cron['callable']);
					else
						CurlHelper::callAction($cron['app'], $cron['module'], $cron['action']);
				}
			}
		}
	}

	/**
	 * Runs all crons
	 */
	public function force($withEcho = false)
	{
		if(!$this->installed)
			$this->installCrons();
		for($pId = $this->lowestPriorityIndex; $pId < $this->highestPriorityIndex + 1; $pId ++)
		{
			if(!isset($this->crons[$pId]))
				continue;
			foreach ($this->crons[$pId] as $cron)
			{
				if($withEcho)
				{
					CommandLineHandler::line("Executing cron: ");
					CommandLineHandler::line(print_r($cron, true));
					CommandLineHandler::line("");
				}
				if(isset($cron['callable']))
					call_user_func($cron['callable']);
				else
					CurlHelper::callAction($cron['app'], $cron['module'], $cron['action']);
			}
		}
	}

}