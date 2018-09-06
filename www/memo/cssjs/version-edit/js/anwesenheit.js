/**
 * Berechnet aus einem Wert, der aus dem Feld "Zeiten" stammt, die Anwesenheit
 * in h
 * @param {type} zeiten Bsp: 6-15.15
 * @returns {Number|String} Zeit in h (Pause 45 min bereits abgezogen)
 */
function anwesenheitVonZeiten(zeiten) {
  var diffMinutes = diffInMinuten(zeiten);
  return minutesToKaz(diffMinutes) / 60;
}

/**
 * Liefert Anwesenheit in Minuten abzueglich der abzuziehenden Pause.
 * @param {type} diffMinutes Die Anwesenheit Differenz in Minuten. Bsp: 360
 * @returns {Number}
 */
function minutesToKaz(diffMinutes) {
  var subMinutes = diffMinutes >= 435 ? 45 : (diffMinutes >= 375 ? 30 : (diffMinutes >= 255 ? 15 : 0));
  return diffMinutes - subMinutes;
}

/**
 * Liefert in Abhaengigkeit von der abgezogenen Pause eine CSS Klasse.
 * @param {type} zeiten Bsp: 6-15.15
 * @returns {String} CSS Klasse für Anwesenheit Felder
 */
function anwesenheitCss(zeiten) {
  var diffMinutes = diffInMinuten(zeiten);
  return ((minutesToKaz(diffMinutes) == minutesToKaz(diffMinutes - 15)) ? "red" : "");
}

/**
 * Berechnet aus einem Wert, der aus dem Feld "Zeiten" stammt, die Differenz
 * in Miunten.
 * @param {type} zeiten Bsp: 6-15.15
 * @returns {Number|String} Differenz der Zeiten in min
 */
function diffInMinuten(zeiten) {
  if (!isString(zeiten)) {
    return '';
  }
  var splitted = zeiten.split('-');
  if (splitted.length != 2 || splitted[1] == '') {
    return '';
  }
  var t1 = splitted[0];
  var t2 = splitted[1];

  t1 = t1.indexOf('.') == -1 ? t1 + '.00' : t1;
  t2 = t2.indexOf('.') == -1 ? t2 + '.00' : t2;
  // Wenn irgendein Text drin steht : nicht berechnen
  if (isNaN(t2) || isNaN(t1)) {
    return '';
  }

  var t1 = t1.split('.');
  var t2 = t2.split('.');
  var d1 = new Date(0, 0, 0, t1[0], t1[1]);
  var d2 = new Date(0, 0, 0, t2[0], t2[1]);
  var diff = new Date(d2 - d1);
  // Pause ab 4:15h: 15 min = 0.25 / ab 6:15h : 30 min = 0.5 / ab 7:15h : 45 min = 0.75 h abziehen
  var diffMinutes = (diff.getHours() * 60) - 60 + diff.getMinutes();
  return diffMinutes;
}
