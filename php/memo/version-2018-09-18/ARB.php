<?php

/**
 * Eine Instanz stellt eine geleistete ARB dar, die am Datum geleistet wurde,
 * die Stunden oder 0 dauerte und BUD oder keinem BUD zugeordnet ist und
 * Bemerkungen in "Name" hat, oder auch nicht.
 */
class ARB extends ObjectAbstract {

  /**
   * Hier muessen bei persistenten Objekten die Eingabefelder = persistente
   * Properties gesetzt werden.
   * @param type $id
   */
  public function __construct($id = null) {
    parent::__construct($id);
    $this->setProperty(Zeit::datumDmy(Zeit::heute()), self::PROPERTY_DATUM);
  }

  public function isPersistentProperty($name) {
    return in_array($name, array(
        self::PROPERTY_STUNDEN,
        self::PROPERTY_KAZ,
        self::PROPERTY_DATUM,
        self::PROPERTY_NAME,
        self::PROPERTY_DATUM_ARB_EINGETRAGEN_IN_KAZ,
        self::PROPERTY_KAZ_KANN_SPAETER,
        self::PROPERTY_MARK1
    ));
  }

  public function belongsTo($budget) {
    $keys = explode("~", trim($budget->getProperty(ObjectAbstract::PROPERTY_KEYS, "abcdefg")));
    $propertyToMatch = $this->getProperty(ObjectAbstract::PROPERTY_NAME, "") . " " . $this->getProperty(ObjectAbstract::PROPERTY_BEMERKUNG, "");
    $found = false;
    foreach ($keys as $key) {
      $key = trim($key);
      if ($key != "") {
        $found = $found || stripos($propertyToMatch, $key) !== FALSE;
      }
    }
    return $found;
  }

    /**
     * Das Objekt wurde aktualisiert (oder verändert).
     */
    public function setChanged()
    {

    }

  /**
   * Alle Elemente dieser Instance , die in der Website dargestellt werden sollen.
   * @return type
   */
  public function getElements() {
    parent::getElements();
    $this->addElement(Element::create(self::PROPERTY_NAME)
                    ->addAttribute("value", $this->getProperty(self::PROPERTY_NAME, "")));
    $this->addElement(Element::create(self::PROPERTY_STUNDEN)
                    ->addAttribute("value", $this->getProperty(self::PROPERTY_STUNDEN, "")));
    $this->addElement(Element::create(self::PROPERTY_KAZ)
                    ->addAttribute("value", $this->getProperty(self::PROPERTY_KAZ, "")));
    $this->addElement(Element::create(self::PROPERTY_DATUM)
                    ->addAttribute("value", $this->getProperty(self::PROPERTY_DATUM, "")));
      $this->addElement(Element::create(self::PROPERTY_KAZ_KANN_SPAETER)
              ->addAttribute("value", $this->getProperty(self::PROPERTY_KAZ_KANN_SPAETER, "")));
      $this->addElement(Element::create(self::PROPERTY_MARK1)
              ->addAttribute("value", $this->getProperty(self::PROPERTY_MARK1, "")));
      $this->addElement(Element::create(self::PROPERTY_DATUM_ARB_EINGETRAGEN_IN_KAZ)
              ->addAttribute("value", $this->getProperty(self::PROPERTY_DATUM_ARB_EINGETRAGEN_IN_KAZ, "")));
    return parent::getElements();
  }

}
