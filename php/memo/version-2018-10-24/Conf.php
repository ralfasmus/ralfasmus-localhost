<?php

class Conf {
    
  static public function get($name, $default = "") {
    $wert = $default;
    $archiveAccess = self::isArchive();

    switch($name) {
      case "DATA_FILE_NAME_BASE" : $wert = self::getDataPathRoot() . ($archiveAccess ? "/data_archive" : "/data"); break;
      case "DATA_FILE_NAME_BACKUP_BASE" : $wert =  self::getDataPathRoot() . "/data_backup"; break;
      case "DATA_FILE_NAME_ARCHIVE_BASE" : $wert =  self::getDataPathRoot() . ($archiveAccess ? "/data" : "/data_archive"); break;
      case "LOG_FILE_NAME_BASE" : $wert = ROOT_DIR . "/log/memo"; break;
      case "LOG_LEVEL" : $wert = LOG_LEVEL; break;
      default: ;
    }
    return $wert;
  }

    /**
     * return wether the current application uses the data/ or the data_archive instances.
     * true if application is using data_archive/instances.
     */
  static public function isArchive() {
      return strpos(VHOST_DOMAIN, "archiv") !== false;
  }

  static private function getDataPathRoot() {
      return ROOT_DIR . "/data/memo/" . APPLICATION_NAME;
  }

}
