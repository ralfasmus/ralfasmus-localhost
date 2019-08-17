<?php
/**
 * Liefert diverse Einstellungen fuer diesen Request.
 * Immer aus status 'PersistenceActive'!
 *
 * Class Config
 */
final class Config implements Config_Interface
{
    use SingleInstance_Trait;

    private $note = null;

    static public function createConfig(string $configId) : self {
        return self::createSingleInstance()->initialize($configId);
    }

    private function initialize(string $configId) : self {
        assert(!is_null($configId) && '' != $configId, 'config-id darf nicht leer oder null sein.');
        $this->note = PersistenceActive::getSingleInstance()->loadOrCreateNote($configId, NoteDefault::class);
        return $this;
    }

    public function getConfigValue($key) {
        return $this->getNote()->getPropertyDefault($key, '', true);
    }

    private function getNote() : Note {
        return $this->note;
    }

}
