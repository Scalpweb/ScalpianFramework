<?php
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    require_once(__DIR__ . "/../lib/Orion.php");

    Orion::init();
    Orion::setConfigurationDirectory(ORION_MAIN_DIR.'/config');
    Orion::getRouter()->dispatch();
