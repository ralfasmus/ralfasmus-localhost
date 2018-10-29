<?php

// --------- DEFINE --------------------------------------------------------------------------

// Verzeichnis, in dem www, php u.a. liegt
define("ROOT_DIR",dirname(realpath("."),4));
define("APPLICATION_NAME",basename(realpath(".")));
define("FILESETS_ROOT_DIR",realpath(".") . DIRECTORY_SEPARATOR . "filesets");
define("HREF_FILESETS_ROOT", "http://ralfwork.localhost:90/fileset/priv/media/filesets");

// --------- INCLUDE -------------------------------------------------------------------------

require_once("local.version.inc.php");
require_once(APPLICATION_PHP_DIR . DIRECTORY_SEPARATOR . "index.inc.php");

// --------- INIT ----------------------------------------------------------------------------
$request = \Allgemein\Request::getSingleInstance();
$directory              = $request->getProperty("directory", "/");
$directoryName    = substr($directory, strrpos($directory, "/") + 1);
$directoryName = $directoryName === false ? "" : $directoryName;
$relativeDirectoryPath = substr($directory, 0, strrpos($directory, "/"));
$fileset = new Fileset(HREF_FILESETS_ROOT, FILESETS_ROOT_DIR, $relativeDirectoryPath, $directoryName);

$filesetTitle           = $fileset->getTitle();
$filesetShortTitle      = $fileset->getShortTitle();
$pageTitle              = $filesetShortTitle;
$darstellung            = $request->getProperty(Fileset::GET_KEY_DARSTELLUNG, "compact"); // unitegallery hat auch ein "default" schema!
$levels                 = 0 + $request->getProperty(Fileset::GET_KEY_LEVELS, "1");
$cssjsPath              = "/fileset/cssjs/" . APPLICATION_VERSION_CSSJS;
if ($levels > 1) {
    $request->setProperty("DIRNAME", Fileset::GET_KEY_FILETITLE);
}

// --------- HTML AUSGABE --------------------------------------------------------------------

require_once("include/$darstellung.head.1.inc.php");
?>
<body>
    <a id="pagetop"></a>
    <div id="page" class=container-fluid">
        <div class="menu">&nbsp;&nbsp;
            <span class="title"><?php echo $filesetTitle; ?></span>&nbsp;&nbsp;
            <a href="<?php echo $fileset->getFilesetHref(array(), $relativeDirectoryPath); ?>">zurück</a>&nbsp;
            <a href="<?php echo $fileset->getFilesetHref(array(Fileset::GET_KEY_DARSTELLUNG => "compact")); ?>">compact</a>
            <a href="<?php echo $fileset->getFilesetHref(array(Fileset::GET_KEY_DARSTELLUNG => "tiles-nested")); ?>">tiles nested</a>
            <a href="<?php echo $fileset->getFilesetHref(array(Fileset::GET_KEY_DARSTELLUNG => "tiles-justified")); ?>">tiles justified</a>
            <a href="<?php echo $fileset->getFilesetHref(array(Fileset::GET_KEY_DARSTELLUNG => "grid")); ?>">grid</a>
        </div>
        <?php $fileset->html($levels); ?>
    </div>
<?php
require_once("include/$darstellung.body.1.inc.php");
?>
</body>
</html>