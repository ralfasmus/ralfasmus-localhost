<?php
/**
 * Aktueller Request.
 * Class Request
 */
final class Request implements Properties_Interface, Request_Interface
{

    /**
     * @var Properties_Interface|null GET/POST Parameter, die nicht mit note- beginnen, also weder in
     * $propertiesNoteBerechnet noch in $propertiesNotePersistent enthalten sind. Das sind Parameter, die also nicht der
     * zu bearbeitenden Note zugeordnet sind, sondern dem Request.
     */
    use PropertiesStatic_Trait,
        SingleInstance_Trait {
            createSingleInstance as private trait_createSingleInstance;
            getSingleInstance as trait_getSingleInstance;
    }

    /**
     * @see Request::$propertiesNotePersistent
     */
    private const REQUEST_PROPERTY_INDICATOR_NOTE_PERSISTENT = 'note-persistent-';
    /**
     * @see Request::$propertiesNoteBerechnet
     */
    private const REQUEST_PROPERTY_INDICATOR_NOTE_BERECHNET = 'note-berechnet-';

    private static $singleInstance = null;
    /**
     * @var Properties_Interface|null GET/POST Parameter, deren Name mit note-persistent- beginnt. Sie werden der in diesem
     * EDIT/SAVE/ zu bearbeitenden Note-Instanz zugeordnet und werden beim Speichern der Note auf dem Filesystem
     * beruecksichtigt.
     */
    private $propertiesNotePersistent = NULL;
    /**
     * @var Properties_Interface|null GET/POST Parameter, deren Name mit note-berechnet- beginnt. Sie werden der in diesem
     * EDIT/SAVE/ zu bearbeitenden Note-Instanz zugeordnet und werden beim Speichern der Note auf dem Filesystem
     * IGNORIERT.
     */
    private $propertiesNoteBerechnet = NULL;

    /**
     * @var array|null Hash Array von Properties_Interface Objekten mit den keys "get", "post", "files", "server" usw.
     */
    private $propertiesAll = NULL;

    /**
     * Abruf der SingleInstance. Diese muss vorher explizit mittels @see SingleInstance_Trait::createSingleInstance()
     * erzeugt worden sein.
     *
     * @return Request_Interface
     */
    static public function getSingleInstance() : Request_Interface {
       return static::trait_getSingleInstance();
    }


    /**
     * Request Property, die den Status zum Laden von Notes definiert.
     */
    public const REQUEST_PROPERTY_STATUS = 'status';

    static private function removeParameterPrefix(array $parameters, string $prefix) : array {
        $result = array();
        foreach($parameters as $key => $value) {
            $result[str_replace($prefix, '', $key)] = $value;
        }
        return $result;
    }

    static public function createSingleInstance(array $requestProperties) : Request_Interface {
        return static::trait_createSingleInstance()->initialize($requestProperties);
    }

    /**
     * Request initialization.
     * @param array $requestProperties
     * @throws Exception
     */
    private function initialize(array $requestProperties) : Request_Interface
    {
        $this->initializePropertiesTrait();
        $this->propertiesAll = new PropertiesStatic($requestProperties);
        $propertiesGet = $this->getPropertiesAll()->getPropertyDefault('get', array(), true);
        $propertiesPost = $this->getPropertiesAll()->getPropertyDefault('post', array(), true);

        // POST ueberschreibt get. Muessen aber alles alphanumerische Schluessel sein.
        $parameters = array_merge($propertiesGet, $propertiesPost);

        // Persistent Note-Instanz Parameter extrahieren
        $this->propertiesNotePersistent = new PropertiesStatic(static::removeParameterPrefix(
                array_filter($parameters, function($key) { return stripos($key, static::REQUEST_PROPERTY_INDICATOR_NOTE_PERSISTENT) === 0; }, ARRAY_FILTER_USE_KEY ),
                static::REQUEST_PROPERTY_INDICATOR_NOTE_PERSISTENT));

        // Berechnete Note-Instanz Parameter extrahieren
        $this->propertiesNoteBerechnet = new PropertiesStatic(static::removeParameterPrefix(
                array_filter($parameters, function($key) { return stripos($key, static::REQUEST_PROPERTY_INDICATOR_NOTE_BERECHNET) === 0; }, ARRAY_FILTER_USE_KEY ),
                static::REQUEST_PROPERTY_INDICATOR_NOTE_BERECHNET));

        // Request Parameter extrahieren
        $this->setProperties(array_filter($parameters, function($key) {
            return stripos($key, static::REQUEST_PROPERTY_INDICATOR_NOTE_BERECHNET) === false
                    && stripos($key, static::REQUEST_PROPERTY_INDICATOR_NOTE_PERSISTENT) === false;
        }, ARRAY_FILTER_USE_KEY ));

        // Initialisierung der Persistence Schicht, bevor sie mittels ::getSingleInstance() genutzt werden kann
        NoteFactory::createSingleInstance();
        PersistenceActive::createSingleInstance();
        PersistenceBackup::createSingleInstance();
        PersistenceDeleted::createSingleInstance();

        // Load and/or create Configuration Object for this request
        Config::createSingleInstance();

        return $this;
    }


