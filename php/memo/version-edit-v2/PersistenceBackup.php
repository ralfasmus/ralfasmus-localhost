<?php
/**
 * Alle Funktionen zum persistenten Laden und Speichern von Notes im Status backup.
 *
 * Class PersistenceBackup
 */
class PersistenceBackup extends Persistence
{
    use SingleInstance_Trait;
    use Persistence_Trait;
}
