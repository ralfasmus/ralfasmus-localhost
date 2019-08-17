<?php
/**
 * Alle Funktionen zum persistenten Laden und Speichern von Notes im Status backup.
 *
 * Class PersistenceBackup
 */
class PersistenceBackup implements Persistence_Interface
{
    use SingleInstance_Trait { createSingleInstance as public; }
    use Persistence_Trait;
}
