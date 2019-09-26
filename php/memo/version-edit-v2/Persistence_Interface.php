<?php
/**
 * Alle Funktionen zum persistenten Laden und Speichern von Notes.
 *
 * Class Persistence_Interface
 */
interface Persistence_Interface
{
    /**
     * Liefert alle Instanzen die dem Request Persistence Status entsprechen, gefiltert und sortiert entsprechend Parametern.
     *
     * @param array $filterLoadingPropertiesInclude  name => regex array von properties, die bei match im Ergebnis enthalten sind
     * @param array $filterLoadingPropertiesExclude  name => regex array von properties, die bei match NICHT im Ergebnis enthalten sind
     * @param string $sortProperty
     * @param bool $descending
     * @return array
     * @throws Throwable
     */
    public function getNotes(array $filterLoadingPropertiesInclude, array $filterLoadingPropertiesExclude, string $sortProperty, bool $descending): array;

    /**
     * Liefert Pfad und Dateiname zur Instanz, so dass sie daraus geladen
     * werden kann. Der Status ist der aktuelle Status aus dem Request oder der explizit angegebene.
     *
     * @param string $id
     * @return string
     */
    public function getPathAndFilename(string $id): string;

    /**
     * Erstellt oder laedt einen Note Instanz und aktualisiert sie aus den $properties, die aus dem Request kommen.
     * Das kann auch eine Config Note sein.
     *
     * @param string $id
     * @param string $view NoteText|NoteConfig
     * @param Properties_Interface $notePropertiesFromRequest
     * @return Note
     * @throws Throwable
     */
    public function loadOrCreateNote(string $id, string $view): Note;

    /**
     * @param Note $note
     * @param Properties_Interface $propertiesNotePersistent
     * @return Note
     */
    public function updateNoteFromRequest(Note $note, Properties_Interface $propertiesNotePersistent);

    /**
     * Speichert eine vollstaendige Instanz unter dem aktuellen Pfad.
     * @param Note $note
     * @param string $filename
     */
    public function noteSaveToFile(Note $note, string $filename);
    /**
     * Speichert eine vollstaendige Instanz.
     * @param Note $note
     */
    public function noteSave(Note $note);

    /**
     * Loescht eine vollstaendige Instanz.
     * @param Note $note
     * @throws Throwable
     */
    public function noteDelete(Note $note);

    /**
     * Erstellt Backup fuer eine PersistenceActive oder PersistenceDeleted oder PersistenceBackup Instanz.
     * @param Note $note
     */
    public function noteBackup(Note $note);
}
