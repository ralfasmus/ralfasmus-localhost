<?php
/**
 * Liefert diverse Einstellungen fuer diesen Request.
 * Immer aus status 'PersistenceActive'!
 *
 * Class Config
 */
final class Config implements Properties_Interface
{
    use SingleInstance_Trait { createSingleInstance as private trait_createSingleInstance; }
    use Properties_Trait;

    /**
     * Request Property, die die ID zum Laden der Config-Note definiert.
     * @see self::getConfig()
     */
    private const REQUEST_PROPERTY_CONFIG_ID = 'config-id';
    private const REQUEST_PROPERTY_CONFIG_ID_DEFAULT = 'CONFIG-PersistenceActive-DEFAULT';

    private $note = null;


    static public function createSingleInstance() : self {
        return static::trait_createSingleInstance()->initialize();
    }

    /**
     * Initialization.
     * @param array $configProperties
     * @throws Exception
     */
    private function initialize() : self
    {
        $configId = Request::getSingleInstance()->getPropertyDefault(static::REQUEST_PROPERTY_CONFIG_ID, static::REQUEST_PROPERTY_CONFIG_ID_DEFAULT, true);
        $this->setProperties(array('config-id' => $configId));
        $this->setDynamicPropertiesItem($this);
        $persistenceForConfigs = PersistenceActive::getSingleInstance();
        $configNote = $persistenceForConfigs->loadNoteById($configId);
        if(is_null($configNote)) {
            //$persistenceForConfigs->loadOrCreateNote($configId, 'NoteDefault');
            //#asm MyThrowable::throw("Kann keine Config fuer diesen Request laden mit config-id=$configId");
        } else {
            $this->setProperties($configNote->getProperties());
        }
        return $this;
    }
}
