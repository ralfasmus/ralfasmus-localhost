<?php

/**
 *
 * SingleInstance aktuelle Request.
 * @author asmusr
 */
class Request extends ObjectAbstract {

  static private $singleInstance = null;
  private $messages = array();
  private $messageLevel = 'debug';


    private function actionItemEdit()
    {
        $newId = Persistence::newId();
        $instance = Persistence::getOrCreateInstance($this->getProperty("id", $newId));

        // HTML
        $html = file_get_contents(APPLICATION_PHP_DIR . DIRECTORY_SEPARATOR . "view/item-textnote-edit.html");
        $properties = $instance->getProperties();
        foreach($properties as $name => $value) {
            $html = str_replace("REPLACED_BY_ITEM_{$name}_VALUE", $value, $html);
        }
        return $html;
    }

    public function getStatus() {

        // deleted | backup | active
        return $this->getProperty("status", "active");
    }

    private function actionItemList()
    {

        // Html Header der Liste REPLACED_BY_ITEMLIST_TEXTNOTE_HEADER_ITEM_CONFIG

        // HTML itemlist-textnote
        $instance = Persistence::getOrCreateInstance($this->getProperty("config", ""));
        $itemHtml = file_get_contents(APPLICATION_PHP_DIR . DIRECTORY_SEPARATOR . "view/itemlist-textnote-header-item-config.html");
        $properties = $instance->getProperties();
       foreach($properties as $name => $value) {
            $itemHtml = str_replace("REPLACED_BY_ITEM_{$name}_VALUE", $value, $itemHtml);
        }

        // HTML itemlist-textnote
        $instances = Persistence::getInstances("textnote", "name", false);
        $result = file_get_contents(APPLICATION_PHP_DIR . DIRECTORY_SEPARATOR . "view/itemlist-textnote.html");
        $result = str_replace("REPLACED_BY_ITEMLIST_TEXTNOTE_HEADER_ITEM_CONFIG", $itemHtml, $result);

        $itemTemplate = file_get_contents(APPLICATION_PHP_DIR . DIRECTORY_SEPARATOR . "view/itemlist-textnote-item-textnote.html");
        $itemsTextnote = "";
        $nr = 0;
        $maxnum = $this->getProperty("maxnum", 99999);
        $firstnum = $this->getProperty("firstnum", 0);
        $filter = $this->getProperty("filter", "");
        $arts = array();
        foreach($instances as $instance) {
            $nr++;
            if($nr < $firstnum || $nr > $maxnum + $firstnum) continue;
            $itemHtml = $itemTemplate;
            $properties = $instance->getProperties();
            $dataFilterTextValue = "";
            foreach($properties as $name => $value) {
                $dataFilterTextValue .= "$value ";
            }
            $properties["data-filter-text"] = html_entity_decode(str_replace(array('"', "'","&nbsp;"), array(" ", " ", " "), strip_tags($dataFilterTextValue)));
            $properties["data-tooltip-html"] = str_replace('"', "'", $properties["text"]);
            foreach($properties as $name => $value) {
                $itemHtml = str_replace("REPLACED_BY_ITEM_{$name}_VALUE", $value, $itemHtml);
                $itemHtml = str_replace("REPLACED_BY_ITEM_NR", $nr, $itemHtml);
            }
            if ($filter != "" && strpos($properties["data-filter-text"],$filter) === false) continue;
            $itemsTextnote .= $itemHtml;

            // arts liste
            $art = $properties['art'];
            $artArray = explode(" ", $art);
            foreach($artArray as $art) {
                $arts[trim($art, " .")] = "";
            }
        }
        ksort($arts);
        $artList = implode("</div><div class='dvz-js-artlist__item'>", array_keys($arts));
        $result = str_replace("REPLACED_BY_ITEMS_TEXTNOTE", $itemsTextnote, $result);
        $result = str_replace("REPLACED_BY_ARTS", "<div class='dvz-js-artlist__item'>$artList</div>", $result);

        // JSON
        /*
        $data = array("instances-json" => array());
        foreach($instances as $instance) {
            $data["instances-json"][] = $instance->getProperties();
        }
        //$data["instances-json"] = $instances;
        header('Content-Type:json');
        $result = json_encode($data);
        */

        return $result;
    }

