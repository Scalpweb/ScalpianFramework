<?php

session_start();

// TODO list:
// --- 1) Wysiwyg database schema builder
// --- 2) Create fields and validators
// --- 3) Logger UI

define("ORION_LIB_DIR",     __DIR__."");
define("ORION_APP_DIR",     __DIR__."/../app");
define("ORION_WEB_DIR",     __DIR__."/../web");
define("ORION_CACHE_DIR",   __DIR__."/../cache");
define("ORION_MODELS_DIR",  __DIR__."/../models");
define("ORION_LAYOUT_DIR",  __DIR__."/../layouts");
define("ORION_PLUGINS_DIR", __DIR__."/../plugins");
define("ORION_MAIN_DIR",    __DIR__."/..");

class Orion
{

    const version = '0.1.1a';

    /**
     * Initialization of the framework core engine
     */
    static public function init($configurationParameters = array())
    {
        // Loads needed classes:
        require_once(__DIR__."/class.singleton.php");
        require_once(__DIR__."/class.filesystem.php");
        require_once(__DIR__."/cache/class.cache.php");
        require_once(__DIR__."/logger/class.logger.php");
        require_once(__DIR__."/class.loader.php");
        require_once(__DIR__."/class.configuration.php");

        // Force the loading of all exception types:
        require_once(__DIR__."/exceptions/interface.iexception.php");
        require_once(__DIR__."/exceptions/class.customexception.php");
        require_once(__DIR__."/exceptions/classes.exceptions.php");

        // Init configuration:
        Configuration::getInstance()->init($configurationParameters);

        // Init loader:
        Loader::init();

        // Configure error handler:
        Logger::getInstance()->setErrorHandler(new ErrorHandler());

        // Trigger event:
        EventHandler::trigger(EventTypes::ORION_INITIALIZED, null);

        // Setup history recorder
        History::setupListener();
    }

    /**
     * @param $directory
     * @throws ConfigurationException
     */
    static public function setConfigurationDirectory($directory)
    {
        $files = FileSystem::listDirectory($directory, array("php"), false, false);
        foreach($files as $file)
        {
            require_once($directory.'/'.$file);
        }
    }

    /**
     * Dispatch command line handler
     */
    static public function cli()
    {
        $app = new CommandLineHandler();
        $app->dispatch();
    }

    /**
     * @return Singleton Orion configuration
     */
    static public function getConfiguration()
    {
        return Configuration::getInstance();
    }

    /**
     * @return Singleton Orion request
     */
    static public function getRequest()
    {
        return Request::getInstance();
    }

    /**
     * @return Singleton Orion router
     */
    static public function getRouter()
    {
        return Router::getInstance();
    }

    /**
     * @return Singleton Orion logger
     */
    static public function getLogger()
    {
        return Logger::getInstance();
    }

}