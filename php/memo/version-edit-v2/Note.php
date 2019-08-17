<?php
/**
 * Ein Note ist ein persistenter Container für Daten. Das kann auch eine Note sein, die Config-Daten fuer den Request/die
 * Anwendung enthaelt.
 * Class Note
 */
abstract class Note implements Properties_Interface
{
    use Properties_Trait { getPropertyDefault as private trait_getPropertyDefault; }

    /**
     * Property, die die eineindeutige ID der Note-Instanz enthaelt.
     */
    private const NOTE_PROPERTY_ID = 'id';
    /**
     * Property, die die Art-Werte der Note-Instanz durch Leerzeichen getrennt enthaelt. Kann leer sein.
     */
    private const NOTE_PROPERTY_ART = 'art';
    /**
     * Property, die den Wert der Property "Name" der Note enthaelt. Kann leer sein.
     */
    public const NOTE_PROPERTY_NAME = 'name';
    /**
     * Moegliche views/templates zum Anzeigen eines Notes:
     */
    private const PROPERTY_POSSIBLE_VIEWS = "possible-views";
    /**
     * Property einer Instanz aus der @see Note::getArtsList(), die den Wert fuer art enthaelt.
     */
    private const ART_LIST_PROPERTY_ART = 'art';
    /**
     * View Name fuer Instanzen der @see Note::getArtsList().
     */
    private const ART_LIST_PROPERTY_VIEW_DEFAULT = '-art';
    /**
     * Persistent gesetzter primaerer view zum Anzeigen eines Note Objekts
     */
    public const PROPERTY_VIEW = "view";
    /**
     * Default view/template zum Anzeigen eines Note Objekts, falls dieses keine oder eine leere ("") Property "view" hat:
     */
    public const PROPERTY_VIEW_DEFAULT = "default";

    /**
     * Note constructor.
     * @param string $id
     */
    final private function __construct(string $id)
    {
        assert(!empty($id), 'Kann Note nicht erzeugen, weil $id Parameter ungueltig ist');
        /**
         * Default Initialisierung:
         */
        $this->setProperty($id, self::NOTE_PROPERTY_ID);
        $this->setProperty(static::class, 'view');
        $this->initialize();
        Log::logInstanceCreated($this);
    }

    final static public function createForView(string $id, string $view) : Note {
        return new $view($id);
    }

    /**
     * Child Klassen implementieren hier die nach dem Konstruktor der Klasse Note ausgefuehrte Initialisierung.
     */
    abstract protected function initialize() : void ;

    /**
     * @see Note::NOTE_PROPERTY_ID
     * @return string
     */
    final public function getId(): string
    {
        return $this->getPropertyMandatory(self::NOTE_PROPERTY_ID, true, 'Eine Note-Instanz muss eine Property :id: haben!');
    }


    /**
     * Liefert alle Views des Notes als Komma separierter String mit mindestens einem , an Beginn und Ende:
     * ,,v1,v2,
     * @return string
     */
    final private function getAllViews()
    {
        return "," . $this->getPropertyDefault(self::PROPERTY_POSSIBLE_VIEWS) . "," . $this->getPropertyDefault("view", 'NoteDefault', true) . ",";
    }

    /**
     * @param string $filterViews
     * @return bool
     */
    final public function hasViewsMatchingFilterViews(string $filterViews): bool
    {
        // $filterViews sind die im Listen Filter-Feld "Views" mit " " (und) getrennt angegebenen Views
        $noteViews = $this->getAllViews();
        $foundAll = true;
        foreach (explode(" ", $filterViews) as $filterView) {
            $foundAll = $foundAll && stripos($noteViews, ",$filterView,") !== false;
        }
        return $foundAll;
    }

    /**
     * Liefert die alphabetisch sortierte Liste der "art" Werte aus den uebergebenen Note-Instanzen. Jede art ist nur
     * einmal enthalten. Es werden fuer arts der Form .a.b.c zusaetzlich die arts .a und .a.b aufgenommen.
     * Die Liste ist ein Array von @see Properties_Interface mit den Werten "name" = art und "view" = "art".
     *
     * @param array $notes
     * @return array Liste von Properties_Interface
     */
    final static public function getArtsList(array $notes): array
    {
        $arts = array();
        foreach ($notes as $note) {
            $art = $note->getPropertyDefault(self::NOTE_PROPERTY_ART);
            $artArray = explode(" ", $art);
            foreach ($artArray as $art) {
                $arts[trim($art, " ")] = "";
                // jetzt die Art-Teile als weitere "Haupt-" Arts erzeugen:
                $subarts = explode('.', $art);
                foreach ($subarts as $subart) {
                    if ($subart != "") {
                        $arts[".$subart"] = "";
                    }
                }
            }
        }
        ksort($arts);
        $artProperties = array();
        foreach (array_keys($arts) as $art) {
            if ($art != "") {
                $artProp = new Properties();
                $artProp->setProperty($art, self::ART_LIST_PROPERTY_ART);
                $artProp->setProperty(self::ART_LIST_PROPERTY_VIEW_DEFAULT, self::PROPERTY_VIEW);
                $artProperties[] = $artProp;
            }
        }
        return $artProperties;
    }


    /**
     * @see Properties_Trait::getDynamicProperty()
     *
     * @param string $key
     * @return |null
     */
    protected function getDynamicProperty(string $key) {
        $result = null;
        switch ($key) {
            case "data-filter-any" :
                $result = "";
                foreach ($this->getProperties() as $name => $value) {
                    $result .= "$value ";
                }
                break;
            case "data-filter-views" :
                $result = $this->getAllViews();
                break;
        }
        return $result;
    }
}
