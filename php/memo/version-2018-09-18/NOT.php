<?php

/**
 * Eine Instanz stellt eine Notiz dar
 */
class NOT extends ObjectAbstract {

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
    return in_array($name, array("name", "text", "files", "art", self::PROPERTY_MARK1));
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
    $this->addElement(Element::create("text")
                    ->addAttribute("value", $this->getProperty("text", "")));
    $this->addElement(Element::create("files")
                    ->addAttribute("value", $this->getProperty("files", "")));
    $this->addElement(Element::create("art")
                    ->addAttribute("value", $this->getProperty("art", "")));
      $this->addElement(Element::create(self::PROPERTY_MARK1)
              ->addAttribute("value", $this->getProperty(self::PROPERTY_MARK1, "")));
    $this->addElement(Element::create("notefilename")
                    ->addAttribute("value", $this->getId() . ':' . Persistence::getPathAndFilename($this->getId())));
    return parent::getElements();
  }

}
