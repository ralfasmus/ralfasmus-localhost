<?php
/**
 * Zugriff auf ein Set von Properties der Form name => wert und von evtl. dynamisch berechneten Properties.
 * Interface Properties_Interface
 */
interface PropertiesExtended_Interface extends Properties_Interface
{

    /**
     * Setzt das Item, das ggf. dynamisch berechnete Properties liefert.
     * @param Properties_Interface|null $propertiesProvider
     */
    public function setPropertiesExtender(?Properties_Interface $propertiesExtender) : void;
}
