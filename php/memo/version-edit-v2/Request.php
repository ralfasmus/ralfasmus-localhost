<?php
/**
 * Aktueller Request.
 * Class Request
 */
class Request implements Properties_Interface
{

    /**
     * Request/Config Property, deren Wert den "views" Filter der anzuzeigenden Notes enthaelt
     */
    private const CONFIG_PROPERTY_FILTER_VIEWS = "filter-views";
    /**
     * Request Property, die den zuerst zu ladenden view definiert. Das ist index-page.html fuer komplette HTML Seiten
     * inklusive head mit styles usw. oder index-action.html fuer Requests, die nur eine (AJAX) Action ausfuehren und
     * danach keine komplett neue Seite liefern, sondern nur evtl. ein Ergebnis der Action.
     * @see Request::getResponse()
     */
    private const REQUEST_PROPERTY_BASE_VIEW = 'base-view';
    /**
     * Default Wert fuer @see Request::REQUEST_PROPERTY_BASE_VIEW
     */
    private const REQUEST_PROPERTY_BASE_VIEW_DEFAULT = 'index-page';
    /**
     * Request Property, die die ID zum Laden der Config-Note definiert.
     * @see Request::getConfig()
     */
    private const REQUEST_PROPERTY_CONFIG_ID = 'config-id';
    /**
     * Default Wert fuer @see Request::REQUEST_PROPERTY_CONFIG_ID
     */
    private const REQUEST_PROPERTY_CONFIG_ID_DEFAULT = 'defaultconfig';
    /**
     * Request Property, die bestimmt, ob beim Laden einer Config diese auch direkt aus den Config GET Parametern
     * des Requests aktualisiert UND gespeichert wird.
     * @see Request::getConfig()
     */
    private const REQUEST_PROPERTY_SAVECONFIG = 'saveconfig';
    /**
     * Default Wert fuer @see Request::REQUEST_PROPERTY_SAVECONFIG
     */
    private const REQUEST_PROPERTY_SAVECONFIG_DEFAULT = 'no';
    /**
     * Request Property, die den Status zum Laden von Notes definiert.
     */
    private const REQUEST_PROPERTY_STATUS = 'status';
    /**
     * Default Wert fuer @see Request::REQUEST_PROPERTY_STATUS
     */
    private const REQUEST_PROPERTY_STATUS_DEFAULT = 'active';
    /**
     * Request Property, die die auszufuehrende Request Action festlegt
     */
    public const REQUEST_PROPERTY_ACTION = 'action';
    /**
     * Default action Property Wert des Requests.
     * @see Request::REQUEST_PROPERTY_ACTION
     */
    public const REQUEST_PROPERTY_ACTION_DEFAULT = 'homepage';
    /**
     * Request Property, die die ID der zu bearbeitenden Note definiert.
     */
    private const REQUEST_PROPERTY_NOTE_ID = 'id';
    /**
     * @see Request::$propertiesNotePersistent
     */
    private const REQUEST_PROPERTY_INDICATOR_NOTE_PERSISTENT = 'note-persistent-';
    /**
     * @see Request::$propertiesNoteBerechnet
     */
    private const REQUEST_PROPERTY_INDICATOR_NOTE_BERECHNET = 'note-berechnet-';

    /**
     * @var Note|null Note-Instanz, deren Properties diverse Einstellungen fuer diesen Request bestimmen.
     * @see Request::getConfig()
     */
    private $requestConfigCache = NULL;

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
        $propertiesAll = new Properties($requestProperties);
        $propertiesGet = $propertiesAll->getProperty('get', array());
        $propertiesPost = $propertiesAll->getProperty('post', array());

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

