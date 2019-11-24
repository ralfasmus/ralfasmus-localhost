<?php
/**
 * Interface PropertiesExtended_Interface erlaubt zusaetzlich zu den Methoden des Properties_Interface auch einen
 * propertiesExtender zu setzen. Bei der Abfrage von Properties wird dann dieser propertiesExtender implizit mit
 * einbezogen, wenn das angefragte Objekt selbst keinen Wert fuer eine Property liefert.
 */
interface PropertiesExtended_Interface extends Properties_Interface
{

    /**
     * @param Properties_Interface|null $propertiesProvider
     */
    public function setPropertiesExtender(?Properties_Interface $propertiesExtender) : void;
}
