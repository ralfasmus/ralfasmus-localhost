<?php
/**
 * Implementation von @see Properties_Interface
 *
 * trait PropertiesDynamic_Trait
 */
trait PropertiesDynamic_Trait
{
    use Properties_Trait {
        getPropertyDefault as trait_getPropertyDefault;
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
        $result = $this->trait_getPropertyDefault($key, 'unDEfineeD');
        $result = ($result == 'unDEfineeD')
            ? $this->getPropertyDynamic($key, $default)
            : $result;
        return $defaultOnEmpty ? $this->defaultOnEmpty($result, $default) : $result;
    }

    /**
     * Dynamisch erzeugte Properties werden von diesem Trait ueber die Standard Methoden wie
     * @see Properties_Trait::getPropertiesDefault() oder @see Properties_Trait::getPropertyMandatory() geliefert,
     * muessen aber in der Trait-nutzenden Klasse explizit definiert werden. Die Default-Implementation ist:
     * return unDEfineeD;
     *
     * @return mixed, unDEfineeD fuer alle Properties, die nicht dynamisch definiert sind.
     */
    abstract protected function getPropertyDynamic(string $key, $default);

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