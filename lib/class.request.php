<?php

class Request extends Singleton
{

    /**
     * Checks if a get variable is set
     * @param $ref
     * @return bool
     */
    public function isSetGet($ref)
    {
        return isset($_GET[$ref]);
    }

    /**
     * Checks if a post variable is set
     * @param $ref
     * @return bool
     */
    public function isSetPost($ref)
    {
        return isset($_POST[$ref]);
    }

    /**
     * Set a get variable from URL
     * @param $index
     * @param $value
     */
    public function setGetFromUrl($index, $value)
    {
        $_GET['url'.$index] = $value;
    }

    /**
     * Returns a get variable
     * @param $ref
     * @param bool $clean
     * @throws UndefinedGetVariable
     * @return mixed
     */
    public function getGet($ref, $clean = true)
    {
        if(!isset($_GET[$ref]))
            throw(new UndefinedGetVariable("Unknown get variable: ".$ref));
        return $clean ? htmlentities($_GET[$ref]) : $_GET[$ref];
    }

    /**
     * Returns a post variable
     * @param $ref
     * @param bool $clean
     * @throws UndefinedPostVariable
     * @return mixed
     */
    public function getPost($ref, $clean = true)
    {
        if(!isset($_POST[$ref]))
            throw(new UndefinedPostVariable("Unknown post variable: ".$ref));
        return $clean ? htmlentities($_POST[$ref]) : $_POST[$ref];
    }

}