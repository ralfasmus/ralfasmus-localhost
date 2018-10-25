<?php

/**
 * Eine Instanz stellt Freitext dar.
 */
class FRE extends ObjectAbstract {

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
        self::PROPERTY_NAME,
        self::PROPERTY_DATUM,
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
    $this->addElement(Element::create(self::PROPERTY_DATUM)
                    ->addAttribute("value", $this->getProperty(self::PROPERTY_DATUM, "")));
    return parent::getElements();
  }

}
