<?php
/**
 * class PropertiesStatic Instanzen dieser Klasse dienen ausschliesslich zum Ablegen und Auslesen von Properties.
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
        $this->initializeProperties($properties);
    }
}
