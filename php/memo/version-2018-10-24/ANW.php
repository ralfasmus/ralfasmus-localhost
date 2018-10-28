<?php

/**
 * Eine Instanz stellt die ANW fuer N Stunden am Datum/Tag X dar.
 */
class ANW extends ObjectAbstract {

  /**
   * Hier muessen bei persistenten Objekten die Eingabefelder = persistente
   * Properties gesetzt werden.
   * @param type $id
   */
  public function __construct($id) {
    parent::__construct($id);
      $this->setProperty(Zeit::datumDmy(Zeit::heute()), self::PROPERTY_DATUM);
  }

  public function isPersistentProperty($name) {
    return in_array($name, array(
        self::PROPERTY_STUNDEN,
        self::PROPERTY_ZEITEN,
        self::PROPERTY_DATUM,
        self::PROPERTY_BEMERKUNG,
        self::PROPERTY_SCHON_IN_KAZ,
    ));
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
    $this->addElement(Element::create(self::PROPERTY_BEMERKUNG)
                    ->addAttribute("value", $this->getProperty(self::PROPERTY_BEMERKUNG, "")));
    $this->addElement(Element::create(self::PROPERTY_STUNDEN)
                    ->addAttribute("value", $this->getProperty(self::PROPERTY_STUNDEN, "")));
    $this->addElement(Element::create(self::PROPERTY_ZEITEN)
                    ->addAttribute("value", $this->getProperty(self::PROPERTY_ZEITEN, "")));
    $this->addElement(Element::create(self::PROPERTY_DATUM)
                    ->addAttribute("value", $this->getProperty(self::PROPERTY_DATUM, "")));
    $this->addElement(Element::create(self::PROPERTY_SCHON_IN_KAZ)
                    ->addAttribute("value", $this->getProperty(self::PROPERTY_SCHON_IN_KAZ, "")));
    return parent::getElements();
  }

}
