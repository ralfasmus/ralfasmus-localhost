<?php

/**
 * Eine Instanz stellt ein BUD dar, dem ARBen und KAZ-Eintraege zugeordnet
 * sein koennen. Eine BUD muss einen Namen haben und kann eine
 * totale, monatliche, jaehrliche oder keine BUD-Groesse (in h) haben.
 */
class BUD extends ObjectAbstract {

  /**
   * Hier muessen bei persistenten Objekten die Eingabefelder = persistente
   * Properties gesetzt werden.
   * @param type $id
   */
  public function __construct($id = null) {
    parent::__construct($id);
  }

  public function isPersistentProperty($name) {
    return in_array($name, array(
        self::PROPERTY_NAME,
        self::PROPERTY_KEYS,
        self::PROPERTY_BTOTAL,
        self::PROPERTY_ZEITRAUM,
        self::PROPERTY_KORREKTUR,
        self::PROPERTY_KORREKTUR_BEMERKUNG
    ));
  }

  /**
   * Alle Elemente dieser Instance , die in der Website dargestellt werden sollen.
   * @return type
   */
  public function getElements() {
    parent::getElements();

    $this->addElement(Element::create(self::PROPERTY_NAME)
                    ->addAttribute("value", $this->getProperty(self::PROPERTY_NAME, "")));
    $this->addElement(Element::create(self::PROPERTY_KEYS)
                    ->addAttribute("value", $this->getProperty(self::PROPERTY_KEYS, "")));
    $this->addElement(Element::create(self::PROPERTY_ZEITRAUM)
                    ->addAttribute("value", $this->getProperty(self::PROPERTY_ZEITRAUM, "")));
    $this->addElement(Element::create(self::PROPERTY_KORREKTUR)
                    ->addAttribute("value", $this->getProperty(self::PROPERTY_KORREKTUR, "")));
    $this->addElement(Element::create(self::PROPERTY_KORREKTUR_BEMERKUNG)
                    ->addAttribute("value", $this->getProperty(self::PROPERTY_KORREKTUR_BEMERKUNG, "")));
    $this->addElement(Element::create(self::PROPERTY_BTOTAL)
                    ->addAttribute("value", $this->getProperty(self::PROPERTY_BTOTAL, "")));
    return parent::getElements();
  }

}
