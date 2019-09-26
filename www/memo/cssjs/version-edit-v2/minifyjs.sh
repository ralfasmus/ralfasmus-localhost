#!/bin/bash

THIS_FILE=`basename "$0"`
NOW=`date +%Y-%m-%d_%H-%M-%S`
BUILD_HINT="/*! build-hinweis: Datei generiert am $NOW von $THIS_FILE */"
echo "Bereite JS Dateien fuer Einbinden in der Website auf"
echo "Dateien sind mit einem Build-Hinweis inkl. Zeitstempel am Anfang der Datei gekennzeichnet: $BUILD_HINT"

echo "" > js/min.js
cat lib/moment-js/moment-with-locales.js >> js/min.js
echo "" >> js/min.js
cat lib/jquery-3.3.1/node_modules/jquery/dist/jquery.min.js >> js/min.js
echo "" >> js/min.js
cat lib/jquery-ui-1.12.1.custom/jquery-ui.min.js >> js/min.js
echo "" >> js/min.js
cat lib/tocify-js/jquery.tocify.min.js >> js/min.js
echo "" >> js/min.js
cat lib/popper-1.14.7/node_modules/popper.js/dist/umd/popper.min.js >> js/min.js
echo "" >> js/min.js
cat lib/bootstrap-4.3.1/node_modules/bootstrap/dist/js/bootstrap.min.js >> js/min.js
echo "" >> js/min.js
cat lib/summernote-0.8.12-dist/dist/summernote-bs4.min.js >> js/min.js
echo "" >> js/min.js
cat lib/bs4-custom-file-input/dist/bs-custom-file-input.min.js >> js/min.js
echo "" >> js/min.js

# mein neues JS:
cat application/javascript/helpers.js >> js/min.js
echo "" >> js/min.js
cat application/javascript/datetime.js >> js/min.js
echo "" >> js/min.js
cat application/javascript/ajax.js >> js/min.js
echo "" >> js/min.js
cat application/javascript/init.js >> js/min.js
echo "" >> js/min.js
cat application/javascript/events.js >> js/min.js

echo "$BUILD_HINT" >>  "js/min.js"
echo "Done...................";
