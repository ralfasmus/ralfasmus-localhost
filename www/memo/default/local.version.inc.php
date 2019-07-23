<?php
 // default versionen

if(!defined("ALLGEMEIN_VERSION_PHP")) {
    define("ALLGEMEIN_VERSION_PHP","version-edit");
    //define("ALLGEMEIN_VERSION_PHP","version-2018-10-24");
}

 if(!defined("APPLICATION_VERSION_PHP")) {
     define("APPLICATION_VERSION_PHP","version-edit");
     //define("APPLICATION_VERSION_PHP","version-2018-11-29");
 }
 if(!defined("APPLICATION_VERSION_CSSJS")) {
     define("APPLICATION_VERSION_CSSJS","version-edit");
     //define("APPLICATION_VERSION_CSSJS","version-2018-11-29");
 }
if(!defined("LOG_LEVEL")) {
    //define("LOG_LEVEL", 0); // ObjectAbstract::LOG_LEVEL_OFF
    define("LOG_LEVEL", 4); // ObjectAbstract::LOG_LEVEL_DEBUG
}

//define('APPLICATION_PHP_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . APPLICATION_NAME . DIRECTORY_SEPARATOR . APPLICATION_VERSION_PHP);
// Verzeichnis fuer PHP-Klassen der Application: ralfwork.localhost/php/memo/version-XXXXXXXXX
define('APPLICATION_PHP_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . "memo" . DIRECTORY_SEPARATOR . APPLICATION_VERSION_PHP);
// Verzeichnis fuer allgemeine Default PHP-Klassen : ralfwork.localhost/php/default/version-XXXXXXXXX
define('ALLGEMEIN_PHP_DIR_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . "allgemein" . DIRECTORY_SEPARATOR . ALLGEMEIN_VERSION_PHP);