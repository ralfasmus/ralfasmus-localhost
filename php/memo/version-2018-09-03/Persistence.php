<?php


class Persistence extends ObjectAbstract {

  /**
   * Fuer die Datei auf dem Filesystem.
   */
  const DATA_FILE_NAME_VALUE_SEPARATOR = "§";
  const DATA_FILE_PROPERTY_SEPARATOR = "°";
  const DATA_FILE_INSTANCE_SEPARATOR = "\n";

  /**
   * Liefert alle Instanzen sortiert.
   * @return type
   */
  public static function getInstances($instanceType, $sortProperty, $descending, $filterBudgetId = "", $includeArchiveData = false) {
    $instances = array();
    $sortList = array();
    $filterBudget = Request::getSingleInstance()->getProperty("data-filter-budget-id", $filterBudgetId);
    if ($filterBudget) {
      $budget = self::getOrCreateInstance($filterBudget);
    }
    $filenameBase = Conf::get("DATA_FILE_NAME_BASE");
    $idx=1;
    foreach (glob($filenameBase . "/$instanceType/data_$instanceType*.data") as $filename) {
      //  file_put_contents(APPLICATION_ROOT."/log/memo/log.txt", $idx++ . " Loading $filename" . "\r\n", FILE_APPEND);
      $instance = self::loadInstanceByFilename($filename);
      $add = !$filterBudget || ($instance->belongsTo($budget));
      if ($add) {
        if(is_array($sortProperty)) {
            $sortString = "";
            foreach($sortProperty as $prop) {
                $sortString .= $instance->getProperty($prop, "") . " ...";
            }
        } else {
            $sortString = $instance->getProperty($sortProperty, "");
        }
        $sortList[$instance->getId()] = $sortString;
        $instances[$instance->getId()] = $instance;
      }
    }
    // ARCHIVE DATA
    if ($includeArchiveData) {
      $filenameBase = Conf::get("DATA_FILE_NAME_ARCHIVE_BASE");
      foreach (glob($filenameBase . "/$instanceType/data_$instanceType*.data") as $filename) {
        $instance = self::loadInstanceByFilename($filename);
        $add = !$filterBudget || ($instance->belongsTo($budget));
        if ($add) {
            if(is_array($sortProperty)) {
                $sortString = "";
                foreach($sortProperty as $prop) {
                    $sortString .= $instance->getProperty($prop, "");
                }
            } else {
                $sortString = $instance->getProperty($sortProperty, "");
            }
          $sortList[$instance->getId()] = $sortString;
          $instances[$instance->getId()] = $instance;
        }
      }
    }

    if ($descending) {
      arsort($sortList);
    } else {
      asort($sortList);
    }
    $result = array();
    foreach ($sortList as $id => $key) {
      $result[] = $instances[$id];
    }
    return $result;
  }

  static private function loadInstanceByFilename($filename) {
    Request::getSingleInstance()->addMessage("LOAD $filename");
    Request::getSingleInstance()->setMessageLevel("debug");
    $instance = self::loadInstance(file_get_contents($filename));
    $x = $instance->getProperty("info", "UnkNon");
    if (false && $x != "UnkNon") {
      $y = $instance->getProperty("text", "");
      $t = "<br/>-----<br/>";
      if (stripos($y, $t) === false) {
        $instance->setProperty("$x$t$y", "text");
        self::saveInstance($instance);
      }
    }
    return $instance;
  }

  public static function loadInstanceById($id) {
    $filename = self::getPathAndFilename($id);
    if (!file_exists($filename)) {
      return NULL;
    }
    return self::loadInstanceByFilename($filename);
  }

