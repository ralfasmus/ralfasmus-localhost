<?php

/**
 * Datum/Zeitfunktionen
 */
class Zeit {

  /**
   * @var \DateTime
   */
  static private $requestStartzeit = null;

  /**
   * Liefert DateTime Instance fuer "tt.mm.jjjj" Datum String oder null.
   * @param string $datum
   * @return \DateTime
   */
  static public function getDateTimeFromDatumField($datum) {
    if ($datum) {
      return new \DateTime($datum);
    } else
      return null;
  }

  /**
   * Liefert das Datum in der Form DD.MM.YYYY.
   * @param \DateTime $dateTime Datum. Wenn null, dann liefere den default.
   * @param type $default
   * @return string
   */
  public static function datumDmy($dateTime = null, $default = "") {
    return self::datumString($dateTime, "d.m.Y", $default);
  }

  /**
   * Liefert das Datum in der Form YYYY-MM-DD-HH-MM-SS
   * @param \DateTime $dateTime Datum. Wenn null, dann liefere den default.
   * @param type $default
   * @return string
   */
  public static function datumYmdHis($dateTime = null, $default = "") {
    return self::datumString($dateTime, "Y-m-d-H-i-s", $default);
  }

  /**
   * Liefert als "heutiges Datum" gesetztes Datum als DateTime Objekt.
   * @return \DateTime
   */
  public static function heute() {
    return new \DateTime();
  }

  /**
   * Lifert $dateTime formatiert. Ist $dateTime null, wird $default formatiert
   * geliefert, wenn $default ein DateTime Objekt ist. Sonst wird $default
   * geliefert.
   * @param \DateTime $dateTime
   * @param string $format Format-Angabe entsprechend php DateTime oder php strftime
   * @param type $default
   * @return \DateTime
   */
  static private function datumString($dateTime, $format, $default = "") {
    if (!$dateTime) {
      if (is_a($default, "DateTime")) {
        $dateTime = $default;
      } else {
        return $default;
      }
    }
    if (strpos($format, "%") !== FALSE) {
      // strftime format
      // siehe http://php.net/manual/de/function.strftime.php
      return strftime($format, $dateTime->getTimestamp());
    } else {
      // DateTime format
      return $dateTime->format($format);
    }
  }

  /**
   * Setzt Zeit fuer alle weiteren Datum/Zeit-
   * Funktionen dieser Klasse.
   */
  static public function setRequestStartzeit() {
    assert(self::$requestStartzeit == null);
    self::$requestStartzeit = new \DateTime();
  }

  /**
   * Liefert die Startzeit des Request,
   * muss also zu Beginn des Request einmal
   * aufgerufen werden.
   *
   * @return string
   */
  static public function time($format = "H:i:s") {
    assert(self::$requestStartzeit != null);
    return self::$requestStartzeit->format($format);
  }

  static public function milliseconds() {
    $mt = explode(' ', microtime());
    return ((int) $mt[1]) * 1000 + ((int) round($mt[0] * 1000));
  }

}
