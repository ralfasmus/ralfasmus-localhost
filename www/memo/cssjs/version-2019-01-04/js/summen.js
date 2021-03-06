/**
 * Aktualisiert alle Summen in Kopfzeile einer Liste.
 * @returns {undefined}
 */
function summenUpdateAll() {
  updateArbAnwJetztDifferenz();
  updateArbAnwDifferenz('heute');
  updateArbAnwDifferenz('gestern');
  updateArbAnwDifferenz('hwoche');
  updateArbAnwDifferenz('vwoche');
  updateArbAnwDifferenz('hmonat');
  updateArbAnwDifferenz('vmonat');
  updateAnwSollDifferenz('heute');
  updateAnwSollDifferenz('gestern');
  updateAnwSollDifferenz('hwoche');
  updateAnwSollDifferenz('vwoche');
  updateAnwSollDifferenz('hmonat');
  updateAnwSollDifferenz('vmonat');
  $('.summe input').each(function () {
    var list = findMyListParent(this);
    summenListUpdate(list);
  });
}

function updateArbAnwJetztDifferenz() {
    var diff = bisJetztZuBuchendeStunden();
    updateTrackDifferenz('.arb-anw', 'jetzt', summarizeFieldValues(selectorInputElements('heute', 'ARB', 'stunden')),
        diff);
}

/**
 * Liefert die nach der in ANW heute gesetzten Startzeit bis JETZT notwendig zu buchenden (zu arbeitenden) Stunden.
 * Dabei sind die Pausen abgezogen, die KAZ auch automatisch abzieht.
 */
function bisJetztZuBuchendeStunden() {
    var zeitenInput = $(selectorInputElements('heute', 'ANW', 'zeiten')).val();
    var startDate = getDateFromZeitraum(zeitenInput, true);
    var diff = 0;
    if(startDate != '') {
        var startString = startDate.getHours() + '.' + startDate.getMinutes();
        var jetztString = zeitJetztAufgerundet();
        var zeitraumString = startString + '-' + jetztString;
        var jetztDate = getDateFromZeitraum(zeitraumString, false);
        var arbeitMinutes = diffInMinuten(startDate, jetztDate);
        var calculatedPauseMinutes = pauseMinutes(arbeitMinutes, '');
        diff = (arbeitMinutes - calculatedPauseMinutes) / 60;
    }
    return diff;

}

function updateArbAnwDifferenz(zeitraum) {
    var zuBuchendeStunden = summarizeFieldValues(selectorInputElements(zeitraum, 'ANW', 'stunden'));
    if(zuBuchendeStunden == 0) {
       zuBuchendeStunden = bisJetztZuBuchendeStunden();
    }
  updateTrackDifferenz('.arb-anw', zeitraum, summarizeFieldValues(selectorInputElements(zeitraum, 'ARB', 'stunden')),
          zuBuchendeStunden);
}
function updateAnwSollDifferenz(zeitraum) {
  var selector = selectorInputElements(zeitraum, 'ANW', 'stunden');
  var summeSoll = 6 * $(selector).size();
  updateTrackDifferenz('.anw-soll', zeitraum, summarizeFieldValues(selector), summeSoll);
}

function selectorInputElements(zeitraum, dataInstanceType, inputElementName) {
  return '[data-loop="' + dataInstanceType + '"] [data-element=instance][data-instance-type=' + dataInstanceType + '][data-select-datum*=' + zeitraum + '] input[name=' + inputElementName + ']';
}

function updateTrackDifferenz(bereich, zeitraum, ist, soll) {
  var diff = ist - soll;
  var selectorStart = '.track-differenzen ' + bereich + ' .' + zeitraum;
  $(selectorStart).removeClass('negative');
  if (diff < 0) {
    $(selectorStart).addClass('negative');
  }
  $(selectorStart + ' .wert').html(ist);
  $(selectorStart + ' .differenz').html(' (' + (diff > 0 ? '+' + diff : (diff < 0 ? diff : '&plusmn;0')) + ') ');
}

function summarizeFieldValues(selektorInputElements) {
  var sum = 0;
  $(selektorInputElements).each(function () {
    sum += Number($(this).val());
  });
  return sum;
}

/**
 * Alle Summen einer Liste aktualisieren.
 * @param {type} filterInputElement
 * @returns {undefined}
 */
function summenListUpdate(list) {
  // stunden
  var targetElements = $(list).find('[data-element="instance"]:not(.hidden)').find('input[data-element="stunden"]');
  var summe = 0;
  for (i = 0; i < $(targetElements).length; i++) {
    var inputElement = $(targetElements)[i];
    summe += numberOrDefault($(inputElement).val(), 0);
  }
  $(list).find('.summe.stunden input').val(summe);
  // kaz
  var targetElements = $(list).find('[data-element="instance"]:not(.hidden)').find('input[data-element="kaz"]');
  var summe = 0;
  for (i = 0; i < $(targetElements).length; i++) {
    var inputElement = $(targetElements)[i];
    summe += numberOrDefault($(inputElement).val(), 0);
  }
  $(list).find('.summe.kaz input').val(summe);
}