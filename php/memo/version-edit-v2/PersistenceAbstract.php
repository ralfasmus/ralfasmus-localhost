<?php
/**
 * Alle Funktionen zum persistenten Laden und Speichern von Notes.
 *
 * Class PersistenceAbstract
 */
abstract class PersistenceAbstract
{
    protected const PERSISTANCE_STATUS_ACTIVE = 'active';
    protected const PERSISTANCE_STATUS_BACKUP = 'backup';
    protected const PERSISTANCE_STATUS_DELETED = 'deleted';

    /**
     * @var array Cache fuer die geladenen Note Instanzen.
     */
    private $notesCache = array();
    /**
     * @var bool true, wenn Persistence::notesCache valide ist.
     */
    private $notesCacheIsValid = false;

    static private $singleInstances = array();

    /**
     * Fuer jede Subclasse gibt es nur eine SingleInstance, die hier im Class-Member gespeichert ist.
     * @return mixed
     */
    public static function getSingleInstance() {
        $className = static::class;
        if(!isset(self::$singleInstances[$className])) {
            self::$singleInstances[$className] = new $className;
        }
        return self::$singleInstances[$className];
    }

    /**
     * Basis fuer Pfad zu Daten.
     * @return string
     */
    private function getDataFilenameBase() : string {
        assert(defined('ROOT_DIR'), 'PHP Konstante ROOT_DIR ist nicht definiert.');
        assert(defined('APPLICATION_NAME'), 'PHP Konstante APPLICATION_NAME ist nicht definiert.');
        return ROOT_DIR . "/data/memo/" . APPLICATION_NAME;
    }

    /**
     * Liefert den Status active|backup|deleted dieser Persistance Instanz.
     * @return mixed
     */
    abstract protected function getPersistanceStatus();

    /**
     * Laedt alle Notes in den Cache, die dem status des requests entsprechen und mit den im Filter angegebenen views matchen.
     *
     * @param string $filterViews Wert aus dem Eingabefeld auf der Noteliste-Seite.
     * @return array
     * @throws Throwable
     */
    private function loadNotes(string $filterViews): array
    {
        if (!$this->notesCacheIsValid) {
            $filenameBase = $this->getDataFilenameBase();
            foreach (glob($filenameBase . "/" . $this->getPersistanceStatus() . '/*') as $filename) {
                $note = $this->loadNoteByFilename($filename);
                assert(!is_null($note), "Note-Instanz aus Datei $filename ist nach dem Laden NULL.");
                if ($note->hasViewsMatchingFilterViews($filterViews)) {
                    $this->notesCache[] = $note;
                }
            }
            $this->notesCacheIsValid = true;
        }
        return $this->notesCache;
    }

    /**
     * Liefert alle Instanzen sortiert.
     *
     * @param string $filterViews
     * @param string $sortProperty
     * @param bool $descending
     * @return array
     * @throws Throwable
     */
    public function getNotes(string $filterViews, string $sortProperty, bool $descending): array
    {

        $notes = $this->loadNotes($filterViews);

        $sortList = array();
        $noteList = array();

        foreach ($notes as $note) {
            if (is_array($sortProperty)) {
                $sortString = "";
                foreach ($sortProperty as $prop) {
                    $sortString .= $note->getPropertyDefault($prop) . " ...";
                }
            } else {
                $sortString = $note->getPropertyDefault($sortProperty);
            }
            $sortList[$note->getId()] = $sortString;
            $noteList[$note->getId()] = $note;
        }

        if ($descending) {
            arsort($sortList);
        } else {
            asort($sortList);
        }
        $result = array();
        foreach ($sortList as $id => $key) {
            $result[] = $noteList[$id];
        }
        return $result;
    }

    /**
     * Laedt eine Note-Instanz aus Datei $filename.
     *
     * @param string $filename
     * @return Note
     * @throws Throwable
     */
    public function loadNoteByFilename(string $filename): Note
    {
        assert(!is_null($filename) && is_string($filename) && ($filename != ''), 'Kann Note Instance mit leerem/null filename nicht laden.');
        $note = NULL;
        try {
            $noteString = file_get_contents($filename);
            assert($noteString != '', "Note-Instanz aus Datei $filename ist nach dem Laden leer.");
            $note = $this->instantiateNoteFromString($noteString);
        } catch (Throwable $throwable) {
            MyThrowable::handleThrowable($throwable,"Kann Note-Datei $filename nicht finden.");
        }
        assert(!is_null($note), "Note-Instanz aus Datei $filename ist nach dem Laden NULL.");

        return $note;
    }

