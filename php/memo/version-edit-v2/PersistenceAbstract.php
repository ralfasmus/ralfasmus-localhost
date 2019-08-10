<?php
/**
 * Alle Funktionen zum persistenten Laden und Speichern von Notes.
 * Class Persistence
 */
class Persistence
{
    /**
     * @var string Status der betrachteten Note Instanzen: active|backup|deleted|archive
     */
    private $persistanceStatus = "";
    private const PERSISTANCE_STATUS_ACTIVE = 'active';
    private const PERSISTANCE_STATUS_BACKUP = 'backup';
    private const PERSISTANCE_STATUS_DELETED = 'deleted';

    public function __construct(string $persistanceStatus)
    {
        assert(in_array($persistanceStatus, array(self::PERSISTANCE_STATUS_ACTIVE, self::PERSISTANCE_STATUS_BACKUP, self::PERSISTANCE_STATUS_DELETED)));
        $this->persistanceStatus = $persistanceStatus;
    }

    /**
     * @var array Cache fuer die geladenen Note Instanzen.
     */
    private $notesCache = array();
    /**
     * @var bool true, wenn Persistence::notesCache valide ist.
     */
    private $notesCacheIsValid = false;

    /**
     * @return string
     */
    static private function getDataFilenameBase() : string {
        assert(defined('ROOT_DIR'), 'PHP Konstante ROOT_DIR ist nicht definiert.');
        assert(defined('APPLICATION_NAME'), 'PHP Konstante APPLICATION_NAME ist nicht definiert.');
        return ROOT_DIR . "/data/memo/" . APPLICATION_NAME;
    }

    private function getPersistanceStatus() {
        return $this->persistanceStatus;
    }

    /**
     * Laedt alle Notes in den Cache, die dem status des requests entsprechen und mit den im Filter angegebenen views matchen.
     * @param string $filterViews
     * @return array
     * @throws Throwable
     */
    function loadNotes(string $filterViews): array
    {
        if (!$this->notesCacheIsValid) {
            $filenameBase = Conf::get("DATA_FILE_NAME_BASE");
            foreach (glob($filenameBase . "/" . $this->getPersistanceStatus()) as $filename) {
                $note = self::loadNoteByFilename($filename);
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
                    $sortString .= $note->getProperty($prop, "") . " ...";
                }
            } else {
                $sortString = $note->getProperty($sortProperty, "");
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
    static function loadNoteByFilename(string $filename): Note
    {
        $note = NULL;
        try {
            $note = self::instantiateNoteFromString(file_get_contents($filename));
        } catch (Throwable $throwable) {
            Log::error("Kann Note-Datei $filename nicht finden.");
            throw $throwable;
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
    public static function loadNoteById(string $id): ?Note
    {
        if ($id == "" || is_null($id)) {
            return NULL;
        }
        $filename = self::getPathAndFilename($id);
        if (!file_exists($filename)) {
            return NULL;
        }
        return self::loadNoteByFilename($filename);
    }

    /**
     * @param string $noteString
     * @return Note
     */
    private static function instantiateNoteFromString(string $noteString): Note
    {
        $properties = json_decode($noteString, true);
        $note = new Note($properties["id"]);
        $note->setProperties($properties);
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
        return Conf::get("DATA_FILE_NAME_BASE") . "/" . $this->getPersistanceStatus() . "/$id";
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
    public function loadOrCreateNoteAndUpdate(string $id, Properties_Interface $notePropertiesFromRequest): Note
    {
        $note = self::loadNoteById($id);
        if (is_null($note)) {
            $note = new Note($id);
        }
        foreach ($notePropertiesFromRequest->getProperties() as $name => $value) {
            $note->setProperty($value, $name);
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
        $props = $note->getProperties();
        file_put_contents("${filename}", json_encode($props, JSON_HEX_QUOT | JSON_HEX_TAG));
        Log::debug("Saved Note to $filename");
    }

    /**
     * Speichert eine vollstaendige Instanz.
     * @param Note $note
     */
    public function noteSave(Note $note, Request $request)
    {
        $filename = $this->getPathAndFilename($note->getId());
        $this->noteSaveToFile($note, $filename);
        $filename = self::getPathAndFilename($note->getId(), "backup");
        $filename .= $request->getProperty('backupextension', '_backup_zeit_unbekannt', true);
        self::noteSaveToFile($note, $filename);
    }

    /**
     * Loescht eine vollstaendige Instanz.
     * @param Note $note
     * @param Request $request
     * @throws Throwable
     */
    public function noteDelete(Note $note, Request $request)
    {
        // Note nach deleted kopieren
        $filename = self::getPathAndFilename($note->getId(), "deleted");
        self::noteSaveToFile($note, $filename);
        // Note loeschen
        try {
            $filename = self::getPathAndFilename($note->getId(), $request->getRequestStatus());
            unlink($filename);
        } catch (Throwable $throwable) {
            Log::error("Kann Datei nicht loeschen.");
            throw $throwable;
        }
    }

    /**
     * Erstellt Backup fuer eine active oder deleted oder backup Instanz.
     * @param Note $note
     * @param Request $request
     */
    public static function noteBackup(Note $note, Request $request)
    {
        $filename = self::getPathAndFilename($note->getId() . time(), "backup");
        self::noteSaveToFile($note, $filename);
    }

}
