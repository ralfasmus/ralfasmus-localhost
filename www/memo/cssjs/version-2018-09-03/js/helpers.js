
/**
 * Millisekunden timestamp
 * @returns {Number}
 */
function nowTimestamp() {
  return $.now();
}

/**
 * Liefert true, wenn str ein String ist.
 * @param {type} str
 * @returns {Boolean}
 */
function isString(str) {
  return (typeof str === 'string' || str instanceof String);
}

/**
 * Liefert true, wenn num eine Zahl ist.
 * @param {type} num
 * @returns {Boolean}
 */
function isNumber(num) {
  return (num !== '' && !isNaN(num));
}

/**
 * Liefert das eine Liste umfassende Element fuer die Liste, in der element ein
 * Mitglied ist.
 * @param {type} element
 * @returns {jQuery}
 */
function findMyListParent(element) {
  return $(element).parentsUntil(".list-parent").last().parent();
}

/**
 *
 * @param {type} instance Instanz als Objekt
 * @returns {jQuery} Liefert die data-instance-id der Instanz.
 */
function getInstanceId(instance) {
  return $(instance).attr('data-instance-id');
}


/**
 * Liefert das instance element zu dem containedElement gehoert. Also ausgehend von
 * element das erste aeussere Element dass ein data-instance-id Attribut hat.
 * @param {type} instanceOrContainedElementOrSelector Die Instance oder ein Contained
 * Element als Objekt oder ein Selektor, der die Instanz eindeutig auswaehlt.
 * @returns {jQuery}
 */
function getMyInstance(instanceOrContainedElementOrSelector) {
  var instance = null;
  if ($(instanceOrContainedElementOrSelector).filter('[data-instance-id]').empty()) {
    instance = $(instanceOrContainedElementOrSelector).parentsUntil('[data-instance-id]').last().parent();
  } else {
    instance = $(instanceOrContainedElementOrSelector).first();
  }
  return instance;
}

/**
 * Liefert die Budget Instance oder null.
 * @param {type} arbeitInstance
 * @returns {jQuery}
 */
function getMyBudgetInstance(arbeitInstance) {
  addMyBudgetInstanceId(arbeitInstance);
  var budgetId = $(arbeitInstance).attr('data-my-budget-instance-id');
  return isString(budgetId) ? $('[data-instance-type=BUD][data-instance-id="' + budgetId + '"]') : null;
}


function addMyBudgetInstanceId(arbeitInstance) {
  var budgets = $('[data-instance-type=BUD]');
  $(budgets).each(function () {
    addBudgetInstanceId(this, arbeitInstance);
  });
}


function addBudgetInstanceId(budgetInstance, arbeitInstance) {
  var arbeitKeys = $(arbeitInstance).find('.prop-name input').val();
  arbeitKeys = isString(arbeitKeys) ? arbeitKeys.split(' ') : [];
  var budgetKeys = $(budgetInstance).find('.prop-keys input').val();
  budgetKeys = isString(budgetKeys) ? budgetKeys.split(' ') : [];
  logDebug('BUD KEYS:' + budgetKeys);
  for (bi = 0; bi < budgetKeys.length; bi++) {
    for (ai = 0; ai < arbeitKeys.length; ai++) {
      if (budgetKeys[bi] === arbeitKeys[ai]) {
        $(arbeitInstance).attr('data-my-budget-instance-id', $(budgetInstance).attr('data-instance-id'));
        break;
      }
    }
  }
}

/**
 * Liefert einen Selektor, der das instance Element eindeutig auf Basis seiner
 * CSS ID auswaehlt. Andere Vorkommen der Instance auf der Page werden nicht
 * ausgewaehlt.
 *
 */
function getInstanceSelector(instance) {
  var id = $(instance).attr('id');
  return  '[data-element="instance"]' + '[id="' + id + '"]';
}


/**
 * Liefert true, wenn v ein String ist.
 */
function isString(v) {
  return (typeof v === 'string' || v instanceof String);
}

/**
 * Prueft eine AJAX Response und zeigt AJAX Response Message / Fehlerinformationen
 * in der Browser Konsole an.
 *
 * @param {type} jqXHROrString
 * @param {type} expectedType json|html. Ist der erwartete Typ json, wird
 * jqXHROrString auf valides JSON geprueft.
 * @param {type} showAlwaysResponseText true=Zeige die message property
 * einer JSON response immer an, auch wenn es keinen Fehler gibt.
 * @returns {undefined}
 */
