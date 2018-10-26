<?php

// Verzeichnis, in dem www, php u.a. liegt
define("ROOT_DIR",dirname(realpath("."),4));
define("APPLICATION_NAME",basename(realpath(".")));
define("BILDER_ROOT_DIR",realpath(".") . DIRECTORY_SEPARATOR . "bilder");
define("HREF_FILESETS_ROOT", "http://ralfwork.localhost:90/bild/priv/galerie/bilder");

require_once("local.version.inc.php");
require_once(APPLICATION_PHP_DIR . DIRECTORY_SEPARATOR . "index.inc.php");

$request = \Allgemein\Request::getSingleInstance();
$fileset = new Fileset(HREF_FILESETS_ROOT, BILDER_ROOT_DIR, $request->getProperty("directory", ""));

?><!DOCTYPE html>
<html lang="de">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

  <!-- Image Gallery: http://unitegallery.net/index.php?page=documentation#installing_the_gallery -->
  <!-- https://github.com/vvvmax/unitegallery -->
  <!-- ----------------------------------------------------------------------------------------- --->
		<!-- <script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js'></script> -->
    <!-- <script type='text/javascript' src='/bild/cssjs/version-edit/unitegallery/js/jquery-11.0.min.js'></script> -->
    <script src="/bild/cssjs/version-edit/jquery-ui-1.12.1.custom/external/jquery/jquery.js"></script>

		<script type='text/javascript' src='/bild/cssjs/version-edit/unitegallery/js/unitegallery.min.js'></script>

		<link rel='stylesheet' href='/bild/cssjs/version-edit/unitegallery/css/unite-gallery.css' type='text/css' />
		<script type='text/javascript' src='/bild/cssjs/version-edit/unitegallery/themes/default/ug-theme-default.js'></script>
		<link rel='stylesheet' href='/bild/cssjs/version-edit/unitegallery/themes/default/ug-theme-default.css' type='text/css' />

    <title><?php echo $fileset->getShortTitle(); ?></title>
	</head>

  <body>
    <a id="pagetop"></a>
    <div id="page" class=container-fluid">
        <h1>Test Anfang</h1>
        <?php
             $fileset->html();
        ?>

        <h1>Test Ende</h1>
      </div>

    </div>
    <script type="text/javascript">

			jQuery(document).ready(function(){
				jQuery("#gallery").unitegallery();
			});

		</script>
  </body>
</html>