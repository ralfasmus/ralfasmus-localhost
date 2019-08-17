<?php
/**
 * Implementation von @see Properties_Interface
 * Class Properties
 */
Class Properties implements Properties_Interface
{
    use Properties_Trait;
    /**
     * Properties constructor.
     * @param array $properties
     */
    public function __construct(array $properties = array())
    {
        $this->setProperties($properties);
    }

    /**
     * @see Properties_Trait::getDynamicProperty()
     *
     * @param string $key
     * @return |null
     */
    protected function getDynamicProperty(string $key) {
        // Es gibt hier keine dynamisch berechneten Properties.
        return null;
    }
}
