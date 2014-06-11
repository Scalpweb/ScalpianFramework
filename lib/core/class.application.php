<?php

class Application
{

	protected $status = ApplicationStatus::APP_RUNNING;
	private $name, $layout;

	final public function __construct($_name = '')
	{
		$this->layout = Configuration::getInstance()->get("Routing/DefaultLayout");
		if ($_name === '')
			$_name = substr(get_class($this), 0, strpos(get_class($this), 'Application'));
		$this->name = ucfirst($_name);
	}

	/**
	 * @return string Application status
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * Dispatch application, module and action
	 * @param $module
	 * @param $action
	 * @param $args
	 */
	public function dispatch($module, $action, $args)
	{
		if ($this->status === ApplicationStatus::APP_ON_HOLD)
		{
			Logger::getInstance()->log(LoggerEntry::ERROR, 'Router', 'The current application is on hold: ' . $this->getName());
			Router::getInstance()->redirect403();
		}

		if (method_exists($this, "onDispatch"))
		{
			$this->onDispatch($module, $action, $args);
		}
		$module->dispatch($action, $args);
		if (method_exists($this, "afterDispatch"))
		{
			$this->afterDispatch($module, $action, $args);
		}
	}

	/**
	 * @return string Name of the module
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string Name of the layout used by this application by default
	 */
	public function getLayout()
	{
		return $this->layout;
	}

	/**
	 * Set application layout
	 * @param $_layout Name of the layout to be used by this application
	 */
	public function setLayout($_layout)
	{
		$this->layout = $_layout;
	}

	/**
	 * @param $key
	 * @throws UnknownApplicationException
	 * @return mixed
	 */
	static function load($key)
	{
		$app = ucfirst($key) . 'Application';
		try
		{
			return new $app(ucfirst($key));
		} catch (Exception $e)
		{
			throw(new UnknownApplicationException("This application does not exist: " . ucfirst($key)));
		}
	}

	/**
	 * Tests if an application exists
	 * @param $key
	 * @return bool
	 */
	static function exists($key)
	{
		return class_exists(ucfirst($key) . 'Application');
	}

}