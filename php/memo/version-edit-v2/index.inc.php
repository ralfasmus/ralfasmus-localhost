<?php

require_once(ALLGEMEIN_PHP_DIR_DIR . DIRECTORY_SEPARATOR . "index.inc.php");

    $request = new Request($phpProperties);
    echo $request->getResponse();
