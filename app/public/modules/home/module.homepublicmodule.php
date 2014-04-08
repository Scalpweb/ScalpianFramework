<?php

class HomePublicModule extends Module
{

    public function onDispatch($action, $args)
    {
    }

    public function afterDispatch($action, $args)
    {
    }

    /**
     * Executed when calling the index module
     */
    public function actionIndex($a = 1, $b = 2, $c = 3)
    {
        $this->someData = 'test'.$a.$b.$c;
    }

    /**
     * Executed when calling the custom action
     */
    public function actionCustom($a, $b, $c, $d = 'default')
    {
        die('Routed to custom route ['.$a.'], ['.$b.'], ['.$c.'], ['.$d.']');
    }

}