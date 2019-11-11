<?php
class NamespaceAutoloader
{
    // Basisverzeichnis fuer den "Allgemein" Namespace
    const BASE_DIR_DEFAULT = __DIR__;
    // Hier wird die Dateierweiterung bestimmt, die jede Datei mit einer PHP-Klasse haben muss.
    const FILE_EXTENSION = '.php';

    static private $applicationPhpDir = "";

    public static function autoload($className) {
        // $className enthält die Namespaces (hier zum Beispiel "Autoloadertest\Test")
        // Nur unter Windows ist "\" ein erlaubtes Trennzeichen für Verzeichnisse, daher muss
        // es an den Systemstandard angeglichen werden (unter Linux etwa zu "Autoloadertest/Test")
        $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
        if(stripos($className, "Allgemein") === 0) {
            $filePath = NamespaceAutoloader::BASE_DIR_DEFAULT . DIRECTORY_SEPARATOR . $className . NamespaceAutoloader::FILE_EXTENSION;
        } else {
            $filePath = NamespaceAutoloader::$applicationPhpDir . DIRECTORY_SEPARATOR . $className . NamespaceAutoloader::FILE_EXTENSION;
        }
        if (file_exists($filePath)) {
            // Datei zur Klasse includen, falls sie denn existiert
            include_once($filePath);
        }
    }

    /**
     * Basisverzeichnis in dem wiederum die nach Namespaces benannten Verzeichnisse liegen
     * Der Wert soll nicht auf einem "/" enden
     * @param $baseDir Bsp: ROOT_DIR . "/php/memo/" . APPLICATION_VERSION_PHP
     */
    public static function setApplicationPhpDir($applicationPhpDir)
    {
        self::$applicationPhpDir = $applicationPhpDir;
    }
}