/**
 * Muss vor der ersten Benutzung einer moment() funktion aufgerufen werdden.
 * @returns {undefined}
 */
function initMoment() {
  moment.locale('de');
}

/**
 * Liefert den Inhalt des Feldes "datum" einer Instanz.
 * @param {type} note
 * @returns {jQuery}
 */
function getDatum(note) {
  return $(note).find('input[name=note-persistent-datetimesaved]').val();
}

function jetzt(momentFormat) {
  return moment(Date.now()).format(momentFormat);
}


/**
 * Liefert ein Datum in der Form zum Sortieren, berechnet aus dem Inhalt
 * des Feldes "datum" einer Instanz.
 * @param {type} note
 * @returns {String} z.B. 2017-06-30_note_id
 */
function getSortDatum(note) {
  var datum = getDatum(note);
  var date = moment(datum, 'DD.MM.YYYY');
  return date.format('YYYY-MM-DD') + '_' + $(note).attr('data-note-id');
}

/**
 * Aktualisiert das data-select-datum Feld einer Instanz.
 * @param {type} noteSelector
 * @returns {undefined}
 */
function updateDataSelectDatum(note) {
  var datum = getDatum(note);
  var date = moment(datum, 'DD.MM.YYYY');
  var select = '';
  // heute:
  //alert(datum);
  select = select + datum;
  select = select + (date.isSame(moment(), 'day') ? ' heute' : '');
  select = select + (date.isSame(moment(), 'week') ? ' hwoche' : '');
  select = select + (date.isSame(moment(), 'month') ? ' hmonat' : '');
  select = select + (date.isSame(moment(), 'year') ? ' hjahr' : '');
  select = select + (date.isSame(moment().subtract(1, 'day'), 'day') ? ' gestern' : '');
  select = select + (date.isSame(moment().subtract(1, 'week'), 'week') ? ' vwoche' : '');
  select = select + (date.isSame(moment().subtract(1, 'month'), 'month') ? ' vmonat' : '');
  select = select + (date.isSame(moment().subtract(1, 'year'), 'year') ? ' vjahr' : '');
  $(note).attr('data-select-datum', select);
}