    public function getResponse()  {

        $html = "";
        try {
            $action = $this->getProperty("action", "itemlist");
            $html = file_get_contents(APPLICATION_PHP_DIR . DIRECTORY_SEPARATOR . "view/index.html");

            $bodyHtml = "";
            switch ($action) {
                case "item-edit" :
                    $bodyHtml = $this->actionItemEdit();
                    break;
                case "item-delete" :
                    $html = "REPLACED_BY_BODY_CONTENT";
                    $bodyHtml = $this->processRequest($action);
                    break;
                case "item-save" :
                    $html = "REPLACED_BY_BODY_CONTENT";
                    $bodyHtml = $this->processRequest($action);
                    break;
                default:
                    $bodyHtml = $this->actionItemList();
            }

            $html = str_replace("REPLACED_BY_BODY_CONTENT", $bodyHtml, $html);
            $html = str_replace("REPLACED_BY_PAGE", $action, $html);

            /* Config in HTML einfuegen. Config soll nur das HTML beeinflussen.
             * wird config fuer actions wie save benoetigt. so muessen die Parameter clientseitig aus dem Config HTML ausgelesen
             * und in die submitted Form integriert werden.
             */
            $configId = $this->getProperty("config", Persistence::newId());
            $instance = Persistence::getOrCreateInstance($configId);
            $properties = $instance->getProperties();
            foreach ($properties as $name => $value) {
                $html = str_replace("REPLACED_BY_CONFIG_{$name}_VALUE", $value, $html);
            }
            // Nicht ersetzte value="REPLACED_BY" HTML Attribute von Items leeren:
            $html = preg_replace("/REPLACED_BY_CONFIG_([^_]*)_VALUE/i", "", $html);
            $html = preg_replace("/REPLACED_BY_ITEM_([^_]*)_VALUE/i", "", $html);

            Log::info("Done!!!");
        } catch (Throwable $throwable) {
            Log::throwError($throwable);
        }
        $html .= Log::getConsoleLog();

        return ($html);
    }

    /**
   * POST/GET Request verarbeiten.
   */
  public function processRequest($action) {
    $html="Processing Request on server ... ";
    $request = $this;
    $properties = $request->getProperties();
    $id = $request->getProperty("id", "");
    if ($id != "") {
      $instance = Persistence::getOrCreateInstance($id);
      $instance = Persistence::updateInstanceFromRequest($instance);
      if ($action == "item-save") {
        $html .= "SAVING";
        Persistence::saveInstance($instance);
        Persistence::backupInstance($instance);
      }
      if ($action == "item-delete") {
        Persistence::deleteInstance($instance);
      }
      if ($action == "item-backup") {
        Persistence::backupInstance($instance);
      }
      if ($action == "item-recover") {
        //Persistence::deleteInstance($instance);
      }
    } else {
        $html .= var_export($properties, true);
    }
    return $html;
  }

  public function addMessage($s) {
    $this->messages[] = $s;
  }

  public function setMessageLevel($s) {
    $this->messageLevel = $s;
  }

  public function getMessage() {
    return implode("\n", $this->messages);
  }

  public function getMessageLevel() {
    return $this->messageLevel;
  }

  /**
   * Erzeugt einen Request
   */
  static public function getSingleInstance($requestProperties = array()) {
    if (self::$singleInstance == null) {
      self::$singleInstance = new Request();
      foreach ($requestProperties["post"] as $name => $value) {
        self::$singleInstance->setProperty($value, $name);
      }
      foreach ($requestProperties["get"] as $name => $value) {
        self::$singleInstance->setProperty($value, $name);
      }
    }
    return self::$singleInstance;
  }

  public function getDecodedProperty($key, $default = "exception") {
    return rawurldecode(parent::getProperty($key, $default));
  }

  public function isSubmit() {
    return $this->getProperty("submit", "nix submit") != "nix submit";
  }

}
