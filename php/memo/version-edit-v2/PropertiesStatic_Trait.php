<?php
/**
 * Implementation von @see Properties_Interface
 *
 * trait PropertiesStatic_Trait
 */
trait PropertiesStatic_Trait
{
    use Properties_Trait;
    /**
     * @return array
     * @see Properties_Interface::getProperties()
     */
    final public function getProperties(): array
    {
        return $this->properties;
    }
}