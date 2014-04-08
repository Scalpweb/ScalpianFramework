<?php

class Module
{

    private $name, $application, $layout, $current_action = '', $anchors = array(), $finalCode = '';

    final public function __construct($parent, $_name = '')
    {
        if($_name === '')
            $_name = substr(get_class($this), 0, strpos(get_class($this), $parent->getName().'Module'));
        $this->name = ucfirst($_name);
        $this->application = $parent;
        $this->layout = $this->application->getLayout();
    }

    /**
     * Dispatch an action and render view
     * @param $action Name of the action to be dispatched
     * @param $arguments Array of arguments
     * @throws UndefinedActionException
     * @throws WrongNumberOfArgumentsException
     * @throws LayoutDoesNotExistException
     */
    public function dispatch($action, $arguments)
    {
        $method_name = 'action'.ucfirst($action);

        // Call the onDispatch method first
        if(method_exists($this, "onDispatch"))
        {
            $this->onDispatch($action, $arguments);
        }

        // Does this action exists ?
        if(!$this->hasAction($action))
        {
            throw(new UndefinedActionException("Undefined action: ".$action));
        }
        $this->current_action = $action;

        // Does the arguments matching the minimum requirements ?
        $method = new ReflectionMethod($this->name.$this->application->getName().'Module', $method_name);
        $required_args = $method->getNumberOfRequiredParameters();
        if($required_args > sizeof($arguments))
        {
            throw(new WrongNumberOfArgumentsException("Wrong number of arguments. Arguments passed: ".sizeof($arguments).', minimum of arguments needed: '.$required_args));
        }

        // Execute action
        EventHandler::trigger(EventTypes::ORION_BEFORE_ACTION, $this);
        call_user_func_array(array($this, $method_name), $arguments);
        EventHandler::trigger(EventTypes::ORION_AFTER_ACTION, $this);

        // Compute layout
        $layoutCode = "";
        $layoutPath = ORION_APP_DIR.'/'.strtolower($this->application->getName()).'/layouts/layout.'.$this->layout.'.php';
        if(FileSystem::checkFile($layoutPath))
        {
            EventHandler::trigger(EventTypes::ORION_BEFORE_LAYOUT, $this);
            ob_start();
            include($layoutPath);
            $layoutCode = ob_get_contents();
            ob_end_clean();
            EventHandler::trigger(EventTypes::ORION_AFTER_LAYOUT, $this);
        }
        else
        {
            throw(new LayoutDoesNotExistException("The following layout does not exists: [".$this->layout."] (file path: [".$layoutPath."])"));
        }

        $finalCode = $layoutCode;

        // Replace anchor
        foreach($this->anchors as $key => $anchor)
        {
            $finalCode = preg_replace('/<orion anchor=\"'.$key.'\">(.*)<\/orion>/isU', $anchor, $finalCode);
        }
        $finalCode = preg_replace('/<orion anchor=\"(.*)\">(.*)<\/orion>/isU', '${2}', $finalCode);

        // Display result
        $this->finalCode = $finalCode;
        EventHandler::trigger(EventTypes::ORION_BEFORE_DISPLAY, $this);
        echo $this->finalCode;
        EventHandler::trigger(EventTypes::ORION_AFTER_DISPLAY, $this);

        // Finally, call afterDispatch method
        if(method_exists($this, "afterDispatch"))
        {
            $this->afterDispatch($action, $arguments);
        }
    }

    /**
     * Changes current layout
     * @param $layout_name
     */
    public function setLayout($layout_name)
    {
        $this->layout = $layout_name;
    }

    /**
     * Get final html code
     * @return string
     */
    public function getHtmlFinalCode() { return $this->finalCode; }

    /**
     * Set final html code
     * @param $code
     */
    public function setHtmlFinalCode($code) { $this->finalCode = $code; }

    /**
     * Replace the 'insertView' anchor by an Orion tag
     */
    public function insertView()
    {
        $viewPath = $this->getViewDirectory().'/view.'.$this->current_action.'.php';
        if(FileSystem::checkFile($viewPath))
        {
            EventHandler::trigger(EventTypes::ORION_BEFORE_VIEW, $this);
            include($viewPath);
            EventHandler::trigger(EventTypes::ORION_AFTER_VIEW, $this);
        }
    }

    /**
     * Replace the 'insertBlock' anchors by an Orion tag
     */
    public function insertBlock($blockName, $arguments = array())
    {
        $block = new Block($this, $blockName);
        $block->includeBlock($arguments);
    }

    /**
     * Get the layout directory for the current module:
     */
    public function getLayoutDirectory()
    {
        return ORION_APP_DIR . '/' . strtolower($this->application->getName()) . '/layouts';
    }

    /**
     * Get the views directory for the current module:
     */
    public function getViewDirectory()
    {
        return ORION_APP_DIR . '/' . strtolower($this->application->getName()) . '/modules/' . strtolower($this->getName()) . '/views';
    }

    /**
     * @param $name Name of the action to check
     * @return bool Returns true if the action exists
     */
    public function hasAction($name)
    {
        return method_exists($this, 'action'.ucfirst($name));
    }

    /**
     * @return string Name of the module
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Defines an anchor
     * @param $key
     * @param $value
     */
    public function setAnchor($key, $value)
    {
        $this->anchors[$key] = $value;
    }

    /**
     * Insert anchor tag that will be replaced by anchor value or default value if the anchor is never set
     * @param $key
     * @param string $default
     */
    public function insertAnchor($key, $default = "")
    {
        echo "<orion anchor=\"".$key."\">".$default."</orion>";
    }

    /**
     * Tests if an anchor exists
     * @param $key
     * @return bool
     */
    public function anchorExists($key)
    {
        return isset($this->anchors[$key]);
    }

    /**
     * @param $app
     * @param $key
     * @throws UnknownModuleException
     * @return mixed
     */
    static function load($app, $key)
    {
        $module = ucfirst($key).ucfirst($app->getName()).'Module';
        return new $module($app, ucfirst($key));
    }

    /**
     * Tests if a module exists
     * @param $app
     * @param $key
     * @return bool
     */
    static function exists($app, $key)
    {
        if(!is_object($app))
            return class_exists(ucfirst($key).ucfirst($app).'Module');
        return class_exists(ucfirst($key).ucfirst($app->getName()).'Module');
    }

    /**
     * Get parent application object
     * @return mixed
     */
    public function getApplication()
    {
        return $this->application;
    }

}