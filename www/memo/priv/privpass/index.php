<?php

// Verzeichnis, in dem www, php u.a. liegt
define("APPLICATION_ROOT",dirname(realpath("."),4));
define("APPLICATION_NAME",basename(realpath(".")));
require_once("local.version.inc.php");
require_once("../../default/index.inc.php");