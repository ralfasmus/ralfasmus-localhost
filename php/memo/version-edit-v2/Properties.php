<?php

/**
 * @author asmusr
 */
class Properties implements Properties_Interface {

  /**
   * Meine Properties, die gespeichert werden.
   * @var type array
   */
  private $properties = array();

  /**
   * Siehe interface description.
   */
  public function getProperty(string $key, $default = "exception", bool $defaultOnEmpty = false) {
    $properties = $this->getProperties();
    if (isset($properties[$key])) {
      $result = $properties[$key];
    } else {
      $result = $default;
    }
    if ($defaultOnEmpty && ((is_string($result) && $result == "") || (is_null($result)))) {
      $result = $default;
    }

    if (is_string($result) && $result == "exception") {
      throw new Exception("Property $key hat keinen Wert in Objekt " . get_class($this));
    }
    return $result;
  }

  /**
   *
   */
  private function trimPropertyValue($value) {
    if (is_scalar($value)) {
      $value = is_string($value) ? trim($value) : $value;
      if (is_numeric(str_replace(array(",", "."), "", $value))) {
        $value = str_replace(",", ".", $value);
      }
    }
    return $value;
  }

  /**
   * Siehe interface description.
   */
  public function setProperties(array $properties) {
    foreach ($properties as $name => $value) {
      $this->properties[$name] = $value;
    }
  }

  /**
   * Siehe interface description.
   */
  public function getProperties() : array {
    return $this->properties;
  }

  /**
   * Siehe interface description.
   */
  public function setProperty($value, string $key) {
    $wert = $this->trimPropertyValue($value);
    $this->properties[$key] = $wert;
  }

  /**
   * Siehe interface description.
   */
  public function getDecodedProperty(string $key, $default = "exception") : string {
    return rawurldecode($this->getProperty($key, $default));
  }

}
