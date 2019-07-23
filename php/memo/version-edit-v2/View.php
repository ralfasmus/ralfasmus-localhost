<?php

/**
 * Eine View Instance dient zur Generierung von HTML inklusive der Ersetzung von Platzhaltern der Form
 * REPLACED_BY_[ITEM|CONFIG|PROPERTY]_xxx_VALUE
 */
class View {


    /**
     * Liefert fuer jedes der Properties-Objekte (z.B. Items) in $propertiesList das generierte HTML, wobei
     * das passende Template fuer jedes item geladen wird und alle REPLACED_BY_[ITEM|CONFIG|PROPERTY]_property_VALUE
     * Platzhalter durch die entsprechenden Werte des Properties-Objekts ersetzt werden.
     */
    static public function createHtml(string $placeHolder, array $propertiesList) : string {
        $templates = array();
        $html = '';
        foreach($propertiesList as $properties) {
            // Bestimmung des zu verwendenden Templates: Entsprechend Property 'view'
            $propertiesView = $properties->getProperty(Properties_Interface::PROPERTY_VIEW, "");
            $templateNameExtension = ($propertiesView == '') ? '' : "-${propertiesView}";
            $templateName = "${placeHolder}${templateNameExtension}";

            if(!isset($templates[$templateName])) {
                $templates[$templateName] = self::getViewHtml("${templateName}.html");
            }
            $html .= self::replacePlaceHoldersVALUE($templates[$templateName], $properties);
        }
        return $html;
    }

    /**
     * Laedt einen view vom Filesystem.
     */
    static public function getViewHtml(string $viewFilename) : string {
        $filename = APPLICATION_PHP_DIR . DIRECTORY_SEPARATOR . "view/$viewFilename";
        if (!file_exists($filename)) {
          throw new Exception("Kann View Datei nicht finden: " . str_replace("\\", "/", $filename));
        }
        return file_get_contents($filename);
    }


  /**
     * Liefert fuer $placeHolder das generierte HTML, wobei
     * das passende Template geladen wird und alle REPLACED_BY_[ITEM|CONFIG|PROPERTY]_property_VALUE
     * Platzhalter durch die entsprechenden Werte des Properties-Objekts ersetzt werden.
     * Das ganze rekursiv.
   */
    static public function replacePlaceHolders($placeHolder, $request) {

        $html = "";
        switch($placeHolder) {

            // #########################################################################
            // # PAGE- und AJAX Action Requests: base-view, also das aeusserste
            // # Template, das dann Platzhalter enthaelt
            // #########################################################################

            case 'index-page' :
            case 'index-action' :
            default:
                                            $html = View::getViewHtml("${placeHolder}.html");
                                            break;
            // #########################################################################
            // # AJAX Action Requests: body-content (und andere PLACE_HOLDER)
            // #########################################################################

            case 'itemsave' :
                                            Persistence::itemSave($request->getUpdatedActionItem(), $request);
                                            break;
            case 'itemdelete' :
                                            Persistence::itemDelete($request->getUpdatedActionItem(), $request);
                                            break;
            case 'itembackup' :
                                            Persistence::itemBackup($request->getUpdatedActionItem(), $request);
                                            break;
            case 'itemrecover' :
                                            throw new Exception("Not Implemented");
                                            break;

            // #########################################################################
            // # PAGE Requests: body-content (und andere PLACE_HOLDER)
            // #########################################################################

            case 'itemlist-filter' :
                                            //@TODO view und filter
                                            $items = $request->getItems();
                                            $html = View::createHtml($placeHolder, $request->getArtsList($items));
                                            break;
            case 'itemlist-items' :
                                            //@TODO view und filter
                                            $items = $request->getItems();
                                            // @TODO hier noch die Config filter auswerten
                                            $html = View::createHtml($placeHolder, $items);
                                            break;
            case 'itemedit' :
                                            $item = $request->getUpdatedActionItem();
                                            $html = View::createHtml($placeHolder, array($item));
                                            break;
            case 'body-content' :
                                            $action = $request->getProperty("action", Request::REQUEST_ACTION_DEFAULT);
                                            $html = self::replacePlaceHolders($action, $request);
                                            break;
            case 'page-class' :
                                            $html = $request->getProperty("action", Request::REQUEST_ACTION_DEFAULT);
                                            break;
        }

        // #### PLACE_HOLDER_CONFIG im Template ersetzen
        $configItem = $request->getConfig();
        $configItem->setProperty(Properties_Interface::PLACE_HOLDER_CONFIG, Properties_Interface::PLACE_HOLDER_PROPERTY_NAME);
        $html = self::replacePlaceHoldersVALUE($html, $configItem);

         // #### PLACE_HOLDER_PROPERTY im Template ersetzen
        $regexp = "/REPLACED_BY_PROPERTY_([0-9a-zA-Z\-_]+)_VALUE/";
        preg_match_all($regexp, $html, $matches);
        $foundPlaceHolders = (count($matches) > 0) ? $matches[1] : array();

        // #### Rekursiv im erzeugten HTML nach REPLACED_BY suchen, das HTML dafuer generieren und diese dann erstzen
        $properties = new Properties();
        foreach($foundPlaceHolders as $foundPlaceHolder) {
            $placeHolderHtml = self::replacePlaceHolders($foundPlaceHolder, $request);
            $properties->setProperty($placeHolderHtml, $foundPlaceHolder);
        }
        $html = self::replacePlaceHoldersVALUE($html, $properties);
        return $html;

        /*
        // JSON:
        $data = array("items-json" => array());
        foreach($items as $item) {
            $data["items-json"][] = $item->getProperties();
        }
        //$data["items-json"] = $items;
        header('Content-Type:json');
        $result = json_encode($data);
        */
    }


    /**
     * Ersetze REPLACED_BY_[ITEM|CONFIG|PROPERTY]_name_VALUE Zeichenfolgen in einem html string
     * mit den Werten der entsprechenden Eigenschaften des gegebenen properties Objekts.
     */
    static private function replacePlaceHoldersVALUE(string $html, Properties_Interface $properties) : string {
      // ITEM|CONFIG|PROPERTY
      $placeHolderType = $properties->getProperty(Properties_Interface::PLACE_HOLDER_PROPERTY_NAME, Properties_Interface::PLACE_HOLDER_PROPERTY_DEFAULT);
      $regexp = "/REPLACED_BY_${placeHolderType}_([0-9a-zA-Z\-_]+)_VALUE/";
      preg_match_all($regexp, $html, $matches);
      $names = (count($matches) > 0) ? $matches[1] : array();
      foreach($names as $name) {
         $html = str_replace("REPLACED_BY_${placeHolderType}_{$name}_VALUE", $properties->getProperty($name, ""), $html);
      }
      return $html;
    }
}