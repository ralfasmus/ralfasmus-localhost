#!/bin/bash

# Build Script fuer SCSS Dateien
#
# Voraussetzungen:
# npm.cmd install -g sass
# 1.17.3
#
# ##### Build Hinweis #############################

THIS_FILE=`basename "$0"`
NOW=`date +%Y-%m-%d_%H-%M-%S`
BUILD_HINT="/*! build-hinweis: Datei generiert am $NOW von $THIS_FILE */"
echo "Bereite CSS Dateien fuer Einbinden in der Website auf"
echo "Minifizierte Dateien sind mit einem Build-Hinweis inkl. Zeitstempel am Anfang der Datei gekennzeichnet: $BUILD_HINT"

echo "Es entsteht css/min.css"
sass build.scss --style compressed "css/min.css"
echo "" >>  "css/min.css"
echo "$BUILD_HINT" >>  "css/min.css"

echo "Done..................";
