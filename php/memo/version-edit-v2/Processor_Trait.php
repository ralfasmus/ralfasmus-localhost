<?php

/**
 * Properties eines Processors koennen im view/*.html mit pcreate[instance-class]=this&pcreate[processor-method]=getPropertyDefault
 * eingebunden werden.
 * Class Processor
 */
Trait Processor_Trait
{
    use Properties_Trait;

    /**
     * @var string Processor Method to execute via
     * @see Processor_Trait::execute().
     */
    private $processorMethod = '';

    /**
     * @var array Parameters to use when executing Processor Method via
     * @see Processor_Trait::execute().
     */
    private $processorMethodParameters = array();

    /**
     * Returns the Default Processor Method to use if
     * @see Processor_Trait::$processorMethod == ''.
     * Is implemented in the abstract Processor class to use late static binding.
     *
     * @return string
     */
    abstract function getDefaultProcessorMethod() : string;

    /**
     * @see Processor_Trait::$processorMethod.
     *
     * @return string
     */
    private function getProcessorMethod() {
        return $this->processorMethod == '' ? $this->getDefaultProcessorMethod() : $this->processorMethod;
    }

    /**
     * Erzeugt eine Instance eines Processors.
     * Wird aufgerufen in @see ProcessorFactory::createProcessor().
     *
     * @param Properties_Interface $properties
     * @return Processor_Interface
     */
    static public function createInstance(Properties_Interface $properties) : Processor_Interface {
        $instance = new static;
        $instance->initializeProcessorTrait($properties);
        Log::logInstanceCreated($instance);
        return $instance;
    }

    /**
     * Initialisiert die Eigenschaften dieses Trait.
     *
     * @param Properties_Interface $properties
     * @return $this
     */
    private function initializeProcessorTrait(Properties_Interface $properties) : void {
        $this->initializePropertiesTrait()->setDynamicPropertiesItem($properties);
    }

    /**
     * Fuehrt die @see Processor_Trait::$processorMethod des Processors mit seinen
     * @see Processor_Trait::$processorMethodParameters aus und liefert das Ergebnis.
     *
     * @return mixed
     */
    public function execute() {
        $method = $this->getProcessorMethod();
        $parameters = $this->processorMethodParameters;
        $callable = array($this, $method);
        assert(is_callable($callable));
        if(!is_callable($callable)) {
            MyThrowable::throw("Kann callable nicht ausfuehren.");
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