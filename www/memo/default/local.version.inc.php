<?php
 // default versionen

 if(!defined("APPLICATION_VERSION_PHP")) {
     //define("APPLICATION_VERSION_PHP","version-edit");
     define("APPLICATION_VERSION_PHP","version-2018-10-15");
     //war: define("APPLICATION_VERSION_PHP","version-2018-09-18");
   //war: define("APPLICATION_VERSION_PHP","version-2018-09-06");
 }
 if(!defined("APPLICATION_VERSION_CSSJS")) {
     //define("APPLICATION_VERSION_CSSJS","version-edit");
     define("APPLICATION_VERSION_CSSJS","version-2018-10-15");
     // war: define("APPLICATION_VERSION_CSSJS","version-2018-09-18");
   //war: define("APPLICATION_VERSION_CSSJS","version-2018-09-17");
 }
if(!defined("LOG_LEVEL")) {
    define("LOG_LEVEL", 0); // ObjectAbstract::LOG_LEVEL_OFF
    //define("LOG_LEVEL", 4); // ObjectAbstract::LOG_LEVEL_DEBUG
}