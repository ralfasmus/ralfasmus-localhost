<?php

/**
 * Stellt ein HTML Template dar und bietet Methoden zum nachladen inkludierter
 * Dateien/Templates und zum Materialisieren der Daten.
 *
 * @author asmusr
 */
class View {

  static private $today = null;

  /**
   * Counter zur fortlaufenden Erzeugung von CSS-IDs
   * @var type
   */
  static private $cssCounter = 1;

  static public function create() {
    return new View();
  }

  private function getRequest() {
    return Request::getSingleInstance();
  }

  /**
   * Liefert die komplette Response fuer den Request.
   * @return type JSON oder HTML
   */
  public function getResponse() {
    $request = $this->getRequest();

    $view = $request->getProperty("data-view", "exception");
    Request::getSingleInstance()->addMessage("data-view: $view");
    if (stripos($view, ".html") !== FALSE) {
      if (stripos($view, "index") !== FALSE) {
        // Ergebnis als HTML
        $html = file_get_contents(PHP_DIR . "/view/$view");
        $html = $this->replaceIncludes($html);
        return $this->materializeLoopOrInstance($html);
      } else {
        $html = file_get_contents(PHP_DIR . "/view/$view");
        $html = $this->replaceIncludes($html);
        //return $this->materializeLoopOrInstance($html);
        $material = $this->materializeLoopOrInstance($html);
        if (is_array($material)) {
          $resultHtml = $material[0];
          $resultData = $material[1];
        } else {
          $resultHtml = $material;
          $resultData = array();
        }
        $data = array("instance-html" => $resultHtml);
        $data["instances-json"] = $resultData;
        header('Content-Type:json');
        $encoded = json_encode($data);
        return($encoded);
      }
    } else {
      // Ergebnis als JSON
      $data = $this->getJsonData($view);
      header('Content-Type:json');
      return json_encode($data);
    }
  }

  /**
   * Liefert den naechsten in " eingeschlossenen String innerhalb $text ab $startPos.
   * @param type $text hierin wird gesucht
   * @param type $startPos ab hier wird gesucht
   * @param type $defaultValue Ergebnis, wenn nix gefunden
   * @return type
   */
  private function getNextQuotedString($text, $startPos, $defaultValue) {
    $a = stripos($text, '"', $startPos);
    if ($a !== FALSE) {
      $b = stripos($text, '"', $a + 1);
      if ($b !== FALSE && $b > $a) {
        return substr($text, $a + 1, $b - $a - 1);
      }
    }
    return $defaultValue;
  }

  static private function isOlder($jahr, $monat, $days) {
    $dat = new DateTime("$jahr-$monat");
    $interval = self::$today->diff($dat);
    $daysI = $interval->format('%R%a') * 1;
    $result = ($daysI < (-1 * $days)) || ($daysI > 60); // Zukuenftiges > +2 Monate ausschliessen
    return $result;
  }

