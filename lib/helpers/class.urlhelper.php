<?php

class UrlHelper
{

    /**
     * @param null $app
     * @param null $module
     * @param null $action
     * @param array $args
     * @return string
     */
    static public function get($app = null, $module = null, $action = null, $args = array())
    {
        $app =      $app === null    ? Configuration::getInstance()->get("Routing/DefaultApplication") : $app;
        $module =   $module === null ? Configuration::getInstance()->get("Routing/DefaultModule")      : $module;
        $action =   $action === null ? Configuration::getInstance()->get("Routing/DefaultAction")      : $action;
        return '/'.$app.'/'.$module.'/'.$action.'/'.implode('/', $args);
    }

    /**
     * Parse an url and return an array containing the app, module, action and arguments targeted by it.
     * @param $url
     * @return array array('application' => $appName, 'module' => $moduleName, 'action' => $actionName, 'arguments' => $argsKeyValuePairArray);
     */
    static public function parseUrl($url)
    {
        $result = array('application' => '', 'module' => '', 'action' => '', 'arguments' => array());

        $route = array();
        if($url != '')
            $route = explode('/', $url);

        // Set application:
        $index = 0;
        while(sizeof($route) > $index && $route[$index] === '')
        {
            $index ++;
        }

        if(sizeof($route) > $index && Application::exists($route[$index]))
        {
            $result['application'] = ucfirst($route[$index]);
            $index ++;
        }
        else
            $result['application'] = ucfirst(Configuration::getInstance()->get("Routing/DefaultApplication"));

        // Set module:
        if(sizeof($route) > $index && Module::exists($result['application'], $route[$index]))
        {
            $result['module'] = ucfirst($route[$index]);
            $index ++;
        }
        else
            $result['module'] = ucfirst(Configuration::getInstance()->get("Routing/DefaultModule"));

        // Set Action:
        if(sizeof($route) > $index && method_exists($result['module'].$result['application'].'Module', 'action'.ucfirst($route[$index])))
        {
            $result['action'] = ucfirst($route[$index]);
            $index ++;
        }
        else
            $result['action'] = ucfirst(Configuration::getInstance()->get("Routing/DefaultAction"));

        // Set arguments:
        while(sizeof($route) > $index)
        {
            $result['arguments'][] = htmlentities($route[$index]);
            $index++;
        }

        return $result;
    }

}