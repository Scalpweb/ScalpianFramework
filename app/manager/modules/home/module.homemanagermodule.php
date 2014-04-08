<?php

class HomeManagerModule extends Module
{

    public function actionIndex()
    {
    }

    public function actionYesNo($question)
    {
        $this->question = str_replace('-', ' ', $question);
        $this->url = '';
        $id = 1;
        while(Request::getInstance()->isSetGet('url'.$id))
        {
            $this->url .= '/'.Request::getInstance()->getGet('url'.$id);
            $id ++;
        }
    }

}