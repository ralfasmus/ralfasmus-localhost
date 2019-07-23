<?php

/**
 *
 * Aktueller Request.
 * @author asmusr
 */
class Request implements Properties_Interface {

    /**
     * Status des Requests
     */
    private const ITEM_STATUS_DEFAULT = 'active';
    // Request/Config Property zum Filtern der anzuzeigenden Notes:
    private const PROPERTY_FILTER_VIEWS                 = "filter-views";
    // default action fuer Request ?index.php ohne weitere GET/POST Parameter
    const REQUEST_ACTION_DEFAULT = 'homepage';

    private $requestConfig = NULL;
    private $requestStatus = self::ITEM_STATUS_DEFAULT;

    private $propertiesNotePersistent  = NULL;
    private $propertiesNoteBerechnet  = NULL;
    private $propertiesRequest  = NULL;
    private $propertiesAll      = NULL;

    public function __construct(array $requestProperties = array()) {

        $propertiesAll = new Properties();
        foreach($requestProperties as $key => $propertyArray) {
            $propertiesAll->setProperty($propertyArray, $key);
        }
        $propertiesGet = $propertiesAll->getProperty('get', array());
        $propertiesPost = $propertiesAll->getProperty('post', array());

        $this->propertiesNotePersistent = new Properties();
        $this->propertiesNoteBerechnet = new Properties();
        $this->propertiesRequest = new Properties();

        // POST ueberschreibt get. Muessen aber alles alphanumerische Schluessel sein.

        foreach(array_merge($propertiesGet, $propertiesPost) as $name => $value) {
            if(stripos($name, "note-persistent-") === 0) {
                $this->propertiesNotePersistent->setProperty($value, str_replace('note-persistent-','', $name));
            } else if(stripos($name, "note-berechnet-") === 0) {
               $this->propertiesNoteBerechnet->setProperty($value, str_replace('note-berechnet-','', $name));
            } else {
                // Request/Action Property
                $this->propertiesRequest->setProperty($value, $name);
            }
        }

        Log::debug('REQUEST PROPERTIES ITEM PERSISTENT:');
        Log::debug($this->propertiesNotePersistent->getProperties());
        Log::debug('REQUEST PROPERTIES ITEM BERECHNET:');
        Log::debug($this->propertiesNoteBerechnet->getProperties());
        Log::debug('REQUEST PROPERTIES REQUEST:');
        Log::debug($this->propertiesRequest->getProperties());
    }

  private $messages = array();
  private $messageLevel = 'debug';

  /**
   * Liefert den aktuellen Status, dessen Daten angezeigt werden.
   * deleted | backup | active
   */
    public function getRequestStatus() : string {
        return $this->getProperty('status', self::ITEM_STATUS_DEFAULT, true);
    }

    public function getConfig() : Note {
        if (is_null($this->requestConfig)) {
            $this->requestConfig = Persistence::loadOrCreateNote($this->getProperty("config-id", "defaultconfig"), $this);
            $this->requestConfig = Persistence::updateNoteFromRequest($this->requestConfig, $this->getPropertiesRequest());
            // wenn in der Url explizit angegeben ist "saveconfig=yes", dann speichere die request properties aus der Url,
            // wie z.B. filter-art oder filter-text oder filter-views persistent in der config. Sonst wirken sie sich zwar aus,
            // werden aber erst mit dem naechsten Change+Save der Config gespeichert.
            if($this->getProperty("saveconfig", "") == "yes") {
                Persistence::noteSave($this->requestConfig, $this);
            }
        }
        return $this->requestConfig;
    }

  public function getPropertiesNotePersistent() : Properties_Interface {
    return $this->propertiesNotePersistent;
  }

    public function getPropertiesRequest() : Properties_Interface {
      return $this->propertiesRequest;
    }

  public function getUpdatedActionNote() : Note {
    $id = $this->getPropertiesNotePersistent()->getProperty("id");
    $note = Persistence::loadOrCreateNote($id, $this);
    $note = Persistence::updateNoteFromRequest($note, $this->getPropertiesNotePersistent());
    return $note;
  }

    public function getArtsList(array $notes) : array {
        $arts = array();
        foreach($notes as $note) {
            $art = $note->getProperty("art", "");
            $artArray = explode(" ", $art);
            foreach($artArray as $art) {
                $arts[trim($art, " ")] = "";
                // jetzt die Art-Teile als weitere "Haupt-" Arts erzeugen:
                $subarts = explode('.', $art);
                foreach($subarts as $subart) {
                    if($subart != "") {
                        $arts[".$subart"] = "";
                    }
                }
            }
        }
        ksort($arts);
        $artProperties = array();
        foreach(array_keys($arts) as $art) {
            if($art != "") {
                $artProp = new Properties();
                $artProp->setProperty($art, "name");
                $artProp->setProperty("art", Properties_Interface::PROPERTY_VIEW);
                $artProperties[] = $artProp;
            }
        }
        return $artProperties;
    }


    public function getBackupExtension() {
        return $this->getProperty('backupextension', time(), true);
    }

    public function getNotes() : array {
        return Persistence::getNotes($this->getProperty(self::PROPERTY_FILTER_VIEWS, "") , "name", false, $this->getRequestStatus());
    }

   /**
    *
    */
    public function getResponse() :string {
        $html = "";
        try {
            $baseView = $this->getProperty('base-view', 'index-page');
            $html = View::replacePlaceHolders($baseView, $this);
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
  public function getProperty(string $key, $default = "exception", bool $defaultOnEmpty = false) {
    return $this->propertiesRequest->getProperty($key, $default, $defaultOnEmpty);
  }

  /**
   * Siehe interface description.
   */
  public function setProperties(array $properties){
    throw new Exception('Es wurde versucht, die Properties des Requests nachtraeglich zu setzen. Properties des Requests sind die POST und GET Properties. Sie werden einmalig im Konstruktor gesetzt.');
  }

  /**
   * Siehe interface description.
   */
  public function getProperties() : array {
    throw new Exception('Es wurde versucht, ein komplettes Set der Request Properties abzurufen. Properties des Requests sind die unterteilt in Persistente und berechnete Note Properties und Request Properties. Diese muessen mit den entsprechenden Methoden dediziert abgefragt werden.');
  }

  /**
   * Siehe interface description.
   */
  public function setProperty($value, string $key) {
    throw new Exception('Es wurde versucht, die Properties des Requests nachtraeglich zu setzen. Properties des Requests sind die POST und GET Properties. Sie werden einmalig im Konstruktor gesetzt.');
  }

  /**
   * Betrachtet nur die Request/Action Properties, keine Note Properties.
   */
  public function getDecodedProperty(string $key, $default = "exception") : string {
    return $this->propertiesRequest->getDecodedProperty($key, $default);
  }
}
