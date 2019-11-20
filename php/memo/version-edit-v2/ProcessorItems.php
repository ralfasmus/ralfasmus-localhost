<?php
/**
 * Der Processor wird in einem view/*.html als Platzhalter eingebunden,
 * wenn ein Next-Level-View nicht nur einmal eingefuegt werden soll,
 * sondern mehrfach fuer eine Liste von Items. Diese Liste von Items wird hier in der Platzhalter-Methode berechnet.
 *
 * Class ProcessorItems
 */
final class ProcessorItems extends Processor
{
    /**
     * Request/Config Properties, deren Werte die Filter Properties und Regexes fuer das Laden der anzuzeigenden Notes enthalten
     */
    private const CONFIG_PROPERTY_FILTER_PROPERTIES_INCLUDE = "filter-properties-include";
    private const CONFIG_PROPERTY_FILTER_PROPERTIES_EXCLUDE = "filter-properties-exclude";

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
            $processorCreateProperties = new Properties(array(
                //'ProcessorThis' => $this->getPropertyMandatory( 'ProcessorThis'),
                'processor-class' => 'ProcessorView',
                'pexec' => array()
            ));

            $processorInitProperties = new Properties(array(
                    'ParentView' => $this->getPropertyDefault('ParentView', ''),
                    'view' => $item->getPropertyDefault('view', ''),
                    'ParentCssClasses' => $this->getPropertyDefault('ParentCssClasses', ''),
                )
            );
            $processorInitProperties->setDynamicPropertiesItem($item);

            $html .= ProcessorFactory::getSingleInstance()->createProcessor($processorCreateProperties, $processorInitProperties)->execute();
        }
        return $html;
    }

    /**
     * Liefert das HTML fuer die Liste der sichtbaren Notes des Requests.
     *
     * pcreate[processor-class]=ProcessorItems&pcreate[processor-method]=getHtmlNotes
     *
     * @return string
     */
    public function getHtmlNotes() : string
    {
        return $this->getHtmlItems($this->getNotesOfRequest());
    }


    /**
     * Liefert das HTML fuer die Liste der 'art' Werte der sichtbaren Notes des Requests.
     *
     * pcreate[processor-class]=ProcessorItems&pcreate[processor-method]=getHtmlArts
     *
     * @return string
     */
    public function getHtmlArts() : string
    {
        $notes = $this->getNotesOfRequest();
        return $this->getHtmlItems(Note::getArtsList($notes));
    }


    /**
     * Liefert das HTML fuer die EDIT Seite der Note Instance, die in den Request Daten spezifiziert ist.
     *
     * pcreate[processor-class]=ProcessorItems&pcreate[processor-method]=getHtmlEditUpdatedActionNote
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
        $includeRegex = json_decode('{' . Request::getSingleInstance()->getConfigValue(static::CONFIG_PROPERTY_FILTER_PROPERTIES_INCLUDE) . '}', true);
        $excludeRegex = json_decode('{' . Request::getSingleInstance()->getConfigValue(static::CONFIG_PROPERTY_FILTER_PROPERTIES_EXCLUDE) . '}', true);
        return $this->getPersistence()->getNotes($includeRegex, $excludeRegex,Note::NOTE_PROPERTY_NAME, false, Request::getSingleInstance()->getStatus());
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
