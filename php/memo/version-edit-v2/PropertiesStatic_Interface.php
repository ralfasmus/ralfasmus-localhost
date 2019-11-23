<?php

/**
 * Interface PropertiesStatic_Interface
 */
interface PropertiesStatic_Interface extends Properties_Interface
{
    /**
     * Liefert die Properties als iterierbares array name => wert.
     * @return array
     */
    public function getProperties() : array;
}
