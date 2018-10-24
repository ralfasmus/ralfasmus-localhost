<?php
/**
 * Created by IntelliJ IDEA.
 * User: asmusr
 * Date: 24.10.2018
 * Time: 10:16
 */

class Bild
{
    static public function html($hrefBase, $prefixPath, $filename) {
        $title = str_replace("_", " ", $filename);
        $prefix = str_replace("_", " ", $prefixPath);
        $prefix = str_replace(DIRECTORY_SEPARATOR, " ", $prefix);
        echo "<img src='$hrefBase/$filename' data-image='$hrefBase/$filename' data-description='$title'>\n";
    }
}