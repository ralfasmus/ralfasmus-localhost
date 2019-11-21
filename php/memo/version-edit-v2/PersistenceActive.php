<?php
/**
 * Alle Funktionen zum persistenten Laden und Speichern von Notes im Status PersistenceActive.
 * Class PersistenceActive
 */
class PersistenceActive extends Persistence
{
    use SingleInstance_Trait;
    use Persistence_Trait { noteSave as private noteSaveDefault; }

    /**
     * Speichert eine vollstaendige Note Instanz inkl. Backup-Sicherung der neu gespeicherten Version.
     *
     * @param Note $note
     */
    public function noteSave(Note $note)
    {
        $this->noteSaveDefault($note, true);
        PersistenceBackup::getSingleInstance()->noteSave($note);
    }

    /**
     * Erstellt Backup fuer eine Note Instanz.
     *
     * @param Note $note
     */
    public function noteBackup(Note $note)
    {
        PersistenceBackup::getSingleInstance()->noteSave($note);
    }

}
