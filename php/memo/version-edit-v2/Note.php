<?php
/**
 * Ein Note ist ein persistenter Container für Daten. Das kann auch eine Note sein, die Config-Daten fuer den Request/die
 * Anwendung enthaelt.
 * Class Note
 */
abstract class Note implements Note_Interface, Properties_Interface
{
    use Note_Trait { getDynamicProperty as private trait_getDynamicProperty; }

    final public function getDynamicProperty(string $key)
    {
        $special = $this->getDynamicPropertySpecial($key);
        return ($special == 'unDEfineeD') ? $this->trait_getDynamicProperty($key) : $special;
    }

    abstract protected function getDynamicPropertySpecial(string $key);

    /**
     * Property, die die eineindeutige ID der Note-Instanz enthaelt.
     */
    protected const NOTE_PROPERTY_ID = 'id';
    /**
     * Property, die die Art-Werte der Note-Instanz durch Leerzeichen getrennt enthaelt. Kann leer sein.
     */
    protected const NOTE_PROPERTY_ART = 'art';
    /**
     * Property, die den Wert der Property "Name" der Note enthaelt. Kann leer sein.
     */
    public const NOTE_PROPERTY_NAME = 'name';
    /**
     * Moegliche views/templates zum Anzeigen eines Notes:
     */
    protected const PROPERTY_POSSIBLE_VIEWS = "possible-views";
    /**
     * Property einer Instanz aus der @see Note::getArtsList(), die den Wert fuer art enthaelt.
     */
    protected const ART_LIST_PROPERTY_ART = 'art';
    /**
     * View Name fuer Instanzen der @see Note::getArtsList().
     */
    protected const ART_LIST_PROPERTY_VIEW_DEFAULT = 'art';
    /**
     * Persistent gesetzter primaerer view zum Anzeigen eines Note Objekts
     */
    public const PROPERTY_VIEW = "view";
    /**
     * Default view/template zum Anzeigen eines Note Objekts, falls dieses keine oder eine leere ("") Property "view" hat:
     */
    public const PROPERTY_VIEW_DEFAULT = "default";
}
