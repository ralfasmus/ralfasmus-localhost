<?php

/**
 * Interface PropertiesDynamic_Interface erweitert das Properties_Interface nicht. Es dient nur der Klarstellung, das
 * an bestimmten Stellen eine das Properties_Interface erfuellende Instanz vorkommt, die intern anders als eine
 * PropertiesStatic Instanz funktioniert. Dies wird durch Verwendung des PropertiesDynamic_Trait sichergestellt.
 * Die Schnittstelle aendert sich deshalb jedoch nicht.
 */
interface PropertiesDynamic_Interface extends Properties_Interface
{
}
