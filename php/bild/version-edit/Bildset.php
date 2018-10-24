<?php
/**
 * Created by IntelliJ IDEA.
 * User: asmusr
 * Date: 24.10.2018
 * Time: 10:25
 */

class Bildset
{
    private $title = array();
    private $href = array();
    private $prefixPath = "";
    private $hrefBase = "";

    public function __construct($hrefBase, $prefixPath)
    {
        $this->prefixPath = $prefixPath;
        $this->hrefBase = $hrefBase;
    }

    public function addBild($filename) {

    }

    static public function html($hrefBase, $prefixPath, $filename) {
        $title = str_replace("_", " ", $filename);
        $prefix = str_replace("_", " ", $prefixPath);
        $prefix = str_replace(DIRECTORY_SEPARATOR, " ", $prefix);
        echo "<img src='$hrefBase/$filename' data-image='$hrefBase/$filename' data-description='$title'>\n";
    }
}