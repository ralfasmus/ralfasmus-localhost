/**
 * Globale Variablen. Lade das hier im HTML head!
 * @type String
 */
var ID_CLASS_NAME_SEPARATOR = '_';

var testCounter = 0;
var logLevel = 0; //0/nothing 1/error 2/warning 3/info 4/debug 5/trace
var sortAscending = false;
// Daten aller Instancen um den initialen Page Load zu beschleunigen
var allInstancesData = [];
// gibt an, wieviele Views gerade geladen werden.
var loadingCounter = 0;
less = {};
less.env = 'development';
