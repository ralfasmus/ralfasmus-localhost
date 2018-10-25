<?php
/**
 * Created by IntelliJ IDEA.
 * User: asmusr
 * Date: 24.10.2018
 * Time: 10:25
 */

/**
 * Stellt ein Verzeichnis und alle Unterverzeichnisse dar.
 */
class Fileset
{
    private $directoryName = "";
    private $filesetBaseHref = "";
    private $filesetBaseDirectory = "";
    private $files = array();
    private $filesets = array();

    /**
     * Fileset constructor.
     * @param $filesetBaseHref
     * @param $filesetBaseDirectory
     * @param $directoryName
     */
    public function __construct($filesetBaseHref, $filesetBaseDirectory, $directoryName)
    {
        $this->filesetBaseHref = $filesetBaseHref;
        $this->filesetBaseDirectory = $filesetBaseDirectory;
        $this->directoryName = $directoryName;
    }

    public function addFile($filename) {
        $this->files[] = new File($filename, $this);
    }

    public function addFileset($directory) {
        $this->filesets[] = new Fileset($this->getFilesHref(), $this->getFilesetDirectory(), $directory);
    }
    
    private function getFilesetDirectory() {
        return $this->filesetBaseDirectory . DIRECTORY_SEPARATOR . $this->directoryName;
    }

    public function getFilesHref() {
        return $this->filesetBaseHref . $this->directoryName;
    }

    /**
     * Durchsucht Verzeichnis und erstellt Files.
     * Durchsucht Sub Dirs und erstellt Sub Filesets.
     */
    private function scan() {
        list($files, $directories) = $this->getFilesAndDirectories($this->getFilesetDirectory());
        foreach($files as $file) {
            $this->addFile($file);
        }
        foreach ($directories as $directory) {
            $this->addFileset($directory);
        }
    }

    /**
     * Durchsucht ein Dir und liefert alle Dateien und Directories. 
     * @param $basePath
     * @return array
     */
    private function getFilesAndDirectories($directory) {
        $dirEntries = scandir($directory, SCANDIR_SORT_ASCENDING);
        $files = array();
        $directories = array();
        foreach ($dirEntries as $dirOrFilename) {
            if(!($dirOrFilename == "." || $dirOrFilename == "..")) {
                if (is_dir( $directory . DIRECTORY_SEPARATOR . $dirOrFilename)) {
                    $directories[] = $dirOrFilename;
                } else {
                    $files[] = $dirOrFilename;
                }
            }
        }
        return array($files, $directories);
    }

    private function getTitle() {
        return str_ireplace("_", " ", $this->directoryName);
    }

    private function getRequest() {
        return \Allgemein\Request::getSingleInstance();
    }

    /**
     * HTML Ausgabe 
     */
    public function html() {
       $this->scan();
       $request = $this->getRequest();
       echo "<h1>" . $this->getTitle() . "</h1>";
       echo "<div id='gallery' style='display:none'>";
       foreach($this->filesets as $fileset) {
           $filesetTitle = $fileset->getTitle();
           $url = $_SERVER["REQUEST_URI"];
           $query = http_build_query(array("directory" => $this->directoryName . "/" . $fileset->directoryName));
           $href = parse_url($url, PHP_URL_PATH) . "?$query";
           echo "<h3><a href='$href'>$filesetTitle</a></h3>\n";
       }
       foreach($this->files as $file) {
           $file->html();
       }
       echo "</div>";
    }
}