    /**
     * Laedt ein im aktuellen Status gespeichertes Note. Liefert NULL wenn keins gefunden.
     * @param string $id
     * @return Note|null
     * @throws Throwable
     */
    public function loadNoteById(string $id): ?Note
    {
        if ($id == "" || is_null($id)) {
            return NULL;
        }
        $filename = $this->getPathAndFilename($id);
        if (!file_exists($filename)) {
            return NULL;
        }
        return $this->loadNoteByFilename($filename);
    }

    /**
     * @param string $noteString
     * @return Note
     */
    private function instantiateNoteFromString(string $noteString): Note
    {
        try {
            $properties = json_decode($noteString, true);
            $note = new Note($properties["id"]);
            $note->setProperties($properties);
        } catch (Throwable $throwable) {
            MyThrowable::handleThrowable($throwable, 'Kann note nicht instantiieren durch json_decode von diesem String: ' . $noteString);
        }
        return $note;
    }

    /**
     * Liefert Pfad und Dateiname zur Instanz, so dass sie daraus geladen
     * werden kann. Der Status ist der aktuelle Status aus dem Request oder der explizit angegebene.
     *
     * @param string $id
     * @return string
     */
    public function getPathAndFilename(string $id): string
    {
        assert(!is_null($id) && is_string($id) && ($id != ''), 'Kann Pfad/Dateiname fuer Note zu leerer id nicht bestimmen.');
        return $this->getDataFilenameBase() . "/" . $this->getPersistanceStatus() . "/$id";
    }

    /**
     * Erstellt oder laedt einen Note Instanz und aktualisiert sie aus den $properties, die aus dem Request kommen.
     * Das kann auch eine Config Note sein.
     *
     * @param string $id
     * @param Properties_Interface $notePropertiesFromRequest
     * @return Note
     * @throws Throwable
     */
    public function loadOrCreateNote(string $id): Note
    {
        $note = $this->loadNoteById($id);
        if (is_null($note)) {
            $note = new Note($id);
        }
        return $note;
    }

    /**
     * @param Note $note
     * @param Properties_Interface $propertiesNotePersistent
     * @return Note
     */
    public function updateNoteFromRequest(Note $note, Properties_Interface $propertiesNotePersistent)
    {
        foreach ($propertiesNotePersistent->getProperties() as $name => $value) {
            $note->setProperty($value, $name);
        }
        return $note;
    }

    /**
     * Speichert eine vollstaendige Instanz unter dem aktuellen Pfad.
     * @param Note $note
     * @param string $filename
     */
    public function noteSaveToFile(Note $note, string $filename)
    {
        assert(!is_null($filename) && is_string($filename) && ($filename != ''), 'Kann Note Instance mit leerem/null filename nicht speichern.');
        $props = $note->getProperties();
        file_put_contents("${filename}", json_encode($props, JSON_HEX_QUOT | JSON_HEX_TAG));
        Log::debug("Saved Note to $filename");
    }

    /**
     * Speichert eine vollstaendige Instanz.
     * @param Note $note
     */
    public function noteSave(Note $note)
    {
        $filename = $this->getPathAndFilename($note->getId());
        $this->noteSaveToFile($note, $filename);
    }

    /**
     * Loescht eine vollstaendige Instanz.
     * @param Note $note
     * @throws Throwable
     */
    public function noteDelete(Note $note)
    {
        // Note nach deleted kopieren
        PersistenceDeleted::getSingleInstance()->noteSave($note);
        // Note loeschen
        try {
            $filename = $this->getPathAndFilename($note->getId());
            unlink($filename);
        } catch (Throwable $throwable) {
            MyThrowable::handleThrowable($throwable,'Kann Datei nicht loeschen.');
        }
    }

    /**
     * Erstellt Backup fuer eine active oder deleted oder backup Instanz.
     * @param Note $note
     */
    public function noteBackup(Note $note)
    {
    }

}
