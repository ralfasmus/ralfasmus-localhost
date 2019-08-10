<?php
/**
 * Aktueller Request.
 * Class Request
 */
class Request implements Properties_Interface
{

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
     * @var Properties_Interface|null GET/POST Parameter, die nicht mit note- beginnen, also weder in
     * $propertiesNoteBerechnet noch in $propertiesNotePersistent enthalten sind. Das sind Parameter, die also nicht der
     * zu bearbeitenden Note zugeordnet sind, sondern dem Request.
     */
    private $propertiesRequest = NULL;
    /**
     * @var array|null Hash Array von Properties_Interface Objekten mit den keys "get", "post", "files", "server" usw.
     */
    private $propertiesAll = NULL;


    static private function removeParameterPrefix(array $parameters, string $prefix) : array {
        $result = array();
        foreach($parameters as $key => $value) {
            $result[str_replace($prefix, '', $key)] = $value;
        }
        return $result;
    }

    /**
     * Request constructor.
     * @param array $requestProperties
     * @throws Exception
     */
    public function __construct(array $requestProperties = array())
    {
        assert(is_null(self::$singleInstance), 'Klasse Request darf nur einmal instantiiert werden!');
        $this->propertiesAll = new Properties($requestProperties);
        $propertiesGet = $this->getPropertiesAll()->getPropertyDefault('get', array(), true);
        $propertiesPost = $this->getPropertiesAll()->getPropertyDefault('post', array(), true);

        // POST ueberschreibt get. Muessen aber alles alphanumerische Schluessel sein.
        $parameters = array_merge($propertiesGet, $propertiesPost);

        // Persistent Note-Instanz Parameter extrahieren
        $this->propertiesNotePersistent = new Properties(self::removeParameterPrefix(
                array_filter($parameters, function($key) { return stripos($key, self::REQUEST_PROPERTY_INDICATOR_NOTE_PERSISTENT) === 0; }, ARRAY_FILTER_USE_KEY ),
                self::REQUEST_PROPERTY_INDICATOR_NOTE_PERSISTENT));

        // Berechnete Note-Instanz Parameter extrahieren
        $this->propertiesNoteBerechnet = new Properties(self::removeParameterPrefix(
                array_filter($parameters, function($key) { return stripos($key, self::REQUEST_PROPERTY_INDICATOR_NOTE_BERECHNET) === 0; }, ARRAY_FILTER_USE_KEY ),
                self::REQUEST_PROPERTY_INDICATOR_NOTE_BERECHNET));

        // Request Parameter extrahieren
        $this->propertiesRequest = new Properties(array_filter($parameters, function($key) {
            return stripos($key, self::REQUEST_PROPERTY_INDICATOR_NOTE_BERECHNET) === false
                    && stripos($key, self::REQUEST_PROPERTY_INDICATOR_NOTE_PERSISTENT) === false;
        }, ARRAY_FILTER_USE_KEY ));

        self::$singleInstance = $this;
        Log::debug(self::REQUEST_PROPERTY_INDICATOR_NOTE_PERSISTENT . ' request properties:');
        Log::debug($this->propertiesNotePersistent->getProperties());
        Log::debug(self::REQUEST_PROPERTY_INDICATOR_NOTE_BERECHNET . ' request properties:');
        Log::debug($this->propertiesNoteBerechnet->getProperties());
        Log::debug('andere request properties:');
        Log::debug($this->propertiesRequest->getProperties());
    }

    static public function getSingleInstance() {
        assert(!is_null(self::$singleInstance), 'Request::singleInstance wurde noch nicht erzeugt, aber schopn abgerufen!');
        return self::$singleInstance;
    }

    private function getPropertiesAll() : Properties_Interface {
        return $this->propertiesAll;
    }
    private function getPropertiesCookie() : Properties_Interface {
        return new Properties($this->propertiesAll->getPropertyMandatory('cookie', 'Request wurde nicht richtig initialisiert. $_COOKIE steht nicht im Request Object bereit.'));
    }
    private function getPropertiesFiles() : Properties_Interface {
        return new Properties($this->propertiesAll->getPropertyMandatory('files', 'Request wurde nicht richtig initialisiert. $_FILES steht nicht im Request Object bereit.'));
    }
    private function getPropertiesServer() : Properties_Interface {
        return new Properties($this->propertiesAll->getPropertyMandatory('server', 'Request wurde nicht richtig initialisiert. $_SERVER steht nicht im Request Object bereit.'));
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
     *@see Request::$propertiesRequest
     */
    public function getPropertiesRequest(): Properties_Interface
    {
        return $this->propertiesRequest;
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
            // Browser: index.php?processor.class=ProcessorView&processor.method=getHtml&processor.method=getHtml&processor.class.properties=notelist|noteedit
            // AJAX: index.php?processor.class=ProcessorView&processor.method=saveItem&status=active
            $rootProcessor = new ProcessorRoot($this->getPropertiesRequest());
            $html .= $rootProcessor->getHtml();

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
       return $this->propertiesRequest->getPropertyDefault($key, $default, $defaultOnEmpty);
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
        return $this->propertiesRequest->getPropertyMandatory($key, $exceptionOnEmpty, $exceptionText);
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
        throw new Exception('Es wurde versucht, die Properties des Requests nachtraeglich zu setzen. Properties des Requests sind die POST und GET Properties. Sie werden einmalig im Konstruktor gesetzt.');
    }

    /**
     * @see Properties_Interface::getProperties()
     *
     * @return array
     * @throws Exception
     */
    public function getProperties(): array
    {
        throw new Exception('Es wurde versucht, ein komplettes Set der Request Properties abzurufen. Properties des Requests sind unterteilt in Persistente und berechnete Note Properties und Request Properties. Diese muessen mit den entsprechenden Methoden dediziert abgefragt werden.');
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
        throw new Exception('Es wurde versucht, die Properties des Requests nachtraeglich zu setzen. Properties des Requests sind die POST und GET Properties. Sie werden einmalig im Konstruktor gesetzt.');
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
        return $this->propertiesRequest->getDecodedProperty($key, $default, $defaultOnEmpty);
    }
}
