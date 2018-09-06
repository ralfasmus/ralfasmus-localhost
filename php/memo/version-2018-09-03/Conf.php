<?php

class Conf {
    
  static public function get($name, $default = "") {
    $wert = $default;
    $archiveAccess = strpos(VHOST_DOMAIN, "archiv") !== false;

    switch($name) {
      case "DATA_FILE_NAME_BASE" : $wert = self::getDataPathRoot() . ($archiveAccess ? "/data_archive" : "/data"); break;
      case "DATA_FILE_NAME_BACKUP_BASE" : $wert =  self::getDataPathRoot() . "/data_backup"; break;
      case "DATA_FILE_NAME_ARCHIVE_BASE" : $wert =  self::getDataPathRoot() . ($archiveAccess ? "/data_backup" : "/data_archive"); break;
      default: ;
    }
    return $wert;
  }

  static private function getDataPathRoot() {
      return APPLICATION_ROOT . "/data/memo/" . APPLICATION_NAME;
  }

}
