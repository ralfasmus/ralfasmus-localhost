<?php

namespace Allgemein;

class Request  {

    static private $singleInstance = null;

    private $properties = array();

    /**
     * Erzeugt einen Request
     */
    static public function getSingleInstance($requestProperties = array()) {
        if (self::$singleInstance == null) {
            self::$singleInstance = new Request();
            foreach ($requestProperties["post"] as $name => $value) {
                self::$singleInstance->setProperty($value, $name);
            }
            foreach ($requestProperties["get"] as $name => $value) {
                self::$singleInstance->setProperty($value, $name);
            }
        }
        return self::$singleInstance;
    }

    public function getDecodedProperty($key, $default = "exception") {
        return rawurldecode($this->getProperty($key, $default));
    }



    public function isSubmit() {
        return $this->getProperty("submit", "nix submit") != "nix submit";
    }

    /**
     * Liefert eine berechnete oder gespeicherte Property oder $default,
     * wenn sie nicht gesetzt ist.
     * @param type $key
     * @param type $default
     * @return type
     * @throws Exception
     */
    public function getProperty($key, $default = "exception") {
        $result = $default;
        $properties = $this->getProperties();
        if (isset($properties[$key])) {
            $result = $properties[$key];
        }
        if (is_string($result) && $result == "exception") {
            throw new Exception("Property $key hat keinen Wert in Objekt " . get_class($this));
        }
        return $result;
    }

    protected function trimPropertyValue($value) {
        if (is_scalar($value)) {
            $value = is_string($value) ? trim($value) : $value;
            if (is_numeric(str_replace(array(",", "."), "", $value))) {
                $value = str_replace(",", ".", $value);
            }
        }
        return $value;
    }

    public function setProperties($properties) {
        foreach ($properties as $name => $value) {
            $this->properties[$name] = $value;
        }
    }

    /**
     *
     */
    public function getProperties() {
        return $this->properties;
    }

    /**
     *
     */
    public function setProperty($value, $key) {
        $wert = $this->trimPropertyValue($value);
        $this->properties[$key] = $wert;
        return $wert;
    }


}