  /**

   */
  private static function loadInstance($instanceString) {
    $properties = array();
    foreach (explode(self::DATA_FILE_PROPERTY_SEPARATOR, $instanceString) as $prop) {
      if (stripos($prop, self::DATA_FILE_NAME_VALUE_SEPARATOR) === FALSE) {
        throw new Exception($prop);
      }
      list($name, $value) = explode(self::DATA_FILE_NAME_VALUE_SEPARATOR, $prop);
      if (in_array($name, array("stunden", "kaz", "btotal"))) {
        $value = $value == "" ? "" : number_format((float) $value, 2, '.', '');
      }
      $properties[$name] = $value;
    }
    $instance = self::createInstance($properties[self::PROPERTY_ID]);
    $instance->setProperties($properties);
    return $instance;
  }

  /**
   * Wird zur Zeit nicht genutzt, da nicht alle Instanzen einer Klasse in einer
   * Datei gespeichert werden, sondern jeder Instanz einzeln - die ganze ID bildet
   * also einen Teil des Dateinamens.
   * @param type $id
   * @return type
   */
  static private function getClassNameFromId($id) {
    list($className, $rest) = explode(self::ID_CLASS_NAME_SEPARATOR, $id, 2);
    return $className;
  }

  static public function getPathAndFilename($id) {
    $instanceType = self::getClassNameFromId($id);
    return Conf::get("DATA_FILE_NAME_BASE") . "/$instanceType/data_$id.data";
  }

  static private function getBackupPathAndFilename($id) {
    $instanceType = self::getClassNameFromId($id);
    return Conf::get("DATA_FILE_NAME_BACKUP_BASE") . "/$instanceType/data_$id.data";
  }

  static private function getArchivePathAndFilename($id) {
    $instanceType = self::getClassNameFromId($id);
    return Conf::get("DATA_FILE_NAME_ARCHIVE_BASE") . "/$instanceType/data_$id.data";
  }

  /**
   * Erstellt einen neue Instanz. Die Instanz kann neu sein und aus den
   * HTML Form Daten stammen oder sie stammt aus dem Laden der persistenten Objekte.
   * @param type $id
   */
  static protected function createInstance($id) {
    if (!strpos($id, self::ID_CLASS_NAME_SEPARATOR)) {
      throw new \Exception("$id ist keine ID!");
    }
    list($className, $rest) = explode(self::ID_CLASS_NAME_SEPARATOR, $id);
    $nameSpacedClassName = "$className";
    $instance = new $nameSpacedClassName($id);
    return $instance;
  }

  /**
   * Erstellt einen neue Instanz. Die Instanz kann neu sein und aus den
   * HTML Form Daten stammen oder sie stammt aus dem Laden der persistenten Objekte.
   * @param type $id
   */
  static public function getOrCreateInstance($id) {
    $instance = self::loadInstanceById($id);
    $instance = $instance == NULL ? self::createInstance($id) : $instance;
    return $instance;
  }

  /**
   * Speichert eine vollstaendige Instanz.
   */
  public static function saveInstance($instance) {

    $props = array();
    foreach ($instance->getProperties() as $name => $value) {
      $props[] = "$name" . self::DATA_FILE_NAME_VALUE_SEPARATOR . $value;
    }
    $content = implode(self::DATA_FILE_PROPERTY_SEPARATOR, $props);
    $filename = self::getPathAndFilename($instance->getId());
    $backupFilename = self::getBackupPathAndFilename($instance->getId());
    Request::getSingleInstance()->addMessage("SAVE " . $instance->getId() . " $content : $filename : $backupFilename");
    Request::getSingleInstance()->setMessageLevel("info");
    file_put_contents($filename, $content);
    file_put_contents($backupFilename, $content);
  }

  /**
   * Loescht eine vollstaendige Instanz.
   */
  public static function deleteInstance($id, $archive) {
    $filename = self::getPathAndFilename($id);
    if (file_exists($filename)) {
      $content = file_get_contents($filename);
      file_put_contents(self::getBackupPathAndFilename($id), $content);
      if ($archive) {
        $archiveFilename = self::getArchivePathAndFilename($id);
        file_put_contents($archiveFilename, $content);
      }
      unlink($filename);
    }
  }

}
