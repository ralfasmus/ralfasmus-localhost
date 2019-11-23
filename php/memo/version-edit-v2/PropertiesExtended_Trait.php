<?php
/**
 * Implementation von @see PropertiesExtended_Interface
 *
 * trait PropertiesExtended_Trait
 */
trait PropertiesExtended_Trait
{
    use Properties_Trait {
        initializePropertiesTrait as trait_initializePropertiesTrait;
        getPropertyDefault as trait_getPropertyDefault;
    }

    /**
     * @var Properties_Interface|null Dynamische Properties werden ggf von einem Item (dem persistenten Note Item) geliefert.
     */
    private $propertiesExtender = null;

    final private function initializePropertiesTrait() : self {
        $this->propertiesExtender = $this;
        return $this->trait_initializePropertiesTrait();
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

    final private function getPropertyExtended(string $key, $default = '') {
        return (is_null($this->propertiesExtender) || $this === $this->propertiesExtender)
            ? $default
            : $this->propertiesExtender->getPropertyDefault($key, $default);
    }

    /**
     * @param Properties_Interface|null $dynamicPropertyItem
     *@see PropertiesExtendet_Interface::setPropertiesExtender().
     */
    final public function setPropertiesExtender(?Properties_Interface $propertiesExtender) : void {
        $this->propertiesExtender = $propertiesExtender;
    }

    /**
     * The Properties container itself can access its non-dynamic, non-extended set of properties. By doing so,
     * one should carefully remember that there might be these other dynamic + extended properties which are not
     * included in the result of this getPropertiesStatic() method.
     *
     * @return array set of the objects non-dynamic, non-extended properties.
     */
    final private function getPropertiesStatic(): array
    {
        return $this->properties;
    }

}