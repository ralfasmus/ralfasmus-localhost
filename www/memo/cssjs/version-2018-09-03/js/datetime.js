/**
 * Muss vor der ersten Benutzung einer moment() funktion aufgerufen werdden.
 * @returns {undefined}
 */
function initMoment() {
  moment.locale('de');
}

/**
 * Liefert den Inhalt des Feldes "datum" einer Instanz.
 * @param {type} instance
 * @returns {jQuery}
 */
function getDatum(instance) {
  return $(instance).find('input[name=datum]').val();
}

/**
 * Liefert ein Datum in der Form zum Sortieren, berechnet aus dem Inhalt
 * des Feldes "datum" einer Instanz.
 * @param {type} instance
 * @returns {String} z.B. 2017-06-30_instance_id
 */
function getSortDatum(instance) {
  var datum = getDatum(instance);
  var date = moment(datum, 'DD.MM.YYYY');
  return date.format('YYYY-MM-DD') + '_' + $(instance).attr('data-instance-id');
}

/**
 * Aktualisiert das data-select-datum Feld einer Instanz.
 * @param {type} instanceSelector
 * @returns {undefined}
 */
function updateDataSelectDatum(instance) {
  var datum = getDatum(instance);
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
  $(instance).attr('data-select-datum', select);
}