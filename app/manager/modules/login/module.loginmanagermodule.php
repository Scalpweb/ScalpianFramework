<?php

class LoginManagerModule extends Module
{

    public function onDispatch()
    {
        Router::getInstance()->redirectIf(User::getInstance()->hasAccess('manager'), '/Manager');
        $this->setLayout('login');
    }

    public function actionIndex()
    {
        if(Request::getInstance()->isSetPost('username') && Request::getInstance()->isSetPost('password'))
        {
            Router::getInstance()->redirectIf(User::getInstance()->doLogin(Request::getInstance()->getPost('username'), Request::getInstance()->getPost('password')), '/Manager');
            $this->error = 'Username or password invalid.';
        }
    }

    public function actionLogout()
    {
        User::getInstance()->doLogout();
        Router::getInstance()->redirect('/Manager');
    }

}