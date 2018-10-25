<?php
/**
 * Created by IntelliJ IDEA.
 * User: asmusr
 * Date: 24.10.2018
 * Time: 10:16
 */

class File
{
    private $filename = "";
    private $fileset = null;

    public function __construct($filename, Fileset $fileset)
    {
        $this->filename = $filename;
        $this->fileset = $fileset;
    }

    private function getTitle() {
        return str_ireplace(array(".jpg",".jpeg",".mp4",".mov",".png",".gif"),"", str_ireplace("_", " ", $this->filename));
    }

    private function getHref() {
        return $this->fileset->getFilesHref() . "/" . $this->filename;
    }

    public function html() {
        $title = $this->getTitle();
        $href = $this->getHref();
        echo "<img src='$href' data-image='$href' data-description='$title'>\n";
    }
}