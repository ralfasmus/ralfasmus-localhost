<?php

/**
 * Eine Instanz stellt eine Son dar
 */
class SON extends ObjectAbstract {

  /**
   * Hier muessen bei persistenten Objekten die Eingabefelder = persistente
   * Properties gesetzt werden.
   * @param type $id
   */
  public function __construct($id = null) {
    parent::__construct($id);
  }

  public function isPersistentProperty($name) {
    return in_array($name, array("name", "info", "pos"));
  }

  /**
   * Alle Elemente dieser Instance , die in der Website dargestellt werden sollen.
   * @return type
   */
  public function getElements() {
    parent::getElements();
    $this->addElement(Element::create(self::PROPERTY_NAME)
                    ->addAttribute("value", $this->getProperty(self::PROPERTY_NAME, "")));
    $this->addElement(Element::create("info")
                    ->addAttribute("value", $this->getProperty("info", "")));
    $this->addElement(Element::create("pos")
                    ->addAttribute("value", $this->getProperty("pos", "")));
    return parent::getElements();
  }

}
