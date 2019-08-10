<?php
/**
 * Alle Funktionen zum persistenten Laden und Speichern von Notes im Status backup.
 *
 * Class PersistenceBackup
 */
class PersistenceBackup extends PersistenceAbstract
{
    protected function getPersistanceStatus() {
        return self::PERSISTANCE_STATUS_BACKUP;
    }
}
