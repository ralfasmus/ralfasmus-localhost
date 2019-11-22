<?php

/**
 * Trait Note_Trait
 */
Trait Note_Trait
{
    use Properties_Trait { getPropertyDefault as private trait_getPropertyDefault; }

    static public function createInstance(Properties_Interface $properties) : Note_Interface {
        $instance = new static;
        $instance->initializeNoteTrait($properties);
        return $instance;
    }

    /**
     * Initialisiert die Eigenschaften dieses Trait.
     *
     * @param Properties_Interface $properties
     * @return $this
     */
    private function initializeNoteTrait(Properties_Interface $properties) : void {
        $this->initializePropertiesTrait();
        $this->setProperties($properties->getProperties());
        $this->setProperty(static::class, 'view');
    }

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
        $result = 'unDEfineeD';
        switch ($key) {
            // zur Verwendung im data-text Attribute der DUPlicate Url
            case "data-text" :
                $result .= $this->getValueDoubleQuote2singleQuote($this->getPropertyDefault('text', ''));
                break;
            // zur Verwendung in einem Filter-Input Feld im Formular
            case "data-filter-any" :
                $result = "";
                foreach ($this->getProperties() as $name => $value) {
                    if(!is_array($value)) {
                        $result .= strtolower($this->getValueDoubleQuote2singleQuote($value, '')) . ' ';//htmlspecialchars("$value "));
                    }
                }
                break;
            // zur Verwendung in einem Filter-Input Feld im Formular
            case "data-filter-views" :
                $result = strtolower($this->getDynamicProperty('all-views'));
                break;
            // zur Verwendung u.a. beim Laden von Notes.
            case "all-views" :
                $result = $this->getAllViews();
                break;
            // Downloadlinks auf die Attachment Documents der Note
            case "fileslinked" :
                $filenames = explode(' ', $this->getPropertyDefault('files', ''));
                $linkTags = array();
                foreach($filenames as $filename) {
                    if($filename != '') {
                        list($timestamp, $name) = explode('_', $filename);
                        $downloadHrefBase = "/memo/priv/" . APPLICATION_NAME . '/download';
                        $linkTags[] = '<a class="dvz-note-download" href="' . "$downloadHrefBase/$filename" . '" target="_blank">' . "$name ($timestamp)</a>";
                    }
                }
                $result .= implode('<br/>', $linkTags);
                break;
        }
        return $result;
    }
}
