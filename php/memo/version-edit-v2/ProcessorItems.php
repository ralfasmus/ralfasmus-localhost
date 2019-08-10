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
     * Request/Config Property, deren Wert den "views" Filter der anzuzeigenden Notes enthaelt
     */
    private const CONFIG_PROPERTY_FILTER_VIEWS = "filter-views";

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
    private function getHtmlItems(array $items) {
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
    protected function getHtmlNotes()
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
    protected function getHtmlArts()
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
    public function getHtmlEditUpdatedActionNote()
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
        return $this->getPersistance()->getNotes($this->getConfig()->getPropertyDefault(self::CONFIG_PROPERTY_FILTER_VIEWS), Note::NOTE_PROPERTY_NAME, false, $this->getStatus());
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
