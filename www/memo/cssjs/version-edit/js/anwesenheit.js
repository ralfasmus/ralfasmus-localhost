
/**
 * Liefert Pausenminuten wie gesetzt oder berechnet, wenn Feld leer.
 * @param {Number} diffMinutes Die Anwesenheit Differenz in Minuten. Bsp: 360
 * @param {string} pauseMinutes Die eingetragene Pausenzeit in Minuten. Bsp: '15' oder ''
 * @returns {Number} Pause in Minuten
 */
function pauseMinutes(diffMinutes, pauseMinutes) {
    var defaultSubMinutes = diffMinutes >= 435 ? 45 : (diffMinutes >= 375 ? 30 : (diffMinutes >= 255 ? 15 : 0));
    var subMinutes = (pauseMinutes == '' ? defaultSubMinutes : pauseMinutes);
    return subMinutes;
}

/**
 * Liefert in Abhaengigkeit von der abgezogenen Pause eine CSS Klasse. Ist die berechnete Pause bei 15 min AZ weniger
 * == 0 dann ROT.
 * @param start Startzeit als Date
 * @param end Ende-Zeit als Date
 * @returns {String} CSS Klasse für Anwesenheit Felder
 */
function anwesenheitCss(start, end) {
  var diffMinutes = diffInMinuten(start, end);
  var calculatedPauseMinutes1 = pauseMinutes(diffMinutes, '');
  var calculatedPauseMinutes2 = pauseMinutes(diffMinutes - 15, '');
  return (calculatedPauseMinutes1 > calculatedPauseMinutes2) ? "red" : "";
}

/**
 * Berechnet aus einem Wert, der aus dem Feld "Zeiten" stammt, die Differenz
 * in Minuten.
 * @param start Startzeit als Date
 * @param end Ende-Zeit als Date
 * @returns {Number|String} Differenz der Zeiten in min
 */
function diffInMinuten(start, end) {
  var diff = new Date(end - start);
  // Pause ab 4:15h: 15 min = 0.25 / ab 6:15h : 30 min = 0.5 / ab 7:15h : 45 min = 0.75 h abziehen
  var diffMinutes = (diff.getHours() * 60) - 60 + diff.getMinutes();
  return diffMinutes;
}

/**
 * Liefert Start- oder Endzeit eines Zeitraums. Wenn der Zeitraum nur die Startzeit enthaelt und
 * diese gefragt ist, dann wird sie auch geliefert.
 * @param {type} zeiten Bsp: 6-15.15 oder 6.00 oder 6.00-
 * @param getStartDate
 * @returns {*}
 */
function getDateFromZeitraum(zeiten, getStartDate) {

    if (!isString(zeiten) || zeiten == '') {
        return '';
    }
    var splitted = zeiten.split('-');
    var t1 = splitted[0];
    if (getStartDate) {
        if (isNaN(t1)) {
            return '';
        }
        t1 = t1.indexOf('.') == -1 ? t1 + '.00' : t1;
        var t1a = t1.split('.');
        var d1 = new Date(0, 0, 0, t1a[0], t1a[1]);
        return d1;
    } else {
        if (splitted.length != 2 || splitted[1] == '') {
            return '';
        }
        var t2 = splitted[1];
        if (isNaN(t2)) {
            return '';
        }
        t2 = t2.indexOf('.') == -1 ? t2 + '.00' : t2;
        var t2a = t2.split('.');
        var d2 = new Date(0, 0, 0, t2a[0], t2a[1]);
        return d2;
    }
}