    private function getPropertiesAll() : Properties_Interface {
        return $this->propertiesAll;
    }
    private function getPropertiesCookie() : Properties_Interface {
        return new PropertiesStatic($this->propertiesAll->getPropertyMandatory('cookie', 'Request wurde nicht richtig initialisiert. $_COOKIE steht nicht im Request Object bereit.'));
    }
    private function getPropertiesFiles() : Properties_Interface {
        return new PropertiesStatic($this->propertiesAll->getPropertyMandatory('files', 'Request wurde nicht richtig initialisiert. $_FILES steht nicht im Request Object bereit.'));
    }
    private function getPropertiesServer() : Properties_Interface {
        return new PropertiesStatic($this->propertiesAll->getPropertyMandatory('server', 'Request wurde nicht richtig initialisiert. $_SERVER steht nicht im Request Object bereit.'));
    }
    /**
     * @return Properties_Interface
     *@see Request::$propertiesNotePersistent
     */
    public function getPropertiesNotePersistent(): Properties_Interface
    {
        return $this->propertiesNotePersistent;
    }
    /**
     * @return Properties_Interface
     *@see Request::$properties
     */
    public function getPropertiesRequest(): Properties_Interface
    {
        return $this;
    }

    public function getConfigValue(string $key) : string {
        return Config::getSingleInstance()->getPropertyDefault($key, '');
    }

    /**
     * Liefert den Persistence Status fuer diesen Request. Der Status ergibt sich aus der Property 'status' der verwendeten
     * Konfiguration. Nur wenn keine Konfiguration angegeben ist, wird eine GET Variable 'status' ausgewertet,
     * die bei der Erzeugung der Konfiguration dann als status gesetzt wird. Ist keine GET Variable 'status' angegeben,
     * wird ein Default Wert 'PersistenceActive' angenommen.
     *
     * @return string PersistenceActive|PersistenceDeleted|PersistenceBackup Fuer diesen Request zu verwendender Status beim Laden von Notes.
     */
    public function getStatus(): string
    {
        $status = $this->getConfigValue(static::REQUEST_PROPERTY_STATUS);
        if($status == '') {
            return 'PersistenceActive';
        }
    }


    /**
     * Generiert und liefert das Response HTML. Dabei wird auch ein Action wie z.B. Speichern, Loeschen, Backup einer
     * Note ausgefuehrt.
     */
    public function getResponse(): string
    {
        $html = "";
        try {
            //echo '<br>GET properties: ' . var_export($this->propertiesRequest->getProperties(), true);
            // Browser: index.php?filter-views=NoteText&pcreate[processor_class]=ProcessorView&pcreate[processor_method]=getHtml&pinit[view]=notelist&config-id=default-PersistenceActive
            // AJAX: index.php?processor.class=ProcessorView&processor.method=saveItem&status=PersistenceActive
            $processorCreateProperties = new PropertiesStatic($this->getPropertiesRequest()->getPropertyDefault('pcreate', array()));
            $processorInitProperties = new PropertiesStatic($this->getPropertiesRequest()->getPropertyDefault('pinit', array()));
            $html .= ProcessorFactory::createSingleInstance()->createProcessor($processorCreateProperties, $processorInitProperties)->execute();
            Log::info("Done!!!");
        } catch (Throwable $throwable) {
            MyThrowable::handleThrowable($throwable, 'Fehler beim Erzeugen der Response.', false);
        }
        $propertiesServer = $this->getPropertiesServer();
        Log::info('Request: ' . $propertiesServer->getPropertyDefault('REQUEST_URI'));
        foreach (array('post', 'cookie', 'get', 'files', 'server', 'request') as $key) {
            $container = $this->getPropertiesAll()->getPropertyMandatory($key, "Request nicht richtig initialisiert. Request Properties fuer $key nicht gefunden");
            Log::info("<h4>Request Properties -$key-:</h4>");
            foreach ($container as $name => $value) {
                Log::info("<strong>$name</strong>=" . var_export($value,true));
            }
        }
        $html .= Log::getHtmlLog() . Log::getConsoleLog();

        return $html;
    }

}
