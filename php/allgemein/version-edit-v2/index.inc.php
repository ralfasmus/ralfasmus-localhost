<?php


$vhost = $_SERVER['HTTP_HOST'];
$vhost = strpos($vhost, ":") === false ? $vhost : substr($vhost, 0, strpos($vhost, ":"));
define('VHOST_DOMAIN', $vhost);

//error_reporting(E_ALL);
error_reporting(E_ALL | E_STRICT);
setlocale(LC_TIME, 'de_DE.utf8'); // Locale muss auf dem Server installiert sein (vbox!). Fuer Ausgabe Wochentag
ini_set("display_startup_errors", "On");
ini_set("display_errors", "On");
// um ein Jahr ARB Daten zu verarbeiten, wird ein hoeheres memory limit benoetigt:
ini_set('memory_limit', '4134217728');

include_once('NamespaceAutoloader.php');
NamespaceAutoloader::setApplicationPhpDir(APPLICATION_PHP_DIR);
spl_autoload_register(array('NamespaceAutoloader', 'autoload'));

$phpProperties = array();
// Die Reihenfolge ist entscheidend!
// wer zuerst steht, ueberschreibt andere, wenn der
// request nach einer property gefragt wird.
foreach (array(
                 "server" => $_SERVER,
                 "get" => $_GET,
                 "post" => $_POST,
                 "session" => &$_SESSION,
                 "cookie" => &$_COOKIE,
                 "files" => &$_FILES,
                 "request" => &$_REQUEST,
         ) as $arrayName => $arr) {
    if (is_array($arr)) {
        $phpProperties[$arrayName] = $arr;
    }
}
