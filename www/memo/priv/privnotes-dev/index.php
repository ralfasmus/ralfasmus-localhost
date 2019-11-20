<?php

try {
    // Verzeichnis, in dem www, php u.a. liegt
    define("ROOT_DIR",dirname(realpath("."),4));
    define("APPLICATION_NAME",basename(realpath(".")));
    define("APPLICATION_VERSION_PHP","version-edit-v2");
    define("LOG_LEVEL", 4 ); // DEBUG

    // Verzeichnis fuer PHP-Klassen der Application: ralfwork.localhost/php/memo/version-XXXXXXXXX
    define('APPLICATION_PHP_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . "memo" . DIRECTORY_SEPARATOR . APPLICATION_VERSION_PHP);

    $vhost = $_SERVER['HTTP_HOST'];
    $vhost = strpos($vhost, ":") === false ? $vhost : substr($vhost, 0, strpos($vhost, ":"));
    define('VHOST_DOMAIN', $vhost);

    //error_reporting(E_ALL);
    error_reporting(E_ALL | E_STRICT);
    setlocale(LC_TIME, 'de_DE.utf8'); // Locale muss auf dem Server installiert sein (vbox!). Fuer Ausgabe Wochentag
    ini_set("display_startup_errors", "On");
    ini_set("display_errors", "On");
    ini_set("assert.exception", 1);
    // um ein Jahr ARB Daten zu verarbeiten, wird ein hoeheres memory limit benoetigt:
    ini_set('memory_limit', '4134217728');

    /**
     * Autoloading, Namespace Initialization
     */
    include_once(APPLICATION_PHP_DIR . '/NamespaceAutoloader.php');
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
    $request = Request::createSingleInstance($phpProperties);
    echo $request->getResponse();
} catch (Throwable $t) {
    $br = "<br/>";
    echo "<h3>";
    echo "Fehler ist aufgetreten!$br";
    echo "Error Message: " . $t->getMessage()  .  $br;
    echo "in " . $_SERVER['PHP_SELF'] . $br;
    echo "</h3>";
    echo "<pre>";
    echo $t->getTraceAsString();
    echo var_export($phpProperties, true) . PHP_EOL;
    echo "</pre>";
} finally {

}