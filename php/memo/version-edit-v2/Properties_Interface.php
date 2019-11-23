<?php
/**
 * Zugriff auf ein statisches Set von Properties der Form name => wert. Keine dynamisch generierten Properties.
 * Interface Properties_Interface
 */
interface Properties_Interface
{

    /**
     * Liefert eine berechnete oder gespeicherte Property.
     * Wenn die Property nicht gesetzt ist, wird $default geliefert.
     * Ist $defaultOnEmpty == true, so wird im Falle dass der Property Wert gesetzt aber leer oder null ist,
     * der $default geliefert.
     *
     * @param string $key
     * @param mixed $default
     * @param bool $defaultOnEmpty wenn true, so wird der $default auch geliefert, wenn der Property Wert sonst "" waere.
     * @return mixed
     */
    public function getPropertyDefault(string $key, $default = '', bool $defaultOnEmpty = false);

    /**
     * Liefert eine berechnete oder gespeicherte Property.
     * Wenn die Property nicht gesetzt ist, wird eine Exception geworfen.
     * @param string $key
     * @param bool $exceptionOnEmpty wenn true (=default), wird eine Exception auch geworfen, wenn die Property zwar
     * gesetzt, aber ein leerer String oder null ist.
     * @param string $exceptionText
     * @return mixed
     */
    public function getPropertyMandatory(string $key, bool $exceptionOnEmpty = true, string $exceptionText = '');

    /**
     * Setzt die uebergebenen Properties. Bereits gesetzte Properties werden ueberschrieben. Hier nicht
     * uebergebene Properties bleiben erhalten.
     *
     * @param array $properties Array name => wert
     * @return mixed
     */
    public function setProperties(array $properties) : void;

    /**
     * Setzt $value fuer Property mit Name $key
     *
     * @param $value
     * @param string $key
     * @return mixed
     */
    public function setProperty($value, string $key) : void;

    /**
     * Liefert die Property rawurldecoded. @param string $key
     * @param string $default
     * @return string
     *@see Properties_Interface::getProperty(). Es gibt hier keinen $defaultOnEmpty
     * Parameter, weil nicht benoetigt und nnicht sinnvoll.
     *
     */
    public function getDecodedProperty(string $key, string $default = '', $defaultOnEmpty = false) : string;
}