        Log::debug(self::REQUEST_PROPERTY_INDICATOR_NOTE_PERSISTENT . ' request properties:');
        Log::debug($this->propertiesNotePersistent->getProperties());
        Log::debug(self::REQUEST_PROPERTY_INDICATOR_NOTE_BERECHNET . ' request properties:');
        Log::debug($this->propertiesNoteBerechnet->getProperties());
        Log::debug('andere request properties:');
        Log::debug($this->propertiesRequest->getProperties());
    }

    /**
     * @return string active|deleted|backup|archive Fuer diesen Request zu verwendender Status beim Laden von Notes.
     */
    public function getRequestStatus(): string
    {
        return $this->getProperty(self::REQUEST_PROPERTY_STATUS, self::REQUEST_PROPERTY_STATUS_DEFAULT, true);
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
            $this->requestConfigCache = $this->getUpdatedConfigNote();

            // wenn in der Url explizit angegeben ist "saveconfig=yes", dann speichere die request properties aus der Url,
            // wie z.B. filter-art oder filter-text oder filter-views persistent in der config. Sonst wirken sie sich zwar aus,
            // werden aber erst mit dem naechsten Change+Save der Config gespeichert.
            if ($this->getProperty(self::REQUEST_PROPERTY_SAVECONFIG, self::REQUEST_PROPERTY_SAVECONFIG_DEFAULT) == "yes") {
                Persistence::noteSave($this->requestConfigCache, $this);
            }
        }
        return $this->requestConfigCache;
    }

    /**
     * Laedt die Config Note, aktualisiert sie aus den Config- Request GET/POST Parametern,
     * speichert sie und liefert sie als Ergebnis.
     *
     * @return Note
     */
    private function getUpdatedConfigNote(): Note
    {
        $id = $this->getProperty(self::REQUEST_PROPERTY_CONFIG_ID, self::REQUEST_PROPERTY_CONFIG_ID_DEFAULT);
        $note = Persistence::loadOrCreateNoteAndUpdate($id, $this, $this->getPropertiesRequest());
        return $note;
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
        $id = $this->getPropertiesNotePersistent()->getProperty(self::REQUEST_PROPERTY_NOTE_ID);
        $note = Persistence::loadOrCreateNoteAndUpdate($id, $this, $this->getPropertiesNotePersistent());
        return $note;
    }

    /**
     * @see Request::$propertiesNotePersistent
     * @return Properties_Interface
     */
    public function getPropertiesNotePersistent(): Properties_Interface
    {
        return $this->propertiesNotePersistent;
    }

    /**
     * @see Request::$propertiesRequest
     * @return Properties_Interface
     */
    public function getPropertiesRequest(): Properties_Interface
    {
        return $this->propertiesRequest;
    }

    /**
     * Laedt und liefert alle zu betrachtenden Notes dieses Requests.
     * @return array
     */
    public function getNotesOfRequest(): array
    {
        return Persistence::getNotes($this->getProperty(self::CONFIG_PROPERTY_FILTER_VIEWS, ""), Note::NOTE_PROPERTY_NAME, false, $this->getRequestStatus());
    }

    /**
     * Generiert und liefert das Response HTML. Dabei wird auch ein Action wie z.B. Speichern, Loeschen, Backup einer
     * Note ausgefuehrt.
     */
    public function getResponse(): string
    {
        $html = "";
        try {
            $baseViewName = $this->getProperty(self::REQUEST_PROPERTY_BASE_VIEW, self::REQUEST_PROPERTY_BASE_VIEW_DEFAULT);
            $html = View::replacePlaceHolders($baseViewName, $this);
            Log::info("Done!!!");
        } catch (Throwable $throwable) {
            Log::errorThrown($throwable);
        }
        $html .= Log::getHtmlLog() . Log::getConsoleLog();

        return ($html);
    }


    // ############# INTERFACE PROPERTIES #################################

    /**
     * Betrachtet nur die Request/Action Properties, keine Note Properties.
     */
    public function getProperty(string $key, $default = "exception", bool $defaultOnEmpty = false)
    {
        return $this->propertiesRequest->getProperty($key, $default, $defaultOnEmpty);
    }

    /**
     * Siehe interface description.
     */
    public function setProperties(array $properties)
    {
        throw new Exception('Es wurde versucht, die Properties des Requests nachtraeglich zu setzen. Properties des Requests sind die POST und GET Properties. Sie werden einmalig im Konstruktor gesetzt.');
    }

    /**
     * Siehe interface description.
     */
    public function getProperties(): array
    {
        throw new Exception('Es wurde versucht, ein komplettes Set der Request Properties abzurufen. Properties des Requests sind die unterteilt in Persistente und berechnete Note Properties und Request Properties. Diese muessen mit den entsprechenden Methoden dediziert abgefragt werden.');
    }

    /**
     * Siehe interface description.
     */
    public function setProperty($value, string $key)
    {
        throw new Exception('Es wurde versucht, die Properties des Requests nachtraeglich zu setzen. Properties des Requests sind die POST und GET Properties. Sie werden einmalig im Konstruktor gesetzt.');
    }

    /**
     * Betrachtet nur die Request/Action Properties, keine Note Properties.
     */
    public function getDecodedProperty(string $key, $default = "exception"): string
    {
        return $this->propertiesRequest->getDecodedProperty($key, $default);
    }
}
