<?php
// ---------- VERSIONEN -------------------------------------------------------

 define("APPLICATION_VERSION_PHP","version-edit");
 //define("APPLICATION_VERSION_PHP","version-2018-10-24");
 define("ALLGEMEIN_VERSION_PHP","version-edit");
 //define("ALLGEMEIN_VERSION_PHP","version-2018-10-24");
 define("APPLICATION_VERSION_CSSJS","version-edit");
 //define("APPLICATION_VERSION_CSSJS","version-2018-10-15");

// ---------- LOGGING und DEBUGGING -------------------------------------------

 define("LOG_LEVEL", 4); // ObjectAbstract::LOG_LEVEL_DEBUG
 //define("LOG_LEVEL", 0); // ObjectAbstract::LOG_LEVEL_OFF


// ---------- SONSTIGES -------------------------------------------------------

//define('APPLICATION_PHP_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . APPLICATION_NAME . DIRECTORY_SEPARATOR . APPLICATION_VERSION_PHP);
// Verzeichnis fuer PHP-Klassen der Application: ralfwork.localhost/php/fileset/version-XXXXXXXXX
define('APPLICATION_PHP_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . "fileset" . DIRECTORY_SEPARATOR . APPLICATION_VERSION_PHP);
// Verzeichnis fuer allgemeine Default PHP-Klassen : ralfwork.localhost/php/default/version-XXXXXXXXX
define('ALLGEMEIN_PHP_DIR_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . "allgemein" . DIRECTORY_SEPARATOR . ALLGEMEIN_VERSION_PHP);