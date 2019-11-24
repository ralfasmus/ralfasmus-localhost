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
}