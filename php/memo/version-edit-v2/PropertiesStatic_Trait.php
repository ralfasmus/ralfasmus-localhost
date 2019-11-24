<?php
/**
 * trait PropertiesStatic_Trait Implementation der Funktionalitaet fuer eine Klasse, die statische Properties hat
 * (und keine dynamischen oder ueber propertiesExtender definierten).
 */
trait PropertiesStatic_Trait
{
    /**
     * Um das PropertiesStatic_Interface zu erfuellen, wird getProperties() veroeffentlicht.
     */
    use Properties_Trait { getProperties as public; }
}