  /**
   * Liefert HTML fuer Statistik ueber alle Daten (aktuell + archiv)
   * @return string
   */
  private function getStatistik() {
    $SHOW_DAYS = 365;
    self::$today = new DateTime();
    $resultHtml = "";

    // Jetzt Berechnungen pro Budget:
    $budgets = Persistence::getInstances("BUD", "name", false);
    $werte = array();
    $jahre = array();
    $budgetNames = array();
    $budgetAmount = array();
    foreach ($budgets as $budget) {
      $budgetName = $budget->getProperty("name", "unbekannt");
      $budgetNames["" . $budgetName] = "$budgetName";
      $budgetZeitraum = $budget->getProperty("zeitraum", "") == "" ? "" : " " . $budget->getProperty("zeitraum", "");
      $budgetAmount["" . $budgetName] = $budget->getProperty("btotal", "") . $budgetZeitraum;
      if (!isset($werte["$budgetName"])) {
        $werte["$budgetName"] = array();
      }
      $instances = Persistence::getInstances("ARB", "datum", false, $budget->getId(), true);
      $resultData = array();
      foreach ($instances as $instance) {
        $h = $instance->getProperty(ARB::PROPERTY_STUNDEN, 0);
        $kaz = $instance->getProperty(ARB::PROPERTY_KAZ, 0);
        list($tag, $monat, $jahr) = explode(".", $instance->getProperty(ARB::PROPERTY_DATUM, "01.01.1800"));
        if ($jahr >= "2017") {
          $jahre["" . $jahr] = "" . "$jahr";
          if (!isset($werte["$budgetName"]["" . $jahr])) {
            $werte["$budgetName"]["" . $jahr]["Jahr"] = 0;
            $werte["$budgetName"]["" . $jahr]["monate"] = array();
          }
          if (!isset($werte["$budgetName"]["" . $jahr]["monate"]["" . $monat])) {
            $werte["$budgetName"]["" . $jahr]["monate"]["" . $monat] = 0;
          }
          $wert = $kaz != "" && $h != "" ? ($kaz > $h ? $kaz : $h) : ($kaz != "" ? $kaz : ($h != "" ? $h : 0));
          $werte["$budgetName"]["" . $jahr]["monate"]["" . $monat] += $wert;
          $werte["$budgetName"]["" . $jahr]["Jahr"] += $wert;
        }
      }
    }
    ksort($jahre);
    $monatWerte = array();
    $jahresWerte = array();
    foreach ($budgetNames as $budgetName) {
      $budgetWert[$budgetName] = array();
      foreach ($jahre as $jahr) {
        foreach (array("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12") as $monat) {
          // Monatswerte nur fuer die letzten 365 Tage aufnehmen:
          if (!self::isOlder($jahr, $monat, $SHOW_DAYS)) {
            $monatWerte[$budgetName][$jahr . ""]["" . $monat] = 0;
          }
        }
        $jahresWerte[$budgetName][$jahr . ""] = 0;
      }
    }
    foreach ($werte as $budgetName => $jahrWerte) {
      foreach ($jahrWerte as $jahr => $jahrUndMonatWerte) {
        $jahresWerte[$budgetName][$jahr . ""] = $jahrUndMonatWerte["Jahr"];
        foreach ($jahrUndMonatWerte["monate"] as $monat => $wert) {
          // Monatswerte nur fuer die letzten 365 Tage aufnehmen:
          if (!self::isOlder($jahr, $monat, $SHOW_DAYS)) {
            $monatWerte[$budgetName]["" . $jahr]["" . $monat] = $wert;
          }
        }
      }
    }

    // Summen über alle ARB Instanzen berechnen (Budget unabhaengig, da ARB Instanzen
    // mehreren Budgets gleichzeitig zugeordnet sein koennen
    $monatSumme = array();
    $jahrSumme = array();
    foreach ($jahre as $jahr) {
      foreach (array("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12") as $monat) {
        // Monatswerte nur fuer die letzten 365 Tage aufnehmen:
          $monatSumme["$jahr-$monat"] = 0;
      }
      $jahrSumme["$jahr"] = 0;
    }
    $arbInstances = Persistence::getInstances("ARB", "datum", false, "", true);
    foreach ($arbInstances as $instance) {
      $h = $instance->getProperty(ARB::PROPERTY_STUNDEN, 0);
      list($tag, $monat, $jahr) = explode(".", $instance->getProperty(ARB::PROPERTY_DATUM, "01.01.1800"));
      if ($jahr >= 2017 && is_numeric($h)) {
        $jahrSumme["$jahr"] += $h;
        $monatSumme["$jahr-$monat"] += $h;
      }
    }

    // Darstellung:
    $table = "<table>";
    foreach ($monatWerte as $budgetName => $jahrWerte) {
      $table .= "<tr><td>Budget</td><td style='width: 90px;'>Max</td>";
      foreach ($jahrWerte as $jahr => $monate) {
        foreach ($monate as $monat => $wert) {
          $table .= "<td>" . $monat . "/" . ($jahr - 2000) . "</td>";
        }
      }
      foreach ($jahresWerte[$budgetName] as $jahr => $wert) {
        $table .= "<td>$jahr</td>";
      }
      break;
    }
    $table .= "</tr>";

    $trclass = "odd";
    foreach ($monatWerte as $budgetName => $jahrWerte) {
      $trclass = $trclass == "odd" ? "even" : "odd";
      $strong = "";
      foreach (array("#022", "#028", "#030", "#034", "#039", "400059", "412622") as $s) {
        if (strpos($budgetName, $s) !== false) {
          $strong = "strong";
        }
      }
      $tr = "<tr class='$trclass $strong'><td><span title='$budgetName'><span style='padding-right: 20px;'>" . substr($budgetName, 0, 80) . "</span></td>";
      $tr .= "<td>" . $budgetAmount["" . $budgetName] . "</td>";
      foreach ($jahrWerte as $jahr => $monate) {
        foreach ($monate as $monat => $wert) {
          // fuer unproduktive auch die % darstellen:
          $prozentm= (stripos($budgetName, "unproduktiv") !== false && $monatSumme["$jahr-$monat"] > 0) ? round(($wert / $monatSumme["$jahr-$monat"]) * 100, 0) . "%" : "";
          $title =  "$prozentm = $wert/" . $monatSumme["$jahr-$monat"] . " h  @ ";
          $title .=      "$jahr-$monat: $budgetName";
          $tr .= "<td title='$title' style='width: 60px;'>$wert<br><strong>$prozentm</strong></td>";
        }
      }
      foreach ($jahresWerte[$budgetName] as $jahr => $wert) {
        // fuer unproduktive auch die % darstellen:
        $prozent = $jahrSumme["$jahr"] > 0 ? round((($wert / $jahrSumme["$jahr"]) * 100), 1) . "%" : "";
        $title = (stripos($budgetName, "unproduktiv") !== false ? "$prozent = $wert/" . $jahrSumme["$jahr"] . " h" : "");
        $text = $title == "" ? "" : "<br><strong>$prozent</strong>";
        $tr .= "<td title='$title' style='width: 80px;'>$wert$text</td>";
      }
      $tr .= "</tr>";
      $table .= "$tr";
    }
    $table .= "</table>";

    return "$table";
  }

