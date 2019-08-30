<?php
/**
 * Alle Funktionen zum persistenten Laden und Speichern von Notes.
 *
 * Class Persistence_Trait
 */
trait Persistence_Trait
{

    /**
     * @var array Cache fuer die geladenen Note Instanzen.
     */
    private $notesCache = array();

    /**
     * @var bool true, wenn Persistence::notesCache valide ist.
     */
    private $notesCacheIsValid = false;

    /**
     * Basis fuer Pfad zu Daten.
     * @return string
     */
    final private function getDataFilenameBase() : string {
        assert(defined('ROOT_DIR'), 'PHP Konstante ROOT_DIR ist nicht definiert.');
        assert(defined('APPLICATION_NAME'), 'PHP Konstante APPLICATION_NAME ist nicht definiert.');
        return ROOT_DIR . "/data/memo/" . APPLICATION_NAME;
    }


    /**
     * Liefert das Pfadfragment (status) fuer die Klasse, die dieses Trait konkret einsetzt.
     *
     * @return string PersistenceActive|PersistenceBackup|PersistenceDeleted
     */
    final private function getPersistenceStatus() {
        return __CLASS__;
    }
    
    /**
     * Laedt alle Notes in den Cache, die dem status des requests entsprechen und mit den im Filter angegebenen views matchen.
     *
     * @param string $filterViews Wert aus dem Eingabefeld auf der Noteliste-Seite.
     * @return array
     * @throws Throwable
     */
    final private function loadNotes(string $filterViews): array
    {
        if (!$this->notesCacheIsValid) {
            $filenameBase = $this->getDataFilenameBase();
            foreach (glob($filenameBase . "/" . $this->getPersistenceStatus() . '/*') as $filename) {
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
    final public function getNotes(string $filterViews, string $sortProperty, bool $descending): array
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
    final private function loadNoteByFilename(string $filename): Note
    {
        assert(!is_null($filename) && is_string($filename) && ($filename != ''), 'Kann Note Instance mit leerem/null filename nicht laden.');
        $note = NULL;
        try {
            $noteString = file_get_contents($filename);
            Log::debug('Loading File: ' . $filename);
            assert($noteString != '', "Note-Instanz aus Datei $filename ist nach dem Laden leer.");
            $note = $this->instantiateNoteFromString($noteString);
            Log::debug('Loaded ' . Log::objectString($note) . ' from file: ' . $filename);
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
    final private function instantiateNoteFromString(string $noteString): Note
    {
        try {
            $properties = json_decode($noteString, true);
            $view = isset($properties['view']) ? $properties['view'] : 'NoteDefault';
            $note = Note::createForView($properties["id"], $view);
            $note->setProperties($properties);
        } catch (Throwable $throwable) {
            MyThrowable::handleThrowable($throwable, 'Kann note nicht instantiieren durch json_decode von diesem String: ' . $noteString, true);
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
    final public function getPathAndFilename(string $id): string
    {
        assert(!is_null($id) && is_string($id) && ($id != ''), 'Kann Pfad/Dateiname fuer Note zu leerer id nicht bestimmen.');
        return $this->getDataFilenameBase() . "/" . $this->getPersistenceStatus() . "/$id";
    }

    /**
     * Erstellt oder laedt einen Note Instanz und aktualisiert sie aus den $properties, die aus dem Request kommen.
     * Das kann auch eine Config Note sein.
     *
     * @param string $id
     * @param string $view
     * @param Properties_Interface $notePropertiesFromRequest
     * @return Note
     * @throws Throwable
     */
    final public function loadOrCreateNote(string $id, string $view): Note
    {
        assert(!is_null($view) && $view != '', '$view Parameter ist leer oder null.');
        $note = $this->loadNoteById($id);
        if (is_null($note)) {
            $note = Note::createForView($id, $view);
            $this->noteSave($note);
        }
        return $note;
    }

    /**
     * @param Note $note
     * @param Properties_Interface $propertiesNotePersistent
     * @return Note
     */
    final public function updateNoteFromRequest(Note $note, Properties_Interface $propertiesNotePersistent)
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
    final public function noteSaveToFile(Note $note, string $filename)
    {
        assert(!is_null($filename) && is_string($filename) && ($filename != ''), 'Kann Note Instance mit leerem/null filename nicht speichern.');
        $props = $note->getProperties();
        file_put_contents("${filename}", json_encode($props, JSON_HEX_QUOT | JSON_HEX_TAG));
        Log::debug('Writing ' . Log::objectString($note) . ' to file: ' . $filename);
    }

    /**
     * Speichert eine vollstaendige Instanz unter meinem Status
     * @param Note $note
     */
    final public function noteSave(Note $note)
    {
        $filename = $this->getPathAndFilename($note->getId());
        $this->noteSaveToFile($note, $filename);
    }

    /**
     * Loescht eine vollstaendige Instanz.
     * @param Note $note
     * @throws Throwable
     */
    final public function noteDelete(Note $note)
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
     * Erstellt Backup fuer eine PersistenceActive oder PersistenceDeleted oder PersistenceBackup Instanz.
     * @param Note $note
     */
    final public function noteBackup(Note $note)
    {
    }

}
