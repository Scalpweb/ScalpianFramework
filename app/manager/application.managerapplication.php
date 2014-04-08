<?php

class ManagerApplication extends Application
{

    /**
     * Redirect user to login page is necessary
     */
    public function onDispatch()
    {
        Router::getInstance()->redirectIf(!User::getInstance()->hasAccess('manager') && Router::getInstance()->getModule()->getName() != 'Login', '/Manager/Login');
    }

}