  /**
   * Ersetzt in $html alle @include= durch die entsprechenden Dateiinhalte.
   * @param type $html
   * @return type
   * @throws Exception
   */
  private function replaceIncludes($html) {
    $startPos = 0;
    while ($startPos !== FALSE) {
      $startPos = stripos($html, "@include=", $startPos);
      if ($startPos !== FALSE) {
        $filename = $this->getNextQuotedString($html, $startPos, "");
        if ($filename != "") {
          if (strpos($filename, "php") === 0) {
            // PHP funktion ausfuehren:
            if ($filename == "php-statistik-m-j") {
              $includeHtml = $this->getStatistik();
            } else {
              throw new Exception("Unbekannte php Funktion im @include: $filename");
            }
          } else if (strpos($filename, "data") === 0) {
              // DATA instance einbauen:
              if ($filename == "data_FRE_freitext1.data") {
                  $freInstance = Persistence::getOrCreateInstance("FRE_freitext1");
                  $includeHtml = $freInstance->getProperty("name", "...");
              } else {
                  throw new Exception("Unbekannte Instanz im @include: $filename");
              }
          }
          else {
            // .html Datei einbauen:
            $includeHtml = file_get_contents(PHP_DIR . "/view/$filename");
            $includeHtml=str_replace( "APPLICATION_VERSION_CSSJS", APPLICATION_VERSION_CSSJS, $includeHtml);
          }
          $html = str_replace('@include="' . $filename . '"', $includeHtml, $html);
        } else {
          throw new Exception("Kann filename / php funktion fuer @include nicht bestimmen.");
        }
      }
    }
    if (stripos($html, "@include") !== FALSE) {
      $this->replaceIncludes($html);
    }
    return $html;
  }

