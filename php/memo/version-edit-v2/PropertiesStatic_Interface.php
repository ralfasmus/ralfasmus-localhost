<?php

/**
 * Interface PropertiesStatic_Interface erlaubt zusaetzlich zu den Methoden des Properties_Interface auch den Zugriff
 * auf das Set der statisch definierten Properties, da es keine weiteren (dynamisch oder via propertiesExtender
 * definierten) Properties gibt.
 */
interface PropertiesStatic_Interface extends Properties_Interface
{
    /**
     * Liefert alle Properties als iterierbares array name => wert.
     * @return array
     */
    public function getProperties() : array;
}
