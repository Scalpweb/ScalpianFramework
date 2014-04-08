<?php

interface ICommandLineTool
{

    public function execute($options);
    public function getName();
    public function getDescription();

}