  /**
   * Liefert $html materialisiert zurueck. D.h. in $html werden data-loop DIVs
   * mit dem HTML der entsprechenden Instanzen gefuellt und Instanz-DIVs mit der
   * entsprechenden Instanz.
   *
   * @param type $html HTML Template, das mit Daten zu fuellen ist.
   * Z.B. ARB.html oder list-ARB.html
   * @return type
   */
  private function materializeLoopOrInstance($html) {
    $request = $this->getRequest();

    $loopType = $request->getProperty("data-loop", "");


    if ($loopType != "") {
      switch ($loopType) {
        case "BUD" : list($sort, $desc) = array(ObjectAbstract::PROPERTY_NAME, false);
          break;
        case "ANW" : list($sort, $desc) = array(ObjectAbstract::PROPERTY_ID, true);
          break;
        case "ARB" : list($sort, $desc) = array(ObjectAbstract::PROPERTY_DATUM_ID_SORT, true);
          break;
        case "NOT" : list($sort, $desc) = array(array("art",ObjectAbstract::PROPERTY_NAME), false);
          break;
        case "SON" : list($sort, $desc) = array("pos", false);
          break;
        default: list($sort, $desc) = array(ObjectAbstract::PROPERTY_ID, true);
      }
      $instances = Persistence::getInstances($loopType, $sort, $desc);
      $resultHtml = "";
      $resultData = array();
      foreach ($instances as $instance) {
        $resultHtml .= $this->materializeInstance($html, $instance);
        $id = $instance->getId();
        $resultData[$id] = $this->getJsonData("instance-data.json", $id);
      }
      $result = array($resultHtml, $resultData);
    } else {
      $id = $request->getProperty("data-instance-id", "");
      if ($id) {
        $instance = Persistence::getOrCreateInstance($id);
        $resultHtml = $this->materializeInstance($html, $instance);
      } else {
        $resultHtml = $html;
      }
      $result = $resultHtml;
    }
    return $result;
  }

  /**
   * Fuellt das Template $html mit Instanz-Daten von $instance.
   *
   * @param type $html HTML Template, z.B. ARB.html
   * @param type $instance Die Instanz mit den Daten.
   * @return type
   */
  private function materializeInstance($html, $instance) {
    $search = get_class($instance) . "id";
    $html = str_replace($search, $instance->getId(), $html);
    $html = str_replace("IDCOUNT", "cssid_" . Zeit::milliseconds() . "_" . self::$cssCounter++, $html);
    return $html;
  }

  /**
   * Liefert fuer einen bestimmten JSON View das Ergebnis (die Daten) als JSON oder
   * fuehrt eine Aktion (save usw.) mit Request Daten aus.
   * JSON Views sind nicht als Template/Datei vorhanden sondern werden direkt
   * als Liste von Objekten/Daten materialisiert.
   * @param type $view
   * @return type
   */
  private function getJsonData($view, $id = null) {
    $request = $this->getRequest();
    $result = array();
    if ($view == "save-instance.json") {
      Request::getSingleInstance()->processRequest("save");
    }
    if ($view == "delete-instance.json") {
      Request::getSingleInstance()->processRequest("delete");
    }
    if ($view == "instance-data.json") {
      if ($id == null) {
        $id = $request->getProperty("data-instance-id");
      }
      //throw new Exception("ok $id");
      $result["instance-data"] = array();
      if ($id) {
        foreach (array($id) as $id) {
          $instance = Persistence::getOrCreateInstance($id);
          foreach ($instance->getElements() as $element) {
            $result["instance-data"][] = $element->getData();
          }
        }
      }
    }
    $result["message"] = Request::getSingleInstance()->getMessage();
    $result["message-level"] = Request::getSingleInstance()->getMessageLevel();
    return $result;
  }

}
