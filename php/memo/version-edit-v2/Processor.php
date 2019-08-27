<?php

/**
 * Properties eines Processors koennen im view/*.html mit processor-class=this&processor-method=getPropertyDefault
 * eingebunden werden.
 * Class Processor
 */
abstract class Processor implements Properties_Interface
{
    use Properties_Trait;
    /**
     * @var null Dynamische Properties werden ggf von dem persistenten Note Item geliefert.
     */
    private $dynamicPropertiesItem = null;
    /**
     * Prozessor, der diesen erzeugt hat. Bei den ProcessorView-Instanzen wird ueber diese Beziehung der Dateiname des
     * zu bearbeitenden Views zusammengesetzt.
     *
     * @var Processor|null
     */
    protected $parentProcessor = null;

    /**
     * Request Property, die die ID der zu bearbeitenden Note definiert.
     */
    private const REQUEST_PROPERTY_NOTE_ID = 'id';
    /**
     * Processor constructor.
     * @param Processor|null $parentProcessor
     * @param Properties_Interface $properties
     */
    protected function __construct(?Processor $parentProcessor, Properties_Interface $properties)
    {
        Log::logInstanceCreated($this);
        return $this->initialize($parentProcessor, $properties);
    }

    protected function initialize(?Processor $parentProcessor, Properties_Interface $properties) : self {
        $this->parentProcessor = $parentProcessor;
        $this->setProperties($properties->getProperties());
        $this->dynamicPropertiesItem = $properties;
        return $this;
    }

    protected function getParentProcessor() {
        return $this->parentProcessor;
    }

    /**
     * Liefert alle CSS Klassen dieses Processors.
     *
     * @return string
     */
    protected function getCssClasses() : string {
        return (is_null($this->getParentProcessor()) ? '' : $this->getParentProcessor()->getCssClasses());
    }

    protected function getRequest() : Request {
        return Request::getSingleInstance();
    }

    /**
     * Erzeugt einen Child Prozessor aus den $properties und ruft die dort angegeben Methode auf, um das Ergebnis daraus
     * zurueck zu liefern.
     *
     * @param Properties_Interface $properties
     * @return mixed
     * @throws Exception
     */
    public function callFromProperties(Properties_Interface $properties) {
        $processorInstanceProperty = $properties->getPropertyDefault('processor-class', 'ProcessorView', true);
        $processorClassProperties = $properties->getPropertyDefault('processor-class-properties', array(), true);
        $processorClassProperties = is_array($processorClassProperties) ? new Properties($processorClassProperties) : $processorClassProperties;
        $processorMethod = $properties->getPropertyDefault('processor-method', 'getHtml', true);
        $processorMethodParameters = $properties->getPropertyDefault('pmp', array(), true);
        $processorInstance = ($processorInstanceProperty == 'this')
                ? $this
                : (($processorInstanceProperty == 'parent')
                    ? $this->getParentProcessor()
                    : new $processorInstanceProperty($this, $processorClassProperties));
        $callable = array( $processorInstance, $processorMethod);
        if(!is_callable($callable)) {
            MyThrowable::throw("Kann den im Template definierten Platzhalter nicht aufloesen. Das ist kein valider Callable Instance/Class: $processorInstanceProperty="
                    . var_export($processorInstance) . " Properties:". var_export($processorClassProperties, true)
                    . " Method:$processorMethod Parameters:" . var_export($processorMethodParameters, true) . "");
        }
        return call_user_func_array( $callable, $processorMethodParameters);
    }

    protected function getView() : string {
        return '';
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
    public function getPersistence(string $status = ''): Persistence_Interface
    {
        $status = ($status == '') ? $this->getRequest()->getStatus() : $status;
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
        $id = $this->getRequest()->getPropertiesNotePersistent()->getPropertyMandatory(self::REQUEST_PROPERTY_NOTE_ID, true);
        $propertiesNote = $this->getRequest()->getPropertiesNotePersistent();
        $note = $this->getPersistence()->loadOrCreateNote($id, $propertiesNote->getPropertyDefault('view', 'NoteDefault', true));
        $note = $this->getPersistence()->updateNoteFromRequest($note, $propertiesNote);
        return $note;
    }


    /**
     * @see Properties_Trait::getDynamicProperty()
     *
     * @param string $key
     * @return |null
     */
    public function getDynamicProperty(string $key) {
        // Es gibt hier keine dynamisch berechneten Properties.
        return $this->dynamicPropertiesItem->getDynamicProperty($key);
    }

}