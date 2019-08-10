<?php
/**
 * Alle Funktionen zum persistenten Laden und Speichern von Notes im Status active.
 * Class PersistenceActive
 */
class PersistenceActive extends PersistenceAbstract
{
    protected function getPersistanceStatus() {
        return self::PERSISTANCE_STATUS_ACTIVE;
    }

    /**
     * Speichert eine vollstaendige Note Instanz inkl. Backup-Sicherung der neu gespeicherten Version.
     *
     * @param Note $note
     */
    public function noteSave(Note $note)
    {
        parent::noteSave($note);
        $persistenceHandler = PersistenceBackup::getSingleInstance();
        $persistenceHandler->noteSave($note);
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
