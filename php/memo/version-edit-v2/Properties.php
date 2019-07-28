<?php
/**
 * Implementation von @see Properties_Interface
 * Class Properties
 */
class Properties implements Properties_Interface
{
    /**
     * Properties constructor.
     * @param array $properties
     */
    public function __construct(array $properties = array())
    {
        $this->properties = $properties;
    }

    /**
     * Meine Properties in der Form name => wert.
     * @var type array
     */
    private $properties = array();

    /**
     * @see Properties_Interface::getProperty()
     *
     * @param string $key
     * @param string $default
     * @param bool $defaultOnEmpty
     * @return mixed|string|type
     * @throws Exception
     */
    public function getProperty(string $key, $default = "exception", bool $defaultOnEmpty = false)
    {
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
            throw new Exception("Property $key hat keinen Wert in Instanz der Klasse: " . get_class($this) . var_dump($this, true));
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
    private function trimPropertyValue($value)
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
     * @see Properties_Interface::setProperties()
     * @param array $properties
     */
    public function setProperties(array $properties)
    {
        foreach ($properties as $name => $value) {
            $this->properties[$name] = $value;
        }
    }

    /**
     * @see Properties_Interface::getProperties()
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @see Properties_Interface::setProperty()
     * @param $value
     * @param string $key
     */
    public function setProperty($value, string $key)
    {
        $wert = $this->trimPropertyValue($value);
        $this->properties[$key] = $wert;
    }

    /**
     * @see Properties_Interface::getDecodedProperty()
     * @param string $key
     * @param string $default
     * @return string
     * @throws Exception
     */
    public function getDecodedProperty(string $key, $default = "exception"): string
    {
        return rawurldecode($this->getProperty($key, $default));
    }

}
