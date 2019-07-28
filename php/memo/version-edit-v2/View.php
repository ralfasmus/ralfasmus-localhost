<?php

/**
 * Eine View Instance dient zur Generierung von HTML inklusive der Ersetzung von Platzhaltern der Form
 * REPLACED_BY_[NOTE|CONFIG|PROPERTY]_xxx_VALUE
 * Class View
 */
class View
{
    /**
     * Cache filename => file inhalt fuer View Template Dateien unter /view/
     * @var array string => string
     */
    private $viewTemplatesCache = array();

    /**
     * Liefert fuer jedes der Properties-Objekte (z.B. Notes) in $propertiesList das generierte HTML, wobei
     * fuer jede Instanz das passende Template geladen wird und alle REPLACED_BY_[NOTE|CONFIG|PROPERTY]_property_VALUE
     * Platzhalter durch die entsprechenden Werte des Properties-Objekts ersetzt werden.
     * Der Filename des fuer eine Instanz zu ladenden Templates bestimmt sich aus
     * $viewFileNameBase und $templateNameExtension + ".html"
     *
     * @param string $viewFileNameBase Bsp: 'notelist', 'notelist-header', 'notelist-filter', 'notelist-notes'
     * @param array $propertiesList
     * @return string
     * @throws Exception
     */
    static public function createHtml(string $viewFileNameBase, array $propertiesList): string
    {
        $viewFileNames = array();
        $html = '';
        foreach ($propertiesList as $propertiesItem) {
            // Bestimmung des zu verwendenden Item-spezifischen View Templates: Entsprechend Property 'view'
            $viewFileNameExtension = $propertiesItem->getProperty(Properties_Interface::PROPERTY_VIEW, '');
            $viewFileNameExtension = ($viewFileNameExtension == '') ? '' : "_item-${viewFileNameExtension}";
            $viewFileName = "${viewFileNameBase}${viewFileNameExtension}";

            if (!isset($viewFileNames[$viewFileName])) {
                $viewFileNames["$viewFileName"] = self::getViewHtml("${viewFileName}.html");
            }
            $html .= self::replacePlaceHoldersVALUE($viewFileNames[$viewFileName], $propertiesItem);
        }
        return $html;
    }

    /**
     * Laedt einen view vom Filesystem.
     * @param string $viewFilename
     * @return string
     * @throws Exception
     */
    static public function getViewHtml(string $viewFilename): string
    {
        $filename = APPLICATION_PHP_DIR . DIRECTORY_SEPARATOR . "view/$viewFilename";
        if (!file_exists($filename)) {
            throw new Exception("Kann View Datei nicht finden: " . str_replace("\\", "/", $filename));
        }
        return file_get_contents($filename);
    }

