<?php

/**
 * Alle Funktionen zum persistenten Laden und Speichern von Items.
 */
class Persistence {


  static private $itemsCache = array();
  static private $itemsCacheIsValid = false;

  /**
   * Laedt alle Items in den Cache, die dem status des requests entsprechen und mit den im Filter angegebenen views matchen.
   *
   * @param string $view
   */
  private static function loadItems(string $filterViews, string $status) : array {
      if(!self::$itemsCacheIsValid) {
          $filenameBase = Conf::get("DATA_FILE_NAME_BASE");
          foreach (glob($filenameBase . "/$status/*") as $filename) {
              $item = self::loadItemByFilename($filename);
              if($item->hasViewsMatchingFilterViews($filterViews)) {
                  self::$itemsCache[] = $item;
              }
          }
          self::$itemsCacheIsValid = true;
      }
      return self::$itemsCache;
  }

  /**
   * Liefert alle Instanzen sortiert.
   * @return type
   */
  public static function getItems(string $filterViews, string $sortProperty, bool $descending, string $status) : array {

      $items = self::loadItems($filterViews, $status);

      $sortList = array();
      $itemList = array();

      foreach ($items as $item) {
        if(is_array($sortProperty)) {
            $sortString = "";
            foreach($sortProperty as $prop) {
                $sortString .= $item->getProperty($prop, "") . " ...";
            }
        } else {
            $sortString = $item->getProperty($sortProperty, "");
        }
        $sortList[$item->getId()] = $sortString;
        $itemList[$item->getId()] = $item;
      }

      if ($descending) {
        arsort($sortList);
      } else {
        asort($sortList);
      }
      $result = array();
      foreach ($sortList as $id => $key) {
        $result[] = $itemList[$id];
      }
      return $result;
  }

  /**
   *
   *
   */
  static private function loadItemByFilename(string $filename) : Item {
      $item = NULL;
      try {
        $item = self::loadItem(file_get_contents($filename));
      } catch (Throwable $throwable) {
        Log::error("Kann Datei $filename nicht finden.");
        throw $throwable;
      }
      return $item;
  }

  /**
   * Laedt ein im aktuellen Status gespeichertes Item. Liefert NULL wenn keins gefunden.
   */
  public static function loadItemById(string $id, string $status) : ?Item {
    if ($id == "" || is_null($id)) {
      return NULL;
    }
    $filename = self::getPathAndFilename($id, $status);
    if (!file_exists($filename)) {
      return NULL;
    }
    return self::loadItemByFilename($filename);
  }

  /**
   *
   */
  private static function loadItem(String $itemString) : Item {
    $properties = json_decode($itemString, true);
    $item = new Item($properties["id"]);
    $item->setProperties($properties);
    return $item;
  }

  /**
   * Liefert Pfad und Dateiname zur Instanz, so dass sie daraus geladen
   * werden kann. Der Status ist der aktuelle Status aus dem Request oder der explizit angegebene.
   */
  static public function getPathAndFilename(string $id, string $status) : string {
      return Conf::get("DATA_FILE_NAME_BASE") . "/$status/$id";
  }

  /**
   * Erstellt oder laedt einen Instanz.
   */
  static public function loadOrCreateItem(string $id, Request $request) : Item {
      $item = self::loadItemById($id, $request->getRequestStatus());
      if(is_null($item)) {
        $item = new Item($id);
      }
      return $item;
  }

  /**
   *
   */
  public static function updateItemFromRequest(Item $item, Properties_Interface $propertiesItemPersistent) {
      foreach ($propertiesItemPersistent->getProperties() as $name => $value) {
          $item->setProperty($value, $name);
      }
      return $item;
  }

  /**
   * Speichert eine vollstaendige Instanz unter dem aktuellen Pfad.
   */
  public static function itemSaveToFile(Item $item, string $filename) {
    $props = $item->getProperties();
    file_put_contents("${filename}", json_encode($props, JSON_HEX_QUOT | JSON_HEX_TAG));
    Log::debug("Saved Item to $filename");
  }

     /**
     *  Speichert eine vollstaendige Instanz.
     */
    public static function itemSave(Item $item, Request $request) {
        $filename = self::getPathAndFilename($item->getId(), $request->getRequestStatus());
        self::itemSaveToFile($item, $filename);
        $filename = self::getPathAndFilename($item->getId(),"backup");
        $filename .= $request->getProperty('backupextension', '_backup_zeit_unbekannt', true);
        self::itemSaveToFile($item, $filename);
    }

  /**
   * Loescht eine vollstaendige Instanz.
   */
  public static function itemDelete(Item $item, Request $request) {
    // Item nach deleted kopieren
    $filename = self::getPathAndFilename($item->getId(), "deleted");
    self::itemSaveToFile($item, $filename);
    // Item loeschen
    try {
	    $filename = self::getPathAndFilename($item->getId(), $request->getRequestStatus());
        unlink($filename);
	} catch (Throwable $throwable) {
          Log::error("Kann Datei nicht loeschen.");
          throw $throwable;
    }
  }

    /**
     * Erstellt Backup fuer eine active oder deleted oder backup Instanz.
     */
    public static function itemBackup(Item $item, Request $request) {
        $filename = self::getPathAndFilename($item->getId() . $request->getBackupExtension(), "backup");
        self::itemSaveToFile($item, $filename);
    }

}
