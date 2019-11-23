<?php
/**
 * Implementation von @see Properties_Interface
 *
 * trait Properties_Trait
 */
trait Properties_Trait
{
    /**
     * Meine Properties in der Form name => wert.
     * @var type array
     */
    private $properties = array();

    private $propertiesInitialized = false;

    private function initializePropertiesTrait() : self {
        assert(!$this->propertiesInitialized, __CLASS__ . '->propertiesInitialized ist schon TRUE. Nochmal initialisieren nicht erlaubt.');
        $this->propertiesInitialized = true;
        return $this;
    }
    /**
     * @param string $key
     * @param string $default
     * @param bool $defaultOnEmpty
     * @return mixed|string|type
     * @throws Exception
     *@see Properties_Interface::getPropertyDefault()
     *
     */
    final public function getPropertyDefault(string $key, $default = '', bool $defaultOnEmpty = false)
    {
        assert($this->propertiesInitialized, __CLASS__ . '->propertiesInitialized ist FALSE.');
        $result = isset($this->properties[$key]) ? $this->properties[$key] : $default;
        return $defaultOnEmpty ? $this->defaultOnEmpty($result, $default) : $result;
    }

    final private function defaultOnEmpty($value, $default) {
        if((is_string($value) && $value == '') || (is_null($value))) {
            $value = $default;
        }
        return $value;
    }

    final public function setProperties(array $properties) : void {
        foreach($properties as $name => $value) {
            $this->setProperty($value, $name);
        }
    }


    /**
     * @param string $key
     * @param bool $exceptionOnEmpty wenn true (=default), wird eine Exception auch geworfen, wenn die Property zwar
     * gesetzt, aber ein leerer String oder null ist.
     * @param string $exceptionText
     * @return mixed
     *@see Properties_Interface::getPropertyMandatory()
     *
     */
    final public function getPropertyMandatory(string $key, bool $exceptionOnEmpty = true, string $exceptionText = '')
    {
        $result = $this->getPropertyDefault($key, 'unDEfineeD', $exceptionOnEmpty);
        if($result == 'unDEfineeD') {
            MyThrowable::throw("$exceptionText : Property $key ist nicht gesetzt oder leer in Instanz der Klasse: " . get_class($this) . '<br>' . var_export($this, true));
        }
        return $result;
    }

    /**
     * Normalisierung vor dem Speichern:
     * Strings: leading + trailing Spaces entfernen.
     * Dezimalzahlen: "," durch "." ersetzen (besser fuer spaetere Javascript-Verarbeitung).
     * @param $value
     * @return bool|float|int|mixed|string
     */
    final private function trimPropertyValue($value)
    {
        if (is_scalar($value)) {
            $value = is_string($value) ? trim($value) : $value;
            if (is_numeric(str_replace(array(",", "."), "", $value))) {
                $value = str_replace(",", ".", $value);
            }
        }
        return $value;
    }

    /**
     * @param $value
     * @param string $key
     *@see Properties_Interface::setProperty()
     */
    final public function setProperty($value, string $key) : void
    {
        $wert = $this->trimPropertyValue($value);
        $this->properties[$key] = $wert;
    }

    /**
     * @param string $key
     * @param string $default
     * @return string
     * @throws Exception
     *@see Properties_Interface::getDecodedProperty()
     */
    final public function getDecodedProperty(string $key, string $default = '', $defaultOnEmpty = false) : string
    {
        return rawurldecode($this->getPropertyDefault($key, $default, $defaultOnEmpty));
    }
    final public function getValueDoubleQuote2singleQuote(string $value) : string
    {
        return str_replace('"',"'", $value);
    }

}