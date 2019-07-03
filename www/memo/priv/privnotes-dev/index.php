<?php

// Verzeichnis, in dem www, php u.a. liegt
define("ROOT_DIR",dirname(realpath("."),4));
define("APPLICATION_NAME",basename(realpath(".")));

require_once("local.version.inc.php");
require_once(APPLICATION_PHP_DIR . DIRECTORY_SEPARATOR . "index.inc.php");
