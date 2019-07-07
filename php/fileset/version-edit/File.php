<?php
/**
 * Created by IntelliJ IDEA.
 * User: asmusr
 * Date: 24.10.2018
 * Time: 10:16
 */

/**
 * Stellt ein File dar.
 */
class File
{
    /**
     * @var string Name der Datei innerhalb ihres Verzeichnisses (ohne Pfad).
     */
    private $fileName = "";
    /**
     * @var Fileset|null Fileset Objekt des Verzeichnisses, in dem die Datei liegt.
     */
    private $fileset = null;

    public function __construct($fileName, Fileset $fileset)
    {
        $this->fileName = $fileName;
        $this->fileset = $fileset;
    }

    /**
     * @return mixedLiefert fuer den Filename einen besser lesbaren String.
     */
    static private function fileName2Title($fileName) {
        return str_ireplace(array(".jpg",".jpeg",".mp4",".mov",".png",".gif"),"", str_ireplace("_", " ", $fileName));
    }

    private function getRequest() {
        return \Allgemein\Request::getSingleInstance();
    }

    /**
     * @return mixed
     */
    private function getTitle() {
        $titlePattern = $this->getRequest()->getProperty(Fileset::GET_KEY_FILETITLE, "FILENAME");
        $titlePattern = str_ireplace("FILENAME2TITLE", self::fileName2Title($this->fileName), $titlePattern);
        $titlePattern = str_ireplace("FILENAME", $this->fileName, $titlePattern);
        $titlePattern = str_ireplace("DIRNAME", $this->fileset->getShortTitle(), $titlePattern);
        $titlePattern = str_ireplace("DIRPATH",  $this->fileset->getTitle(), $titlePattern);
        return $titlePattern;
    }

    /**
     * Kompletter href des File Objekts.
     * @return string
     */
    private function getHref() {
        return $this->fileset->getFilesHref() . "/" . $this->fileName;
    }

    /**
     * HTML fuer das File ausgeben.
     */
    public function html() {
        $title = $this->getTitle();
        $href = $this->getHref();
        echo "<img alt='$title' src='$href' data-image='$href' data-description='2nd $title'>\n";
    }
}