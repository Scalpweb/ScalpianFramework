<?php

class Router extends Singleton
{

    private $application, $module, $action, $arguments, $customRoutes = array();

    /**
     * Add one custom route to list
     * @param $route
     */
    public function addCustomRoute($route)
    {
        $this->customRoutes = array_merge($this->customRoutes, array($route));
    }

    /**
     * Add custom routes to list
     * @param $routes
     */
    public function addCustomRoutes($routes)
    {
        $this->customRoutes = array_merge($this->customRoutes, $routes);
    }

    /**
     * Returns custom routes list
     * @return array
     */
    public function getCustomRoutes()
    {
        return $this->customRoutes;
    }

    /**
     * Tests if a given route matches any of the configured custom route.
     * @param $route
     * @return array|bool Returns false if no match, or an array describing the route
     */
    public function tryCustomRouteMatching($route)
    {
        $result  = array();
        $matches = array();
        $args    = array();
        foreach($this->customRoutes as $key=>$value)
        {
            $key = str_replace('/',          '\/', $key);
            $key = str_replace('{any}',      '(.+)', $key);
            $key = str_replace('{num}',      '([0-9]+)', $key);
            $key = str_replace('{alpha}',    '([a-z]+)', $key);
            $key = str_replace('{alphanum}', '([a-z0-9]+)', $key);
            if(preg_match('/^'.$key.'$/iU', $route, $matches) === 1)
            {
                for($i = 1; $i < sizeof($matches); $i ++)
                {
                    $args[] =  htmlentities($matches[$i]);
                }
                $result['application']  = $value['application'];
                $result['module']       = $value['module'];
                $result['action']       = $value['action'];
                $result['arguments']    = $args;
                return $result;
            }
        }
        return false;
    }

    /**
     * Dispatch application to current route from $_GET['route'] parameter:
     */
    public function dispatch()
    {
        $route = array();
        $custom_check_succesfull = false;
        if(Request::getInstance()->isSetGet('route'))
        {
            $route = Request::getInstance()->getGet('route');

            // todo This is a fix for chrome "no-favicon" error. We should fine a nicer way...
            if($route === 'favicon.ico')
                header('HTTP/1.1 404 Not Found');

            $customRouteCheck = $this->tryCustomRouteMatching($route);
            if($customRouteCheck !== false)
            {
                $custom_check_succesfull = true;
                $this->application  = Application::load($customRouteCheck['application']);
                $this->module       = Module::load($this->application, $customRouteCheck['module']);
                $this->action       = $customRouteCheck['action'];
                $this->arguments    = $customRouteCheck['arguments'];
            }
            $route = explode('/', $route);
        }

        if(!$custom_check_succesfull)
        {
            // Set application:
            $index = 0;
            if(sizeof($route) > $index && Application::exists($route[$index]))
            {
                $this->application = Application::load($route[$index]);
                $index ++;
            }
            else
                $this->application = Application::load(Configuration::getInstance()->get("Routing/DefaultApplication"));

            // Set module:
            if(sizeof($route) > $index && Module::exists($this->application, $route[$index]))
            {
                $this->module = Module::load($this->application, $route[$index]);
                $index ++;
            }
            else
                $this->module = Module::load($this->application, Configuration::getInstance()->get("Routing/DefaultModule"));

            // Set Action:
            if(sizeof($route) > $index && $this->module->hasAction($route[$index]))
            {
                $this->action = ucfirst($route[$index]);
                $index ++;
            }
            else
                $this->action = ucfirst(Configuration::getInstance()->get("Routing/DefaultAction"));

            // Set get variables:
            $url_index = 0;
            $this->arguments = array();
            while(sizeof($route) > $index)
            {
                Request::getInstance()->setGetFromUrl($url_index, $route[$index]);
                $this->arguments[] = htmlentities($route[$index]);
                $url_index++;
                $index++;
            }
        }
        else
        {
            $arg_index = 0;
            foreach($this->arguments as $element)
            {
                Request::getInstance()->setGetFromUrl($arg_index, $element);
                $arg_index++;
            }
        }

        Logger::getInstance()->log(LoggerEntry::ROUTER, "Router", "Application: ".$this->application->getName());
        Logger::getInstance()->log(LoggerEntry::ROUTER, "Router", "Module: ".$this->module->getName());
        Logger::getInstance()->log(LoggerEntry::ROUTER, "Router", "Action: ".$this->action);
        Logger::getInstance()->log(LoggerEntry::ROUTER, "Router", "URL variables: ".print_r($this->arguments,  true));

        EventHandler::trigger(EventTypes::ORION_BEFORE_DISPATCH, null);
        $this->application->dispatch($this->module, $this->action, $this->arguments);
        EventHandler::trigger(EventTypes::ORION_AFTER_DISPATCH, null);

    }

    /**
     * Returns current application as object
     * @return mixed
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Returns current module as object
     * @return mixed
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Returns current action as string
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Redirects the client
     * @param $url
     */
    public function redirect($url)
    {
        if(method_exists($this->getModule(), "afterDispatch"))
                $this->getModule()->afterDispatch($this->action, $this->arguments);
        header("Location: ".$url);
        exit;
    }

    /**
     * Redirects the client unless condition is true
     * @param $condition
     * @param $url
     */
    public function redirectUnless($condition, $url)
    {
        if(!$condition)
            $this->redirect($url);
    }

    /**
     * Redirects the client if condition is true
     * @param $condition
     * @param $url
     */
    public function redirectIf($condition, $url)
    {
        if($condition)
            $this->redirect($url);
    }

    /**
     * Redirect to 404 error
     */
    public function redirect404()
    {
        header('Status : 404 Not Found');
        header('HTTP/1.0 404 Not Found');
        exit;
    }

    /**
     * Redirect to 403 error
     */
    public function redirect403()
    {
        header('Status : 403 Forbidden');
        header('HTTP/1.0 403 Forbidden');
        exit;
    }

    /**
     * Redirect to 401 error
     */
    public function redirect401()
    {
        header('Status : 401 Unauthorized');
        header('HTTP/1.0 401 Unauthorized');
        exit;
    }



}