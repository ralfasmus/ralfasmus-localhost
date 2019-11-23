<?php
/**
 * Implementation von @see Properties_Interface
 * Class PropertiesStatic
 */
class PropertiesStatic implements PropertiesStatic_Interface
{
    use PropertiesStatic_Trait;
    /**
     * Properties constructor.
     * @param array $properties
     */
    public function __construct(array $properties = array())
    {
        assert(is_array($properties));
        $this->initializePropertiesTrait()->setProperties($properties);
    }
}
