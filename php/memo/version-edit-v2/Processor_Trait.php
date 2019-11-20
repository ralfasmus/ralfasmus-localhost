<?php

/**
 * Properties eines Processors koennen im view/*.html mit pcreate[processor-class]=this&pcreate[processor-method]=getPropertyDefault
 * eingebunden werden.
 * Class Processor
 */
Trait Processor_Trait
{
    private $processorMethod = '';
    private $processorMethodParameters = array();

    static public function createInstance(Properties_Interface $properties) : Processor_Interface {
        return new static($properties);
    }

    /**
     * Processor constructor.
     * @param Properties_Interface $properties
     */
    private function __construct(Properties_Interface $properties)
    {
        Log::logInstanceCreated($this);
        return $this->initialize($properties);
    }

    private function initialize(Properties_Interface $properties) : self {
        $this->setDynamicPropertiesItem($properties);
        return $this;
    }

    public function execute() {
        $method = $this->processorMethod;
        $parameters = $this->processorMethodParameters;
        $callable = array($this, $method);
        assert(is_callable($callable));
        if(!is_callable($callable)) {
            /*
            MyThrowable::throw('<pre>'. "Kann den im Template definierten Platzhalter nicht aufloesen. Das ist kein valider Callable Instance/Class: $processorClassProperty="
                . get_class($processorInstance) . ":" . var_export($processorInstance) . " Properties:". var_export($processorInitProperties, true)
                . " Method:$processorMethod Parameters:" . var_export($processorMethodParameters, true) . "") . '</pre>';
            */
        }
        return call_user_func_array($callable, $parameters);
    }

    public function setProcessorMethod(string $processorMethod) : void {
        $this->processorMethod = $processorMethod;
    }

    public function setProcessorMethodParameters(array $processorMethodParameters) : void {
        $this->processorMethodParameters = $processorMethodParameters;
    }

    public function getConfigValue(string $key) : string
    {
        return Request::getSingleInstance()->getConfigValue($key);
    }

    /**
     * Liefert den Persistence Handler zum aktuell im Request gueltigen Status. Der Status wird ueber die ConfigNote
     * geliefert. Ausnahme: Fuer das Laden der Config Note selbst, ist der Status immer PersistenceActive. Die Config Notes fuer
     * status=PersistenceBackup und status=PersistenceDeleted liegen also auch im Verzeichnis /PersistenceActive/ !
     *
     * @param string $status
     * @return Persistence_Interface
     * @throws Exception
     */
    public function getPersistence(string $status = 'PersistenceActive'): Persistence_Interface
    {
        $status = ($status == '') ? Request::getSingleInstance()->getStatus() : $status;
        assert($status != '', 'Kann status nicht bestimmen und deshalb keinen Persistence Handler laden.');
        return $status::getSingleInstance();
    }

    /**
     * Laedt die zu bearbeitende Note, aktualisiert sie aus den Note-bezogenen Request GET/POST Parametern,
     * speichert sie und liefert sie als Ergebnis. Hierbei handelt es sich nicht um eine Config Note, sondern eine
     * "normale" Note.
     *
     * @return Note
     * @throws Exception wenn der ID Parameter nicht gesetzt ist.
     */
    public function getUpdatedActionNote(): Note
    {
        $id = Request::getSingleInstance()->getPropertiesNotePersistent()->getPropertyMandatory('id', true);
        $propertiesNote = Request::getSingleInstance()->getPropertiesNotePersistent();
        $note = $this->getPersistence()->loadOrCreateNote($id, $propertiesNote->getPropertyDefault('view', 'NoteDefault', true));
        $note = $this->getPersistence()->updateNoteFromRequest($note, $propertiesNote);
        return $note;
    }
}