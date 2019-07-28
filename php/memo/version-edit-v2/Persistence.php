<?php
/**
 * Alle Funktionen zum persistenten Laden und Speichern von Notes.
 * Class Persistence
 */
class Persistence
{

    /**
     * @var array Cache fuer alle geladenen Note Instanzen.
     */
    static private $notesCache = array();
    /**
     * @var bool true, wenn Persistence::notesCache valide ist.
     */
    static private $notesCacheIsValid = false;

    /**
     * @return string
     */
    static private function getDataFilenameBase() : string {
        assert(defined('ROOT_DIR'), 'PHP Konstante ROOT_DIR ist nicht definiert.');
        assert(defined('APPLICATION_NAME'), 'PHP Konstante APPLICATION_NAME ist nicht definiert.');
        return ROOT_DIR . "/data/memo/" . APPLICATION_NAME;
    }

    /**
     * Laedt alle Notes in den Cache, die dem status des requests entsprechen und mit den im Filter angegebenen views matchen.
     * @param string $filterViews
     * @param string $status
     * @return array
     * @throws Throwable
     */
    private static function loadNotes(string $filterViews, string $status): array
    {
        if (!self::$notesCacheIsValid) {
            $filenameBase = Conf::get("DATA_FILE_NAME_BASE");
            foreach (glob($filenameBase . "/$status/*") as $filename) {
                $note = self::loadNoteByFilename($filename);
                assert(!is_null($note), "Note-Instanz aus Datei $filename ist nach dem Laden NULL.");
                if ($note->hasViewsMatchingFilterViews($filterViews)) {
                    self::$notesCache[] = $note;
                }
            }
            self::$notesCacheIsValid = true;
        }
        return self::$notesCache;
    }

    /**
     * Liefert alle Instanzen sortiert.
     * @param string $filterViews
     * @param string $sortProperty
     * @param bool $descending
     * @param string $status
     * @return array
     * @throws Throwable
     */
    public static function getNotes(string $filterViews, string $sortProperty, bool $descending, string $status): array
    {

        $notes = self::loadNotes($filterViews, $status);

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
    static private function loadNoteByFilename(string $filename): Note
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
     * @param string $status
     * @return Note|null
     * @throws Throwable
     */
    public static function loadNoteById(string $id, string $status): ?Note
    {
        if ($id == "" || is_null($id)) {
            return NULL;
        }
        $filename = self::getPathAndFilename($id, $status);
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
     * @param string $status
     * @return string
     */
    static public function getPathAndFilename(string $id, string $status): string
    {
        return Conf::get("DATA_FILE_NAME_BASE") . "/$status/$id";
    }

    /**
     * Erstellt oder laedt einen Note Instanz und aktualisiert sie aus den $properties, die aus dem Request kommen.
     * Das kann auch eine Config Note sein.
     *
     * @param string $id
     * @param Request $request
     * @param Properties_Interface $notePropertiesFromRequest
     * @return Note
     * @throws Throwable
     */
    static public function loadOrCreateNoteAndUpdate(string $id, Request $request, Properties_Interface $notePropertiesFromRequest): Note
    {
        $note = self::loadNoteById($id, $request->getRequestStatus());
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
    public static function updateNoteFromRequest(Note $note, Properties_Interface $propertiesNotePersistent)
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
    public static function noteSaveToFile(Note $note, string $filename)
    {
        $props = $note->getProperties();
        file_put_contents("${filename}", json_encode($props, JSON_HEX_QUOT | JSON_HEX_TAG));
        Log::debug("Saved Note to $filename");
    }

    /**
     * Speichert eine vollstaendige Instanz.
     * @param Note $note
     * @param Request $request
     */
    public static function noteSave(Note $note, Request $request)
    {
        $filename = self::getPathAndFilename($note->getId(), $request->getRequestStatus());
        self::noteSaveToFile($note, $filename);
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
    public static function noteDelete(Note $note, Request $request)
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
