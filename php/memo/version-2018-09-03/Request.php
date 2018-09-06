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

  /**
   * POST/GET Request verarbeiten.
   */
  public function processRequest($action) {
    $id = $this->getRequest()->getProperty("data-instance-id", "");
    if ($id != "") {
      if ($action == "save") {
        $instance = Persistence::getOrCreateInstance($id);
        foreach ($this->getRequest()->getProperties() as $name => $value) {
          if ($instance->isPersistentProperty($name)) {
            //$this->addMessage("Verarbeite Feld $name=$value");
            $instance->setProperty($value, $name);
          }
        }
        $instance->setChanged();
        Persistence::saveInstance($instance);
      }
      if ($action == "delete") {
        $archive = false;
        foreach ($this->getRequest()->getProperties() as $name => $value) {
          $archive = $archive || ($name == "archive" && $value == "1");
        }
        Persistence::deleteInstance($id, $archive);
        $archived = $archive == "1" ? "archiviert und" : "";
        $this->addMessage("Instance $archived deleted: $id");
        $this->setMessageLevel("info");
      }
    }
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
      self::$singleInstance = new Request("request");
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
