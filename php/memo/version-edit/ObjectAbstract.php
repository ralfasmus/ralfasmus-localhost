<?php

/**
 * Allgemeine Methoden.
 * @author asmusr
 */
abstract class ObjectAbstract {

  const ID_CLASS_NAME_SEPARATOR = "_";

  /**
   * Properties
   */
  // presistente:
  const PROPERTY_ID = "id";
  const PROPERTY_BEMERKUNG = "bemerkung";
  const PROPERTY_BTOTAL = "btotal";
  const PROPERTY_DATUM = "datum";
  const PROPERTY_DATUM_ARB_EINGETRAGEN_IN_KAZ = "datum_arbeit_eingetragen_in_kaz"; // datum, wann (zuletzt) in kaz eingetragen bzw. kaz-feld editiert wurde
  const PROPERTY_KAZ = "kaz";
  const PROPERTY_KAZ_KANN_SPAETER = "kaz_kann_spaeter"; // 1 : kann spaeter in kaz eingetragen werden
  const PROPERTY_MARK1 = "mark1"; // 1 : dieses Element markieren
  const PROPERTY_KEYS = "keys";
  const PROPERTY_NAME = "name";
  const PROPERTY_KORREKTUR = "korrektur";
  const PROPERTY_KORREKTUR_BEMERKUNG = "korrekturbemerkung";
  const PROPERTY_STUNDEN = "stunden";
  const PROPERTY_ZEITEN = "zeiten";
  const PROPERTY_ZEITRAUM = "zeitraum";
  const PROPERTY_SCHON_IN_KAZ = "schon_in_kaz";
  // berechnete:
  const PROPERTY_DATUM_ID_SORT = "DatumE";

  public function isPersistentProperty($name) {
    return false;
  }

  private $elements = array();

  /**
   * Meine Properties, die gespeichert werden.
   * @var type array
   */
  private $properties = array();

  /**
   * Counter zur Erzeugung neuer Instance-IDs
   * @var type
   */
  static private $idCounter = 0;

  /**
   *
   * @param type $id
   */
  protected function __construct($id) {
    $this->setProperty($id, self::PROPERTY_ID);
  }

  /**
   *
   * @return Request
   */
  public function getRequest() {
    return Request::getSingleInstance();
  }

  /**
   * Liefert eine berechnete oder gespeicherte Property oder $default,
   * wenn sie nicht gesetzt ist.
   * @param type $key
   * @param type $default
   * @return type
   * @throws Exception
   */
  public function getProperty($key, $default = "exception") {
    $result = $default;
    $properties = $this->getProperties();
    if (isset($properties[$key])) {
      $result = $properties[$key];
    }
    if (is_string($result) && $result == "exception") {
      throw new Exception("Property $key hat keinen Wert in Objekt " . get_class($this));
    }
    return $result;
  }

  protected function trimPropertyValue($value) {
    if (is_scalar($value)) {
      $value = is_string($value) ? trim($value) : $value;
      if (is_numeric(str_replace(array(",", "."), "", $value))) {
        $value = str_replace(",", ".", $value);
      }
    }
    return $value;
  }

  /**
   * Alle Elemente dieser Instance , die in der Website dargestellt werden sollen.
   * @return type
   */
  public function getElements() {
    if (empty($this->elements)) {
      $this->addElement(Element::create("instance")
                      ->addAttribute("data-instance-type", get_class($this)));
      $this->addElement(Element::create(self::PROPERTY_ID)
                      ->addAttribute("value", $this->getProperty(self::PROPERTY_ID, "")));
    }
    return $this->elements;
  }

  public function addElement(Element $element) {
    $this->elements[$element->getName()] = $element;
    $element->addAttribute("data-instance-id", $this->getId());
    return $this;
  }

  public function setProperties($properties) {
    foreach ($properties as $name => $value) {
      $this->properties[$name] = $value;
    }
  }

  /**
   * Siehe interface description.
   */
  public function getProperties() {
    return $this->properties;
  }

    /**
     * Das Objekt wurde aktualisiert (oder verändert).
     */
    public function setChanged()
    {
        $this->setProperty(Zeit::datumDmy(Zeit::heute()), self::PROPERTY_DATUM);
    }

        /**
   * Siehe interface description.
   */
  public function setProperty($value, $key) {
    $wert = $this->trimPropertyValue($value);
    $this->properties[$key] = $wert;
    if ($key == ObjectAbstract::PROPERTY_DATUM) {
      $sortFeld = Zeit::datumYmdHis(Zeit::getDateTimeFromDatumField($wert)) . '_' . $this->getProperty(self::PROPERTY_ID, "");
      $this->setProperty($sortFeld, ObjectAbstract::PROPERTY_DATUM_ID_SORT);
    }
    return $wert;
  }

  /**
   * Liefert eine ID, die als Cache ID, DB ID, Cookie ID usw. genutzt
   * werden kann.
   */
  public function getId() {
    $id = $this->getProperty("id", "");
    if (!$id) {
      $id = get_class($this) . self::ID_CLASS_NAME_SEPARATOR . Zeit::time("U") . "_" . rand(1000000, 9999999);
      // $this->setProperty(uniqid(Zeit::datum() . "_", true), "besucher_id");
      $id = str_replace("Asm\\", "", $id);
      $this->setProperty($id, "id");
    }
    return $id;
  }

}
