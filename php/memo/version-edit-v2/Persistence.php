<?php


class Persistence extends ObjectAbstract {

  /**
   * Fuer die Datei auf dem Filesystem.
   */
  const DATA_FILE_NAME_VALUE_SEPARATOR = "§";
  const DATA_FILE_PROPERTY_SEPARATOR = "°";
  const DATA_FILE_INSTANCE_SEPARATOR = "\n";


  private static function loadInstances($itemType, $status, &$instances) {
      $filenameBase = Conf::get("DATA_FILE_NAME_BASE");

      foreach (glob($filenameBase . "/$status/*") as $filename) {
          $instance = self::loadInstanceByFilename($filename);
          if($instance->getProperty("itemtype", "") == $itemType) {
              $instances[] = $instance;
          }
      }
  }

  /**
   * Liefert alle Instanzen sortiert.
   * @return type
   */
  public static function getInstances($itemType, $sortProperty, $descending) {

      $instances = array();
      $status = self::request()->getStatus();
      if(stripos($status, "active") !== false) {
          self::loadInstances($itemType, "active", $instances);
      }
      if(stripos($status, "deleted") !== false) {
          self::loadInstances($itemType, "deleted", $instances);
      }
      if(stripos($status, "backup") !== false) {
          self::loadInstances($itemType, "backup", $instances);
      }

      $sortList = array();
      $instanceList = array();

      foreach ($instances as $instance) {
        if(is_array($sortProperty)) {
            $sortString = "";
            foreach($sortProperty as $prop) {
                $sortString .= $instance->getProperty($prop, "") . " ...";
            }
        } else {
            $sortString = $instance->getProperty($sortProperty, "");
        }
        $sortList[$instance->getId()] = $sortString;
        $instanceList[$instance->getId()] = $instance;
    }

    if ($descending) {
      arsort($sortList);
    } else {
      asort($sortList);
    }
    $result = array();
    foreach ($sortList as $id => $key) {
      $result[] = $instanceList[$id];
    }
    return $result;
  }

  public static function newId() {
     $newId = "active_id" . Zeit::datumYmdHis(Zeit::heute()) . "_" . Zeit::datumYmdH(Zeit::heute());
     return $newId;
  }

  static private function loadInstanceByFilename($filename) {
    $instance = self::loadInstance(file_get_contents($filename));
    return $instance;
  }

  public static function loadInstanceById($id) {
    if ($id == "") {
      return NULL;
    }
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
  static public function getPathAndFilename($id) {
    list($path, $postfix) = explode("_", $id); // active|deleted|backup
    return Conf::get("DATA_FILE_NAME_BASE") . "/$path/$id";
  }

  /**
   * Erstellt einen neue Instanz. Die Instanz kann neu sein und aus den
   * HTML Form Daten stammen oder sie stammt aus dem Laden der persistenten Objekte.
   * @param type $id
   */
  static protected function createInstance($id) {
    $instance = new NOT($id);
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

  public static function updateInstanceFromRequest($instance) {
      foreach (self::request()->getProperties() as $name => $value) {
          $instance->setProperty($value, $name);
      }
      return $instance;
  }

  /**
   * Speichert eine vollstaendige Instanz.
   */
  public static function saveInstance($instance) {
    foreach ($instance->getProperties() as $name => $value) {
      $props[] = "$name" . self::DATA_FILE_NAME_VALUE_SEPARATOR . $value;
    }
    $content = implode(self::DATA_FILE_PROPERTY_SEPARATOR, $props);
    $id = $instance->getProperty("id");
    $filename = self::getPathAndFilename($id);
    file_put_contents($filename, $content);
  }

  private static function getStatus($instance) {
      list($status, $postfix) = explode("_", $instance->getProperty("id")); // active|deleted|backup
      return $status;
  }


  /**
   * Loescht eine vollstaendige Instanz.
   */
  public static function deleteInstance($instance) {
    $status = self::getStatus($instance);
    $filename = self::getPathAndFilename($instance);
    if($status == "active" || $status == "deleted") {
        self::backupInstance($instance);
    }
    unlink($filename);
  }

    /**
     * Erstellt Backup fuer eine active oder deleted oder backup Instanz.
     */
    public static function backupInstance($instance) {
        list($status, $idFragment, $version) = explode("_", $instance->getProperty("id"));
        if($status == "backup") {
            $version = Zeit::datumYmdHis(Zeit::heute());
        }
        $newId = "backup_{$idFragment}_{$version}";
        $instance->setProperty($newId, "id");
        self::saveInstance($instance);
    }

    private static function request() {
        return Request::getSingleInstance();
    }

}
