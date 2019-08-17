<?php
/**
 * Alle Funktionen zum persistenten Laden und Speichern von Notes im Status deleted.
 * Class PersistenceDeleted
 */
class PersistenceDeleted implements Persistence_Interface
{
    use SingleInstance_Trait { createSingleInstance as public; }
    use Persistence_Trait;

    /**
     * Loescht eine vollstaendige Instanz.
     *
     * @param Note $note
     * @throws Throwable
     */
    public function noteDelete(Note $note)
    {
        // Note loeschen
        try {
            $filename = $this->getPathAndFilename($note->getId());
            unlink($filename);
        } catch (Throwable $throwable) {
            MyThrowable::handleThrowable($throwable,'Kann Datei nicht loeschen.');
        }
    }
}