    /**
     * Liefert fuer $placeHolder das generierte HTML, wobei
     * das passende View Template geladen wird und alle REPLACED_BY_[NOTE|CONFIG|PROPERTY]_property_VALUE
     * Platzhalter durch die entsprechenden Werte des Properties-Objekts ersetzt werden.
     * Das ganze rekursiv.
     *
     * @param $placeHolder
     * @param $request
     * @return string
     * @throws Throwable
     */
    static public function replacePlaceHolders(string $placeHolder, Request $request) : string
    {

        $html = "";
        switch ($placeHolder) {

            // #########################################################################
            // # PAGE- und AJAX Action Requests: base-view, also das aeusserste
            // # Template, das dann diverse Platzhalter enthaelt
            // #########################################################################

            case 'index-page' : // fuehrt evtl. eine Action aus und liefert eine komplette HTML Seite
            case 'index-action' : // fuehrt i.d.R. eine Action aus und liefert i.d.R. nur ein HTML Fragment zur Anzeige des Ergebnisses in der Seite
            default:
                $html = View::getViewHtml("${placeHolder}.html");
                break;

            // #########################################################################
            // # AJAX Action Requests: body-content (und andere PLACE_HOLDER)
            // #########################################################################
            case 'notesave' :
                Persistence::noteSave($request->getUpdatedActionNote(), $request);
                break;
            case 'notedelete' :
                Persistence::noteDelete($request->getUpdatedActionNote(), $request);
                break;
            case 'notebackup' :
                Persistence::noteBackup($request->getUpdatedActionNote(), $request);
                break;
            case 'noterecover' :
                throw new Exception("Not Implemented");
                break;

            // #########################################################################
            // # PAGE Requests: body-content (und andere PLACE_HOLDER)
            // #########################################################################
            case 'notelist-filter' :
                //@TODO view und filter
                $notes = $request->getNotesOfRequest();
                $html = View::createHtml($placeHolder, Note::getArtsList($notes));
                break;
            case 'notelist-notes' :
                //@TODO view und filter
                $notes = $request->getNotesOfRequest();
                // @TODO hier noch die Config filter auswerten
                $html = View::createHtml($placeHolder, $notes);
                break;
            case 'noteedit' :
                $note = $request->getUpdatedActionNote();
                $html = View::createHtml($placeHolder, array($note));
                break;
            case 'body-content' :
                $action = $request->getProperty(Request::REQUEST_PROPERTY_ACTION, Request::REQUEST_PROPERTY_ACTION_DEFAULT);
                $html = self::replacePlaceHolders($action, $request);
                break;
            case 'page-class' :
                $html = $request->getProperty(Request::REQUEST_PROPERTY_ACTION, Request::REQUEST_PROPERTY_ACTION_DEFAULT);
                break;
        }

        // #### PLACE_HOLDER_CONFIG Properties im View Template ersetzen
        $configNote = $request->getConfig();
        $configNote->setProperty(Properties_Interface::PROPERTY_PLACE_HOLDER_INDICATOR_CONFIG, Properties_Interface::PROPERTY_PLACE_HOLDER_INDICATOR);
        $html = self::replacePlaceHoldersVALUE($html, $configNote);

        // #### PLACE_HOLDER_PROPERTY im Template ersetzen
        $regexp = "/REPLACED_BY_PROPERTY_([0-9a-zA-Z\-_]+)_VALUE/";
        preg_match_all($regexp, $html, $matches);
        $foundPlaceHolders = (count($matches) > 0) ? $matches[1] : array();

        // #### Rekursiv im erzeugten HTML nach REPLACED_BY suchen, das HTML dafuer generieren und diese dann erstzen
        $properties = new Properties();
        foreach ($foundPlaceHolders as $foundPlaceHolder) {
            $placeHolderHtml = self::replacePlaceHolders($foundPlaceHolder, $request);
            $properties->setProperty($placeHolderHtml, $foundPlaceHolder);
        }
        $html = self::replacePlaceHoldersVALUE($html, $properties);
        return $html;

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

    /**
     * Ersetze REPLACED_BY_[NOTE|CONFIG|PROPERTY]_name_VALUE Zeichenfolgen in einem html string
     * mit den Werten der entsprechenden Eigenschaften des gegebenen properties Objekts.
     * @param string $html
     * @param Properties_Interface $properties
     * @return string
     */
    static private function replacePlaceHoldersVALUE(string $html, Properties_Interface $properties): string
    {
        // NOTE|CONFIG|PROPERTY
        $placeHolderType = $properties->getProperty(Properties_Interface::PROPERTY_PLACE_HOLDER_INDICATOR, Properties_Interface::PROPERTY_PLACE_HOLDER_INDICATOR_DEFAULT);
        $regexp = "/REPLACED_BY_${placeHolderType}_([0-9a-zA-Z\-_]+)_VALUE/";
        preg_match_all($regexp, $html, $matches);
        $names = (count($matches) > 0) ? $matches[1] : array();
        foreach ($names as $name) {
            $html = str_replace("REPLACED_BY_${placeHolderType}_{$name}_VALUE", $properties->getProperty($name, ""), $html);
        }
        return $html;
    }
}
