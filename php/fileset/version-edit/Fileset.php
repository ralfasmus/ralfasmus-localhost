<?php
/**
 * Created by IntelliJ IDEA.
 * User: asmusr
 * Date: 24.10.2018
 * Time: 10:25
 */

/**
 * Stellt ein Verzeichnis und evtl. alle direkten Unterverzeichnisse dar.
 */
class Fileset
{

    const GET_KEY_DARSTELLUNG = "darstellung";
    const GET_KEY_LEVELS = "levels";
    const GET_KEY_FILETITLE = "filetitle";

    /**
     * @var string Pfad ohne Name des Directories des Filesets ab/unterhalb /filesets/
     * Bsp: /fa/2018
     */
    private $relativeDirectoryPath = "";

    /**
     * @var string Name des Directories des Filesets (ohne Pfadangaben)
     * Bsp: abcdef
     */
    private $directoryName = "";

    /**
     * @var string Fuer die Bildung eines Datei-hrefs die Basis bis zum relativeDirectoryPath des Filesets
     * Bsp: http://www.xyz.de/fileset/priv/media/filesets
     */
    private $filesetBaseHref = "";

    /**
     * @var string Fuer die Bildung eines Datei-Pfads. Die Basis bis zum $relativeDirectoryPath des Filesets
     * Bsp: http://www.xyz.de/fileset/priv/media/filesets
     */
    private $filesetBaseDirectory = "";
    /**
     * @var array Alle File Objekte des Filesets, sortiert.
     */
    private $files = array();
    /**
     * @var array Alle untergeordneten Fileset Objekte des Filesets, sortiert.
     */
    private $filesets = array();

    /**
     * @var bool schon gesacanned?
     */
    private $scanned = false;
    /**
     */
    public function __construct($filesetBaseHref, $filesetBaseDirectory, $relativeDirectoryPath, $directoryName)
    {
        $this->relativeDirectoryPath = $relativeDirectoryPath;
        $this->filesetBaseHref = $filesetBaseHref;
        $this->filesetBaseDirectory = $filesetBaseDirectory;
        $this->directoryName = $directoryName;
    }

    /**
     * @param $fileName
     */
    public function addFile($fileName) {
        $newFile = new File($fileName, $this);
        $this->files[] = $newFile;
        return $newFile;
    }

    public function addFileset($directoryName) {
        $newFileset = new Fileset($this->filesetBaseHref, $this->filesetBaseDirectory,
                $this->directoryName == "" ? "" : $this->relativeDirectoryPath . "/" . $this->directoryName,
                $directoryName);
        $this->filesets[] = $newFileset;
        return $newFileset;
    }

    /**
     * @return string Kompletter Pfad zu den Inhalten des Filesets
     */
    private function getFilesetDirectory() {
        return $this->filesetBaseDirectory . $this->relativeDirectoryPath . DIRECTORY_SEPARATOR . $this->directoryName;
    }

    /**
     * @return string Komplette href-Basis bis zu den Inhalten des Filesets
     */
    public function getFilesHref() {
        return $this->filesetBaseHref . "/" . $this->relativeDirectoryPath . "/" . $this->directoryName;
    }

    /**
     * Liefert href fuer das Fileset selbst.
     *
     * @param array $getProperties GET Parameter, deren Wert gesetzt werden soll. Dabei wird ein in der aktuellen
     * URL evtl. gesetzter Wert ueberschrieben.
     *
     * @param null $directoryName Soll nicht der $directoryName dieses Filesets verwendet werden, so wird er hier
     * angegeben. Dient zur Bildung von Parent-Fileset-Adressen.
     *
     * @return string
     */
    public function getFilesetHref($getProperties = array(), $directoryName = null) {
        /**
         * Aktuelle Adresse
         */
        $url = $_SERVER["REQUEST_URI"];
        /**
         * Aktuell gesetzte GET Parameter
         */
        $getVariables = $this->getRequest()->getProperties();
        /**
         * Soll auf ein anderes Fileset verlinkt werden - dann wird hier der Parameter eingesetzt:
         */
        $getVariables["directory"] = $directoryName === null ? $this->relativeDirectoryPath . "/" . $this->directoryName : $directoryName;
        $getVariables = array_merge($getVariables, $getProperties);
        $query = http_build_query($getVariables);
        $href = parse_url($url, PHP_URL_PATH) . "?$query";
        return $href;
    }

    /**
     * Durchsucht ein Verzeichnis und erstellt File Objekte.
     * Durchsucht Sub Dirs und erstellt Child - Fileset Objekte.
     */
    private function scan() {
        if(!$this->scanned) {
            list($fileNames, $directoryNames) = $this->getFilesAndDirectories($this->getFilesetDirectory());
            foreach ($fileNames as $fileName) {
                $newFile = $this->addFile($fileName);
            }
            foreach ($directoryNames as $directoryName) {
                $newFileset = $this->addFileset($directoryName);
            }
            $this->scanned = true;
        }
    }

    /**
     * Durchsucht ein Verzeichnis und liefert alle direkten Dateien und Child-Directories als Objekte vom Typ
     * File oder Fileset.
     *
     * @param $completePath kompletter Pfad
     * @return array
     */
    private function getFilesAndDirectories($completePath) {
        $dirEntries = scandir($completePath, SCANDIR_SORT_ASCENDING);
        $fileNames = array();
        $directoryNames = array();
        foreach ($dirEntries as $dirOrFilename) {
            if(!($dirOrFilename == "." || $dirOrFilename == "..")) {
                if (is_dir( $completePath . DIRECTORY_SEPARATOR . $dirOrFilename)) {
                    $directoryNames[] = $dirOrFilename;
                } else {
                    $fileNames[] = $dirOrFilename;
                }
            }
        }
        return array($fileNames, $directoryNames);
    }

    /**
     * Kurzer Title des Filesets, z.B. fuer Browser Reiter.
     * @return mixed
     */
    public function getShortTitle() {
        return self::dir2Title(($this->directoryName));
    }

    /**
     * Langer Title des Filesets, z.B. fuer Listendarstellung oder alt-Text.
     * @return mixed
     */
    public function getTitle() {
        return self::dir2Title($this->relativeDirectoryPath . "/" . $this->directoryName);
    }

    private function getRequest() {
        return \Allgemein\Request::getSingleInstance();
    }


    /**
     * @return mixedLiefert fuer den Dirname einen besser lesbaren String.
     */
    static private function dir2Title($dir) {
        return str_ireplace("_", " ", $dir);
    }

    public function subfilesetsHtml($levels = 1) {
        $this->scan();
        if($levels == 1) {
            foreach($this->filesets as $fileset) {
                $filesetTitle = $fileset->getTitle();
                $href = $fileset->getFilesetHref();
                echo "<h3><a href='$href'>$filesetTitle</a></h3>\n";
            }
        }
    }

    /**
     * HTML fuer das Fileset ausgeben.
     *
     * @param int $levels wenn > 1, werden auch fuer die entsprechende Anzahl naechster Ebenen die Dateien
     * ausgegeben.
     */
    public function html($levels = 1) {
        $this->scan();
        if($levels == 1) {
            foreach($this->files as $file) {
                $file->html();
            }
        } else if($levels == 2) {
            foreach($this->filesets as $fileset) {
                $filesetTitle = $fileset->getTitle();
                $href = $fileset->getFilesetHref();
                //echo "<h3><a href='$href'>$filesetTitle</a></h3>\n";
                $fileset->html($levels - 1);
            }
        }
    }
}