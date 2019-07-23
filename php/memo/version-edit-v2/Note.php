<?php

/**
 * Ein Note ist ein persistenter Container für Daten. Das kann auch ein Config-Note sein.
 */
class Note implements Properties_Interface {

    // Moegliche views/templates zum Anzeigen eines Notes:
    private const PROPERTY_POSSIBLE_VIEWS       = "possible-views";

  private $properties = NULL;

  /**
   *
   * @param type $id
   */
  public function __construct(string $id = '') {
      assert(!empty($id), 'Kann Note nicht erzeugen, weil $id Parameter ungueltig ist');
      $this->properties = new Properties();
      $this->setProperty($id, 'id');
      $this->setProperty(Properties_Interface::PLACE_HOLDER_ITEM, Properties_Interface::PLACE_HOLDER_PROPERTY_NAME);
  }

  /**
   * Liefert eine ID, die als Cache ID, DB ID, Cookie ID usw. genutzt
   * werden kann.
   */
  public function getId() : string {
    return $this->getProperty("id");
  }

  public function getView() {
    return $this->getProperty(self::PROPERTY_VIEW, self::PROPERTY_VIEW_DEFAULT, true);
  }

  /**
   * Liefert alle Views des Notes als Komma separierter String mit mindestens einem , an Beginn und Ende:
   * ,,v1,v2,
   */
  private function getAllViews() {
    return "," . $this->getProperty(self::PROPERTY_POSSIBLE_VIEWS, "") . "," . $this->getView() . ",";
  }

  public function hasViewsMatchingFilterViews(string $filterViews) : bool {
    // $filterViews sind die im Listen Filter-Feld "Views" mit " " (und) getrennt angegebenen Views
    $noteViews = $this->getAllViews();
    $foundAll = true;
    foreach(explode(" ", $filterViews) as $filterView) {
        $foundAll = $foundAll && stripos($noteViews, ",$filterView,") !== false;
    }
    return $foundAll;
  }


  // ############# INTERFACE PROPERTIES #################################

  /**
   * Siehe interface description.
   */
  public function getProperty(string $key, $default = "exception", bool $defaultOnEmpty = false) {
    switch($key) {
      case "data-filter-any" :
                     $result = "";
                     foreach($this->getProperties() as $name => $value) {
                       $result .= "$value ";
                     }
                     break;
      case "data-filter-views" :
                     $result = $this->getAllViews();
                     break;

      case "data-tooltip-html" :
                     $text = isset($properties["text"]) ? $properties["text"] : "";
                     $result = str_replace('"', "'", $text);
                     break;

      default :
                     $result = $this->properties->getProperty($key, $default, $defaultOnEmpty); break;
    }

    return $result;
  }

  /**
   * Siehe interface description.
   */
  public function setProperties(array $properties){
    $this->properties->setProperties($properties);
  }

  /**
   * Siehe interface description.
   */
  public function getProperties() : array {
    return $this->properties->getProperties();
  }

  /**
   * Siehe interface description.
   */
  public function setProperty($value, string $key) {
    $this->properties->setProperty($value, $key);
  }

  /**
   * Siehe interface description.
   */
  public function getDecodedProperty(string $key, $default = "exception") : string {
    return $this->properties->getDecodedProperty($key, $default);
  }

}
