<?php

class Conf {
    
  static public function get($name, $default = "") {

    $wert = $default;
    $archiveAccess = self::isArchive();

    switch($name) {
      case "DATA_FILE_NAME_BASE" : $wert = self::getDataPathRoot(); break;
      case "LOG_FILE_NAME_BASE" : $wert = ROOT_DIR . "/log/memo"; break;
      case "LOG_LEVEL" : $wert = defined('LOG_LEVEL') ? LOG_LEVEL : Log::LOG_LEVEL_DEBUG; break;
      default: ;
    }
    return $wert;
  }

    /**
     * return wether the current application uses the data/ or the data_archive notes.
     * true if application is using data_archive/notes.
     */
  static public function isArchive() {
      return strpos(VHOST_DOMAIN, "archiv") !== false;
  }

  static private function getDataPathRoot() {
      return ROOT_DIR . "/data/memo/" . APPLICATION_NAME;
  }

}