function displayErrors(jqXHROrString, expectedType, showAlwaysResponseText) {
  var messageLevel = 'debug';
  var isError = false;
  var responseText = '';
  var message = '';
  if (isString(jqXHROrString)) {
    // jqXHROrString is just a string like 'ERROR...'
    responseText = jqXHROrString;
  } else {
    // jqXHROrString is HTML Response
    responseText = jqXHROrString.responseText;
  }
  if (responseText.includes('ncaught')) {
    isError = true;
  }
  if (expectedType == 'json' && !isString(jqXHROrString)) {
    // jqXHROrString is JSON Response
    var jsonResult = [];
    try {
      jsonResult = jqXHROrString.responseJSON;
      if (jsonResult.hasOwnProperty('message')) {
        message += jsonResult['message'];
      } else {
        message += "KEINE MESSAGE IN JSONRESULT!";
      }
      if (jsonResult.hasOwnProperty('message-level')) {
        messageLevel = jsonResult['message-level'];
        if (messageLevel == 'error') {
          isError = true;
        }
      }
    } catch (ex) {
      message += "Konnte JSON response nicht parsen! ";
      message += ex.toString();
      isError = true;
    }
  }
  if (isError) {
    logError(message);
  } else {
    if (messageLevel == 'debug') {
      logDebug(message);
    }
    if (messageLevel == 'info') {
      logInfo(message);
    }
    if (messageLevel == 'warn') {
      logWarn(message);
    }
  }
  if (isError || showAlwaysResponseText) {
    message = responseText + '<div>' + message + '</div>';
    $('#errordiv').append(message);
  }
}


/**
 * Liefert numberString als Float oder defaultValue, wenn es keine Zahl ist.
 * @param {type} numberString
 * @param {type} defaultValue
 * @returns {}
 */
function numberOrDefault(numberString, defaultValue) {
  var parsed = parseFloat(numberString);
  if (isNaN(parsed)) {
    parsed = defaultValue;
  }
  return parsed;
}

/**
 * Liefert aktuelles Datum in der Form dd.mm.jjjj
 * @returns {String}
 */
function datumHeute() {
  var jetzt = new Date();
  var dd = jetzt.getUTCDate();
  var mm = jetzt.getUTCMonth() + 1;
  var yyyy = jetzt.getUTCFullYear();
  if (dd < 10) {
    dd = '0' + dd;
  }
  if (mm < 10) {
    mm = '0' + mm;
  }
  var de = dd + '.' + mm + '.' + yyyy;
  return de;
}

function showStatus(text, success) {
  $('#status').html(text);
  if (success) {
    $('#status').removeClass("error");
  } else {
    $('#status').addClass("error");
  }
}

function sleep(millis)
{
  var date = new Date();
  var curDate = null;
  do {
    curDate = new Date();
  } while (curDate - date < millis);
}
/**
 * Liefert den trimmed Teilstring nach @@ oder den gesamten String wenn kein @
 * enthalten.
 * @param {type} str
 * @returns {unresolved}
 */
function textAfterAtAt(str) {
  str = isString(str) ? str : '';
  var result = str.split('@@', 2);
  return result.splice(-1)[0].trim();
}
/**
 * Liefert den Teilstring vor dem 2. Punkt -also Tag+Monat eines DE Datums
 * enthalten.
 * @param {string} datumString Datum der Form 12.11.2017
 * @returns {unresolved}
 */
function textBevorSecondPoint(datumString) {
  str = isString(datumString) ? datumString : '';
  var result = datumString.split('.');
  return result[0] + "." + result[1] + ".";
}
/**
 * Liefert den Teilstring vor dem ersten Leerzeichen.
 * @param {string} datumString Datum der Form 12.11.2017
 * @returns {unresolved}
 */
function textBevorFirstSpace(text) {
    var result = text.split(' ');
    return result[0];
}
/**
 * Liefert den trimmed Teilstring vor inkl. ~ oder den gesamten String wenn kein ~
 * enthalten.
 * @param {type} str
 * @returns {unresolved}
 */
function textBevorIncludingAtAt(str) {
  str = isString(str) ? str : '';
  var result = str.split('@@', 2);
  var s1 = result.splice(0)[0].trim();
  return (str.split('@@', 2).length > 1) ? s1 + ' ~' : s1;
}

function strToLower(str) {
  return isString(str) ? str.toLowerCase() : str;
}

function copyToClipboard(id) {
  var $temp = $("<input>");
  $("body").append($temp);
  $temp.val(id).select();
  document.execCommand("copy");
  $temp.remove();
}

function removeVorlageString(str) {
  return str.replace(' #vorlage ', '').replace('#vorlage ', '').replace(' #vorlage', '').replace('#vorlage', '');
}