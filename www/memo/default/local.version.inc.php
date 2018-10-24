<?php
 // default versionen

if(!defined("DEFAULT_VERSION_PHP")) {
    // define("DEFAULT_VERSION_PHP","version-edit");
    define("DEFAULT_VERSION_PHP","version-2018-10-24");
}

 if(!defined("APPLICATION_VERSION_PHP")) {
     //define("APPLICATION_VERSION_PHP","version-edit");
     define("APPLICATION_VERSION_PHP","version-2018-10-24");
 }
 if(!defined("APPLICATION_VERSION_CSSJS")) {
     //define("APPLICATION_VERSION_CSSJS","version-edit");
     define("APPLICATION_VERSION_CSSJS","version-2018-10-15");
 }
if(!defined("LOG_LEVEL")) {
    define("LOG_LEVEL", 0); // ObjectAbstract::LOG_LEVEL_OFF
    //define("LOG_LEVEL", 4); // ObjectAbstract::LOG_LEVEL_DEBUG
}

//define('APPLICATION_PHP_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . APPLICATION_NAME . DIRECTORY_SEPARATOR . APPLICATION_VERSION_PHP);
// Verzeichnis fuer PHP-Klassen der Application: ralfwork.localhost/php/memo/version-XXXXXXXXX
define('APPLICATION_PHP_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . "memo" . DIRECTORY_SEPARATOR . APPLICATION_VERSION_PHP);
// Verzeichnis fuer allgemeine Default PHP-Klassen : ralfwork.localhost/php/default/version-XXXXXXXXX
define('DEFAULT_PHP_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . "default" . DIRECTORY_SEPARATOR . DEFAULT_VERSION_PHP);