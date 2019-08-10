<?php

/**
 * Class Processor
 */
abstract class Processor
{
    /**
     * Prozessor, der diesen erzeugt hat. Bei den ProcessorView-Instanzen wird ueber diese Beziehung der Dateiname des
     * zu bearbeitenden Views zusammengesetzt.
     *
     * @var Processor|null
     */
    protected $parentProcessor = null;
    /**
     * Properties dieses Processors. Koennen im view/*.html mit processor-class=this&processor-method=getPropertyDefault
     * eingebunden werden.
     *
     * @var Properties_Interface|null
     */
    protected $properties = null;

    /**
     * Request Property, die die ID zum Laden der Config-Note definiert.
     * @see Processor::getConfig()
     */
    private const REQUEST_PROPERTY_CONFIG_ID = 'config-id';
    private const REQUEST_PROPERTY_CONFIG_ID_DEFAULT = 'default';

    /**
     * Request Property, die den Status zum Laden von Notes definiert.
     */
    private const REQUEST_PROPERTY_STATUS = 'status';
    /**
     * Default Wert fuer @see Request::REQUEST_PROPERTY_STATUS
     */
    private const REQUEST_PROPERTY_STATUS_DEFAULT = 'active';
    /**
     * Request Property, die die ID der zu bearbeitenden Note definiert.
     */
    private const REQUEST_PROPERTY_NOTE_ID = 'id';
    /**
     * @var string Status fuer Persistence im aktuellen Request.
     * active|backup|deleted
     */
    private $status = '';
    /**
     * @var Note|null Note-Instanz, deren Properties diverse Einstellungen fuer diesen Request bestimmen.
     * @see Processor::getConfig()
     */
    private $requestConfigCache = NULL;



    /**
     * Processor constructor.
     * @param Processor|null $parentProcessor
     * @param Properties_Interface $properties
     */
    public function __construct(?Processor $parentProcessor, Properties_Interface $properties)
    {
        $this->parentProcessor = $parentProcessor;
        $this->properties = $properties;
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

    // ############# INTERFACE PROPERTIES #################################

    /**
     * Betrachtet nur die Request/Action Properties, keine Note Properties.
     * @see Properties_Interface::getPropertyDefault()
     *
     * @param string $key
     * @param string $default
     * @param bool $defaultOnEmpty
     * @return mixed
     */
    public function getPropertyDefault(string $key, $default = '', bool $defaultOnEmpty = false)
    {
        return $this->properties->getPropertyDefault($key, $default, $defaultOnEmpty);
    }

    /**
     * @see Properties_Interface::getPropertyMandatory()
     *
     * @param string $key
     * @param bool $exceptionOnEmpty
     * @param string $exceptionText
     * @return mixed|void
     */
    public function getPropertyMandatory(string $key, bool $exceptionOnEmpty = true, string $exceptionText = '') {
        return $this->properties->getPropertyMandatory($key, $exceptionOnEmpty, $exceptionText);
    }
    /**
     * @see Properties_Interface::setProperties()
     *
     * @param array $properties
     * @return mixed|void
     * @throws Exception
     */
    public function setProperties(array $properties)
    {
        throw new Exception('Not implemented.');
    }

    /**
     * @see Properties_Interface::getProperties()
     *
     * @return array
     * @throws Exception
     */
    public function getProperties(): array
    {
        return $this->properties->getProperties();
    }

    /**
     * @see Properties_Interface::setProperty()
     *
     * @param $value
     * @param string $key
     * @return mixed|void
     * @throws Exception
     */
    public function setProperty($value, string $key)
    {
        return $this->properties->setProperty($value, $key);
    }

    /**
     * Betrachtet nur die Request/Action Properties, keine Note Properties.
     * @see Properties_Interface::getDecodedProperty()
     * @param string $key
     * @param string $default
     * @param bool $defaultOnEmpty
     * @return string
     * @throws Exception
     */
    public function getDecodedProperty(string $key, string $default = '', $defaultOnEmpty = false) : string
    {
        return $this->properties->getDecodedProperty($key, $default, $defaultOnEmpty);
    }

    protected function getView() : string {
        return '';
    }

    /**
     * Liefert den Persistance Handler zum aktuell im Request gueltigen Status. Der Status wird ueber die ConfigNote
     * geliefert. Ausnahme: Fuer das Laden der Config Note selbst, ist der Status immer active. Die Config Notes fuer
     * status=backup und status=deleted liegen also auch im Verzeichnis /active/ !
     *
     * @param string $status
     * @return PersistenceAbstract
     * @throws Exception
     */
    public function getPersistance(string $status = ''): PersistenceAbstract
    {
        $status = ($status == '') ? $this->getStatus() : $status;
        switch ($status) {
            case "active" :
                return PersistenceActive::getSingleInstance();
                break;
            case "backup" :
                return PersistenceBackup::getSingleInstance();
                break;
            case "deleted" :
                return PersistenceDeleted::getSingleInstance();
                break;
            default:
                throw new Exception("Unbekannter Persistance Status: $status");
        }
    }

    /**
     * Liefert den Persistance Status fuer diesen Request.
     *
     * @return string active|deleted|backup|archive Fuer diesen Request zu verwendender Status beim Laden von Notes.
     */
    public function getStatus(): string
    {
        if ($this->status == '') {
            $requestConfigId = $this->getPropertyDefault(self::REQUEST_PROPERTY_CONFIG_ID, '');
            if (strpos($requestConfigId, 'backup') !== false) {
                $this->status = 'backup';
            } else {
                if (strpos($requestConfigId, 'deleted') !== false) {
                    $this->status = 'deleted';
                } else {
                    // entweder es ist eine 'status=active' config oder keine config gesetzt.
                    if ($requestConfigId == '') {
                        // status aus request property 'status' bestimmen oder default setzen
                        $this->status = $this->getPropertyDefault('status', 'active', true);
                    } else {
                        $this->status = 'active';
                    }
                }
            }
        }
        return $this->status;
    }

    /**
     * Liefert die Note-Instanz, deren Properties diverse Einstellungen fuer diesen Request bestimmen.
     * Ist der Request-Parameter
     * @return Note
     * @see Request::$requestConfigCache
     */
    public function getConfig(): Note
    {
        if (is_null($this->requestConfigCache)) {
            $this->requestConfigCache = $this->getConfigNote();
        }
        return $this->requestConfigCache;
    }

    /**
     * Laedt die Config Note. Immer aus 'active'!
     *
     * @return Note
     */
    private function getConfigNote(): Note
    {
        return $this->getPersistance('active')->loadOrCreateNote($this->getConfigId());
    }

    private function getConfigId(): string
    {
        return $this->getPropertyDefault(self::REQUEST_PROPERTY_CONFIG_ID, self::REQUEST_PROPERTY_CONFIG_ID_DEFAULT
                . '-' . $this->getPropertyDefault(self::REQUEST_PROPERTY_STATUS, self::REQUEST_PROPERTY_STATUS_DEFAULT, true));
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
        $note = $this->getPersistance()->loadOrCreateNote($id);
        $note = $this->getPersistance()->updateNoteFromRequest($note, $this->getRequest()->getPropertiesNotePersistent());
        return $note;
    }

}