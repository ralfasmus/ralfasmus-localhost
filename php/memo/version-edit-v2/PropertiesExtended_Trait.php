<?php
/**
 * trait PropertiesExtended_Trait Implementation der Funktionalitaet fuer eine Klasse, die statische Properties hat
 * und ueber propertiesExtender weitere Properties liefern kann.
 */
trait PropertiesExtended_Trait
{
    use Properties_Trait {
        initializeProperties as trait_initializeProperties;
        getPropertyDefault as trait_getPropertyDefault;
    }

    /**
     * @var Properties_Interface|null Dynamische Properties werden ggf von diesem Object
     * (z.B. dem persistenten Note Item) geliefert.
     */
    private $propertiesExtender = null;

    final private function initializeProperties(array $properties = array()) : self {
        $this->propertiesExtender = $this;
        return $this->trait_initializeProperties($properties);
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
    final public function getPropertyDefault(string $key, $default = '', bool $defaultOnEmpty = false) {
        $result = $this->trait_getPropertyDefault($key, 'unDEfineeD');
        $result = ($result == 'unDEfineeD')
            ? $this->getPropertyExtended($key, $default)
            : $result;
        return $defaultOnEmpty ? $this->defaultOnEmpty($result, $default) : $result;
    }

    /**
     * Returns the Property $key of my propertiesExtender object.
     *
     * @param string $key
     * @param string $default
     * @return mixed|string
     */
    final private function getPropertyExtended(string $key, $default) {
        return (is_null($this->propertiesExtender) || $this === $this->propertiesExtender)
            ? $default
            : $this->propertiesExtender->getPropertyDefault($key, $default);
    }

    /**
     * @param Properties_Interface|null $propertiesExtender
     *@see PropertiesExtended_Interface::setPropertiesExtender().
     */
    final public function setPropertiesExtender(?Properties_Interface $propertiesExtender) : void {
        $this->propertiesExtender = $propertiesExtender;
    }
}