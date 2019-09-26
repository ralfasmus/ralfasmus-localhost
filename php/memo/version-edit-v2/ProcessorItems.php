<?php
/**
 * Der Processor wird in einem view/*.html als Platzhalter eingebunden,
 * wenn ein Next-Level-View nicht nur einmal eingefuegt werden soll,
 * sondern mehrfach fuer eine Liste von Items. Diese Liste von Items wird hier in der Platzhalter-Methode berechnet.
 *
 * Class ProcessorItems
 */
class ProcessorItems extends Processor
{
    /**
     * Request/Config Properties, deren Werte die Filter Properties und Regexes fuer das Laden der anzuzeigenden Notes enthalten
     */
    private const CONFIG_PROPERTY_FILTER_PROPERTIES_INCLUDE = "filter-properties-include";
    private const CONFIG_PROPERTY_FILTER_PROPERTIES_EXCLUDE = "filter-properties-exclude";

    public function __construct(?Processor $parentProcessor, Properties_Interface $properties)
    {
        parent::__construct($parentProcessor, $properties);
    }

    /**
     * Hilfsmethode: Fuer eine Liste von Note Instanzen das HTML jeweils aus dem gleichen View erzeugen und als
     * Konkatenation liefern.
     *
     * @param array $items Liste von items, die das Properties_Interface implementieren.
     * @return string
     */
    private function getHtmlItems(array $items) : string {
        $html = '';
        foreach($items as $item) {
            assert(in_array(Properties_Interface::class, class_implements($item)),
                    'Ein item implementiert nicht das Interface ' . Properties_Interface::class
                    . ' und kann deshalb nicht die Properties zur Initialisierung eines Processors liefern.');

            $html .= $this->getParentProcessor()->callFromProperties(new Properties(array(
                    'processor-class' => 'ProcessorView',
                    'processor-class-properties' => $item,
                    'processor-method' => 'getHtml',
                    'processor-method-parameters' => array()
            )));
        }
        return $html;
    }

    /**
     * Liefert das HTML fuer die Liste der sichtbaren Notes des Requests.
     *
     * processor-class=ProcessorItems&processor-method=getHtmlNotes
     *
     * @return string
     */
    protected function getHtmlNotes() : string
    {
        return $this->getHtmlItems($this->getNotesOfRequest());
    }


    /**
     * Liefert das HTML fuer die Liste der 'art' Werte der sichtbaren Notes des Requests.
     *
     * processor-class=ProcessorItems&processor-method=getHtmlArts
     *
     * @return string
     */
    protected function getHtmlArts() : string
    {
        $notes = $this->getNotesOfRequest();
        return $this->getHtmlItems(Note::getArtsList($notes));
    }


    /**
     * Liefert das HTML fuer die EDIT Seite der Note Instance, die in den Request Daten spezifiziert ist.
     *
     * processor-class=ProcessorItems&processor-method=getHtmlEditUpdatedActionNote
     *
     * @return string
     * @throws Exception
     */
    public function getHtmlEditUpdatedActionNote() : string
    {
        $notes = array($this->getUpdatedActionNote());
        return $this->getHtmlItems($notes);
    }

    /**
     * Laedt und liefert alle zu betrachtenden Notes dieses Requests.
     * @return array
     */
    public function getNotesOfRequest(): array
    {
        $config = $this->getRequest()->getConfig();
        $includeRegex = json_decode('{' . $config->getConfigValue(static::CONFIG_PROPERTY_FILTER_PROPERTIES_INCLUDE) . '}', true);
        $excludeRegex = json_decode('{' . $config->getConfigValue(static::CONFIG_PROPERTY_FILTER_PROPERTIES_EXCLUDE) . '}', true);
        return $this->getPersistence()->getNotes($includeRegex, $excludeRegex,Note::NOTE_PROPERTY_NAME, false, $this->getRequest()->getStatus());
    }
    /*
    // JSON:
    $data = array("notes-json" => array());
    foreach($notes as $note) {
        $data["notes-json"][] = $note->getProperties();
    }
    //$data["notes-json"] = $notes;
    header('Content-Type:json');
    $result = json_encode($data);
    */

}
