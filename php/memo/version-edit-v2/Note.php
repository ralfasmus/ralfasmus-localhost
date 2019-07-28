<?php
/**
 * Ein Note ist ein persistenter Container für Daten. Das kann auch eine Note sein, die Config-Daten fuer den Request/die
 * Anwendung enthaelt.
 * Class Note
 */
class Note implements Properties_Interface
{
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
    private const ART_LIST_PROPERTY_ART = 'art-value';
    /**
     * View Name fuer Instanzen der @see Note::getArtsList().
     */
    private const ART_LIST_PROPERTY_VIEW_DEFAULT = 'art';

    /**
     * @var Properties|null Properties dieser Note Instanz
     */
    private $properties = NULL;

    /**
     * Note constructor.
     * @param string $id
     */
    public function __construct(string $id)
    {
        assert(!empty($id), 'Kann Note nicht erzeugen, weil $id Parameter ungueltig ist');
        $this->properties = new Properties();
        $this->setProperty($id, self::NOTE_PROPERTY_ID);
        $this->setProperty(Properties_Interface::PROPERTY_PLACE_HOLDER_INDICATOR_NOTE, Properties_Interface::PROPERTY_PLACE_HOLDER_INDICATOR);
    }

    /**
     * @see Note::NOTE_PROPERTY_ID
     * @return string
     */
    public function getId(): string
    {
        return $this->getProperty(self::NOTE_PROPERTY_ID);
    }

    /**
     * @see Note::PROPERTY_VIEW
     * @return string
     */
    public function getView()
    {
        return $this->getProperty(self::PROPERTY_VIEW, self::PROPERTY_VIEW_DEFAULT, true);
    }

    /**
     * Liefert alle Views des Notes als Komma separierter String mit mindestens einem , an Beginn und Ende:
     * ,,v1,v2,
     * @return string
     */
    private function getAllViews()
    {
        return "," . $this->getProperty(self::PROPERTY_POSSIBLE_VIEWS, "") . "," . $this->getView() . ",";
    }

    /**
     * @param string $filterViews
     * @return bool
     */
    public function hasViewsMatchingFilterViews(string $filterViews): bool
    {
        // $filterViews sind die im Listen Filter-Feld "Views" mit " " (und) getrennt angegebenen Views
        $noteViews = $this->getAllViews();
        $foundAll = true;
        foreach (explode(" ", $filterViews) as $filterView) {
            $foundAll = $foundAll && stripos($noteViews, ",$filterView,") !== false;
        }
        return $foundAll;
    }


    // ############# INTERFACE PROPERTIES #################################

    /**
     * @see Properties_Interface::getProperty()
     */
    public function getProperty(string $key, $default = "exception", bool $defaultOnEmpty = false)
    {
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

            case "data-tooltip-html" :
                $text = isset($properties["text"]) ? $properties["text"] : "";
                $result = str_replace('"', "'", $text);
                break;

            default :
                $result = $this->properties->getProperty($key, $default, $defaultOnEmpty);
                break;
        }

        return $result;
    }

    /**
     * @see Properties_Interface::setProperties()
     */
    public function setProperties(array $properties)
    {
        $this->properties->setProperties($properties);
    }

    /**
     * @see Properties_Interface::getProperties()
     */
    public function getProperties(): array
    {
        return $this->properties->getProperties();
    }

    /**
     * @see Properties_Interface::setProperty()
     */
    public function setProperty($value, string $key)
    {
        $this->properties->setProperty($value, $key);
    }

    /**
     * @see Properties_Interface::getDecodedProperty()
     */
    public function getDecodedProperty(string $key, $default = "exception"): string
    {
        return $this->properties->getDecodedProperty($key, $default);
    }

    /**
     * Liefert die alphabetisch sortierte Liste der "art" Werte aus den uebergebenen Note-Instanzen. Jede art ist nur
     * einmal enthalten. Es werden fuer arts der Form .a.b.c zusaetzlich die arts .a und .a.b aufgenommen.
     * Die Liste ist ein Array von @see Properties_Interface mit den Werten "name" = art und "view" = "art".
     *
     * @param array $notes
     * @return array Liste von Properties_Interface
     */
    static public function getArtsList(array $notes): array
    {
        $arts = array();
        foreach ($notes as $note) {
            $art = $note->getProperty(self::NOTE_PROPERTY_ART, "");
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
                $artProp->setProperty(self::ART_LIST_PROPERTY_VIEW_DEFAULT, Properties_Interface::PROPERTY_VIEW);
                $artProperties[] = $artProp;
            }
        }
        return $artProperties;
    }


}
