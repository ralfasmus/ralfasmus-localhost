<?php

// --------- DEFINE --------------------------------------------------------------------------

// Verzeichnis, in dem www, php u.a. liegt
define("ROOT_DIR",dirname(realpath("."),4));
define("APPLICATION_NAME",basename(realpath(".")));
define("DATA_ROOT_DIR",realpath(".") . DIRECTORY_SEPARATOR . "data");
define("HREF_DATA_ROOT", "http://ralfwork.localhost:90/betrieb/work/monitor/data");

// --------- INCLUDE -------------------------------------------------------------------------

require_once("local.version.inc.php");
require_once(APPLICATION_PHP_DIR . DIRECTORY_SEPARATOR . "index.inc.php");
$cssjsPath              = "/betrieb/cssjs/" . APPLICATION_VERSION_CSSJS;

// --------- INIT ----------------------------------------------------------------------------
$request = \Allgemein\Request::getSingleInstance();

// --------- HTML AUSGABE --------------------------------------------------------------------

require_once("include/head.1.inc.php");
?>
<body>
    <a id="pagetop"></a>
    <div id="page" class=container-fluid">
        <table id="tabelle1"></table>
    </div>

<?php require_once("include/body.1.inc.php"); ?>
</body>
</html>