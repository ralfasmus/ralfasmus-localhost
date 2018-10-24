<?php

require_once(ALLGEMEIN_PHP_DIR_DIR . DIRECTORY_SEPARATOR . "index.inc.php");

$request = Request::getSingleInstance($phpProperties);
Zeit::setRequestStartzeit();
echo View::create()->getResponse();
