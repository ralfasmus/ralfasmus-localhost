<?php

/**
 * @author asmusr
 */
interface Properties_Interface {

    // Primaerer view zum Anzeigen eines Property Objekts:
    const PROPERTY_VIEW                 = "view";
    // Default view/template zum Anzeigen eines Property Objekts, falls dieses keine oder eine leere ("") Property "view" hat:
    const PROPERTY_VIEW_DEFAULT         = "default";

    const PLACE_HOLDER_PROPERTY_NAME         = "PLACE_HOLDER";
    const PLACE_HOLDER_CONFIG           = "CONFIG";
    const PLACE_HOLDER_PROPERTY         = "PROPERTY";
    const PLACE_HOLDER_ITEM             = "ITEM";
    const PLACE_HOLDER_PROPERTY_DEFAULT = self::PLACE_HOLDER_PROPERTY;


  /**
   * Liefert eine berechnete oder gespeicherte Property oder $default,
   * wenn sie nicht gesetzt ist.
   * @param type $key
   * @param type $default
   * @return type
   * @throws Exception
   */
  public function getProperty(string $key, $default = "exception", bool $defaultOnEmpty = false);

  /**
   * Setzt die uebergebenen Properties. Bereits gesetzte Properties werden ueberschrieben. Hier nicht
   * uebergebene Properties bleiben erhalten.
   */
  public function setProperties(array $properties);

  /**
   * Liefert die Properties als iterierbares array.
   */
  public function getProperties() : array;

  /**
   *
   */
  public function setProperty($value, string $key);

  /**
   *
   */
  public function getDecodedProperty(string $key, $default = "exception") : string;
}
