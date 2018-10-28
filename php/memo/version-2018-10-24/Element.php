<?php

/**
 * Allgemeine Methoden zur Erstellung eines HTML Elements.
 * @author asmusr
 */
class Element {

  /**
   * HTML Attribute und Inhalt (index "html") des Elements.
   * @var type
   */
  private $data = array("attributes" => array());

  static public function create($elementName) {
    $element = new Element();
    $element->data["element"] = $elementName;
    return $element;
  }

  /**
   *
   * @param type $html
   * @return \Element
   */
  public function setHtml($html) {
    $this->data["html"] = $html;
    return $this;
  }

  public function addAttribute($name, $value) {
    /**
     * Suche immer ueber lowercase Strings:
     */
    if (strpos($name, "data-select") === 0) {
      $value = strtolower($value);
    }
    $this->data["attributes"][$name] = $value;
    return $this;
  }

  public function getName() {
    return $this->data["element"];
  }

  public function getData() {
    return $this->data;
  }

}
