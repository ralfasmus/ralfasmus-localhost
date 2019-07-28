<?php
/**
 * Zugriff auf ein Set von Properties der Form name => wert.
 * Interface Properties_Interface
 */
interface Properties_Interface
{

    /**
     * Primaerer view zum Anzeigen eines Property Objekts
     */
    const PROPERTY_VIEW = "view";
    /**
     * Default view/template zum Anzeigen eines Property Objekts, falls dieses keine oder eine leere ("") Property "view" hat:
     */
    const PROPERTY_VIEW_DEFAULT = "default";

    /**
     * Property dessen Wert fuer ein Properties Objekt festlegt, welche PLATZHALTER im View Template ersetzt werden.
     * Bsp: PLACE_HOLDER_CONFIG_name_VALUE wird nur durch ein config-Note-Instanz ersetzt
     * Bsp: PLACE_HOLDER_NOTE_name_VALUE wird nur durch die aktuelle Note-Instanz des Requests (Parameter id) ersetzt
     */
    const PROPERTY_PLACE_HOLDER_INDICATOR = "placeholder-indicator";
    /**
     * Platzhalter Indicator fuer Properties der geladenen Config Note Instanz in View Templates
     */
    const PROPERTY_PLACE_HOLDER_INDICATOR_CONFIG = "CONFIG";
    /**
     * In View Templates: Platzhalter Indicator fuer Properties, die wiederum durch Laden eines Templates oder andere
     * Berechnung ersetzt werden. Jedenfalls keine Note oder Config Note Instanz Properties sind.
     *
     */
    const PROPERTY_PLACE_HOLDER_INDICATOR_PROPERTY = "PROPERTY";
    /**
     * Platzhalter Indicator fuer Properties einer Instanz (z.B. Artlist Item oder geladene Note Instanzen)
     * in View Templates
     */
    const PROPERTY_PLACE_HOLDER_INDICATOR_NOTE = "NOTE";
    /**
     * Ist die Property mit Name Properties_Interface::PLACE_HOLDER_PROPERTY_NAME fuer ein Item nicht gesetzt, so wird
     * dieser Wert angenommen.
     */
    const PROPERTY_PLACE_HOLDER_INDICATOR_DEFAULT = self::PROPERTY_PLACE_HOLDER_INDICATOR_PROPERTY;

    /**
     * Liefert eine berechnete oder gespeicherte Property.
     * Wenn die Property nicht gesetzt ist, wird $default geliefert.
     * Ist in dem Fall aber $default == "exception", wird eine Exception geworfen.
     * Ist $defaultOnEmpty == true, so wird im Falle das der Property Wert gesetzt aber leer oder null ist,
     * der $default geliefert bzw. dann auch eine Exception geworfen, wenn $defaultOnEmpty == "exception".
     *
     * @param string $key
     * @param mixed $default
     * @param bool $defaultOnEmpty wenn true, so wird der $default auch geliefert, wenn der Property Wert sonst "" waere.
     * @return mixed
     */
    public function getProperty(string $key, $default = "exception", bool $defaultOnEmpty = false);

    /**
     * Setzt die uebergebenen Properties. Bereits gesetzte Properties werden ueberschrieben. Hier nicht
     * uebergebene Properties bleiben erhalten.
     *
     * @param array $properties Array name => wert
     * @return mixed
     */
    public function setProperties(array $properties);

    /**
     * Liefert die Properties als iterierbares array name => wert.
     * @return array
     */
    public function getProperties(): array;

    /**
     * Setzt $value fuer Property mit Name $key
     *
     * @param $value
     * @param string $key
     * @return mixed
     */
    public function setProperty($value, string $key);

    /**
     * Liefert die Property rawurldecoded. @see Properties_Interface::getProperty(). Es gibt hier keinen $defaultOnEmpty
     * Parameter, weil nicht benoetigt und nnicht sinnvoll.
     *
     * @param string $key
     * @param string $default
     * @return string
     */
    public function getDecodedProperty(string $key, $default = "exception"): string;
}
