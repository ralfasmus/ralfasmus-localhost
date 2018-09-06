/**
 * Berechnet diverse Properties und Attribute neu, nachdem ein Wert einer Instanz
 * geaendert oder eine Instanz geladen wurde.
 * @param {type} instance
 * @returns {undefined}
 */
function calculateInstanceProperties(instance) {

  // Fuer alle sinnvoll:
  var lowerId = strToLower($(instance).attr('data-instance-id'));
  $(instance).attr('data-sort-id', lowerId);
  $(instance).find('.btn.id-to-clipboard').attr('title', 'Kopiert ID zum Clipboard: ' + lowerId);
  $(instance).find('.btn.link-to-clipboard').attr('title', 'Kopiert Link mit Name als Ankertext zum Clipboard.');
  var name = $(instance).find('input[name=name]').val();
  $(instance).attr('data-sort-name', strToLower(name));
  $(instance).attr('data-select-name', strToLower(name) + '~' + lowerId);
  // ARB
  if ($(instance).attr('data-instance-type') == 'ARB') {
    updateDataSelectDatum(instance);
    var kazStunden = $(instance).find('input[name=kaz]').val();
    var kazKannSpaeter =  (kazStunden == 0 && $(instance).find('input[name=kaz_kann_spaeter]').val() == 1) ? '1' : '';
    var mark1 =  ($(instance).find('input[name=mark1]').val() == 1) ? '1' : '';
    $(instance).attr('data-sort-stunden', $(instance).find('input[name=stunden]').val());
    $(instance).attr('data-sort-kaz', $(instance).find('input[name=kaz]').val());
    $(instance).attr('data-sort-datum', getSortDatum(instance));
    $(instance).attr('data-select-everything', strToLower(name) + '~' + lowerId);
    $(instance).attr('data-select-stunden', $(instance).find('input[name=stunden]').val());
    $(instance).attr('data-select-kaz_kann_spaeter', kazKannSpaeter);
      $(instance).attr('data-select-mark1', mark1);
    $(instance).attr('data-select-kaz', kazStunden);

      if (kazKannSpaeter) {
          $(instance).addClass("kaz_kann_spaeter");
      } else {
          $(instance).removeClass("kaz_kann_spaeter");
      }
      if (mark1) {
          $(instance).addClass("mark1");
      } else {
          $(instance).removeClass("mark1");
      }
      var schonInKaz = $(instance).attr('data-select-kaz') > 0;
      if (schonInKaz) {
          $(instance).addClass("schon_in_kaz");
          $(instance).removeClass("noch_nicht_in_kaz");
      } else {
          $(instance).removeClass("schon_in_kaz");
          $(instance).addClass("noch_nicht_in_kaz");
      }
    $(instance).find('input[name=name]').attr('title', name);
  }
  // BUD
  if ($(instance).attr('data-instance-type') == 'BUD') {
    $(instance).attr('data-sort-zeitraum', $(instance).find('input[name=zeitraum]').val());
    $(instance).attr('data-select-everything', strToLower(name))
            + '~' + strToLower($(instance).find('input[name=korrekturbemerkung]').val())
            + '~' + strToLower($(instance).find('input[name=keys]').val()
                    + '~' + lowerId);
    $(instance).find('input[name=name]').attr('title', name);
    $(instance).find('input[name=keys]').attr('title', $(instance).find('input[name=keys]').val());
    $(instance).find('input[name=korrekturbemerkung]').attr('title', $(instance).find('input[name=korrekturbemerkung]').val());
  }
// ANW
  if ($(instance).attr('data-instance-type') == 'ANW') {
    updateDataSelectDatum(instance);
    var zeitenInput = $(instance).find('input[name=zeiten]').val();
    var calculatedHours = anwesenheitVonZeiten(zeitenInput);
    if (calculatedHours != '') {
      $(instance).find('input[name=stunden]').val(calculatedHours);
      $(instance).removeClass("red");
      $(instance).addClass(anwesenheitCss(zeitenInput));
      var schonInKaz = $(instance).find('input[name=schon_in_kaz]').val();
      $(instance).removeClass("schon_in_kaz");
      $(instance).addClass("nicht_in_kaz");
      if (schonInKaz == 1) {
        $(instance).removeClass("nicht_in_kaz");
        $(instance).addClass("schon_in_kaz");
      }
    }
    $(instance).attr('data-select-bemerkung', strToLower($(instance).find('input[name=bemerkung]').val()));
  }
  // NOT
  if ($(instance).attr('data-instance-type') == 'NOT') {
    updateDataSelectDatum(instance);
    $(instance).attr('data-sort-datum', getSortDatum(instance));

    var mark1 =  ($(instance).find('input[name=mark1]').val() == 1) ? '1' : '';
    var text = $(instance).find('input[name=text]').val();
    logDebug('TEXT:' + text);

    text = text == '<p><br></p>' ? '' : text;
    $(instance).find('input[name=text]').val(text);

    var files = $(instance).find('input[name=files]').val();
    logDebug('FILES:' + files);
    var art = $(instance).find('input[name=art]').val();

    $(instance).attr('data-select-mark1', mark1);
    $(instance).attr('data-sort-art', strToLower(art) + ' ...' + strToLower(name));
    $(instance).attr('data-select-art', strToLower(art));
    $(instance).attr('data-select-text', strToLower(text));
    $(instance).attr('data-select-everything', strToLower(text)
            + ' ~ ' + strToLower(name)
            + ' ~ ' + lowerId
            + ' ~ ' + strToLower(files)
            + ' ~ ' + strToLower($(instance).find('input[name=art]').val()));

    $(instance).find('.show-on-with-text.text').html(text);

    var showfiles = '';
    $(instance).removeClass('has-no-files');
    $(instance).removeClass('has-no-text');
    if (files.length > 4) {
      var filenames = files.split(':')
      for (var fi = 0; fi < filenames.length; fi++) {
        showfiles = showfiles + '<a target="_blank" href="/data/FIL/' + filenames[fi] + '">' + filenames[fi] + '</a><br/>';
      }
    } else {
      $(instance).addClass('has-no-files');
    }
    if (text.length < 1) {
      $(instance).addClass('has-no-text');
    }

    if (mark1) {
        $(instance).addClass("mark1");
    } else {
        $(instance).removeClass("mark1");
    }
    $(instance).attr('data-select-files', showfiles);
    $(instance).find('.show-on-with-text.files').html(showfiles);

    $(instance).find('input[name=name]').attr('title', name + text + showfiles);

    $(instance).find(".link-target-blank a").attr('target', '_blank');
    $(instance).find(".link-target-blank a[href*='#']").attr('target', '_self');
  }
  // SON
  if ($(instance).attr('data-instance-type') == 'SON') {
    $(instance).attr('data-sort-pos', $(instance).find('input[name=pos]').val());
    var info = $(instance).find('input[name=info]').val();
    info = info == '<p><br></p>' ? '' : info;
    $(instance).find('input[name=info]').val(info);

    $(instance).attr('data-select-info', strToLower(info));
    $(instance).attr('data-select-everything'
            + ' ~ ' + strToLower(info)
            + ' ~ ' + strToLower(name)
            + ' ~ ' + lowerId);

    $(instance).find('.show-on-with-info').html(info);

    $(instance).removeClass('has-no-info');
    if (info.length < 1) {
      $(instance).addClass('has-no-info');
    }
  }
}

/**
 * Wird nach dem Aendern eines Feldes aufgerufen. Sendet das Feld zum Speichern.
 * @param {type} containedElement
 * @returns {undefined}
 */
function saveField(containedElement) {

  var fieldName = $(containedElement).attr('name');
  var fieldOldValue = $(containedElement).attr('value');
  var fieldNewValue = $(containedElement).val();
  fieldNewValue = fieldNewValue === '<p><br></p>' ? '' : fieldNewValue;
  var dataInstanceId = getInstanceId(getMyInstance(containedElement));
  var formData = {
    'data-view': 'save-instance.json',
  };
  formData[fieldName] = fieldNewValue;
  formData['data-instance-id'] = dataInstanceId;
  var success = saveInstanceForm(formData);
  if (success) {
    logDebug('Success: ' + fieldName + ' changed:' + fieldOldValue + " to " + fieldNewValue);
  }
}


/**
 * Sendet eine Instanz zum Speichern.
 * @param {type} instance
 * @returns {undefined}
 */
function saveInstance(instance) {
  var instanceId = getInstanceId(instance);
  var formData = {
    'data-view': 'save-instance.json',
  };
  $(instance).find('input').each(function () {
    var fieldName = $(this).attr('name');
    var fieldOldValue = $(this).attr('value');
    var fieldNewValue = $(this).val();

    if ($(instance).hasClass('edit-instance') && $(this).hasClass('editor')) {
      fieldNewValue = $(this).summernote('code');
      //alert(fieldNewValue);
    }
    formData[fieldName] = fieldNewValue;
    logDebug("Save Instance Field: " + fieldName + "=" + fieldNewValue);
  });
  formData['data-instance-id'] = instanceId;
  formData['data-instance-type'] = $(instance).attr('data-instance-type');
  var success = saveInstanceForm(formData);
  if (success) {
    logInfo('Success. Instance ' + instanceId + ' saved.');
  }
}


/**
 * Sendet eine Instanz uzm Loeschen.
 * @param {type} instance
 * @returns {Boolean}
 */
function deleteInstance(instance) {
  var instanceId = getInstanceId(instance);
  var formData = {'data-view': 'delete-instance.json'};
  $(instance).find('input').each(function () {
    var fieldName = $(this).attr('name');
    var fieldOldValue = $(this).attr('value');
    var fieldNewValue = $(this).val();
    formData[fieldName] = fieldNewValue;
  });
  formData['data-instance-id'] = instanceId;
  $.ajax({
    type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
    url: 'index.php', // the url where we want to POST
    data: formData, // our data object
    dataType: 'json', // what type of data do we expect back from the server
    encode: true,
    //async: false,
    success: (function (data, textStatus, jqXHR) {
      logInfo("Deleted Instance: " + textStatus);
      $(instance).replaceWith('');
      filterUpdateAll();
      summenUpdateAll();
    }),
    error: (function (jqXHR, textStatus, errorThrown) {
      logError("Deleted Instance: " + textStatus + errorThrown);
      displayErrors(jqXHR, 'json', true);
    }),
    complete: // after succss/error
            (function (jqXHR, textStatus) {
              displayErrors(jqXHR, 'json', false);
            })
  });
  return true;
}

/**
 * Hilfsmethode zum Senden einer Instanz zum Speichern.
 * @param {type} formData
 * @returns {Boolean}
 */
function saveInstanceForm(formData) {
  var dataInstanceId = formData['data-instance-id'];
  $.ajax({
    type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
    url: 'index.php', // the url where we want to POST
    data: formData, // our data object
    dataType: 'json', // what type of data do we expect back from the server
    encode: true,
    //async: false,
    success: (function (data, textStatus, jqXHR) {
      logInfo("Saved Instance: " + textStatus);
      updateInstances('[data-element="instance"][data-instance-id="' + dataInstanceId + '"]', false);
      $('[data-element=instance]').removeClass('saved');
      $('[data-element="instance"][data-instance-id="' + dataInstanceId + '"]').addClass('saved');
      showStatus("Data gespeichert: Success", true);
    }),
    error: (function (jqXHR, textStatus, errorThrown) {
      logError("Saved Instance: " + textStatus + errorThrown);
      displayErrors(jqXHR, 'json', true);
      showStatus("FEHLER: Daten NICHT gespeichert.", false);
    }),
    complete: // after succss/error
            (function (jqXHR, textStatus) {
              displayErrors(jqXHR, 'json', false);
            })
  });
  return true;
}

/**
 * Holt alle Instanzen vom Server und aktualisiert sie in der Seite.
 * @param {type} instances
 * @param {type} loadViews
 * @returns {undefined}
 */
function updateInstances(instances, loadViews) {
  $(instances).each(function () {
    updateInstance(this, loadViews);
  });
}

/**
 * Holt alle Instanzen vom Server und aktualisiert sie in der Seite.
 * @param {type} instances
 * @param {type} loadViews
 * @returns {undefined}
 */
function updateInstancesInitial(instances, instancesData, loadInsideViews) {
  console.info(instancesData);
  $(instances).each(function () {
    instanceData = instancesData[getInstanceId(this)]['instance-data'];
    updateInstanceData(this, instanceData, loadInsideViews);
  });
}


function updateInstanceData(instance, instanceData, loadInsideViews) {

  var instanceId = getInstanceId(instance);
  var count = 0;
  //logDebug(instanceData);
  for (i in instanceData) {
    if (count++ < 1) {
      logDebug('Updated [data-instance-id="' + instanceId + '"] ');
    }
    var element = instanceData[i]['element'];
    for (attr in instanceData[i]['attributes']) {
      var value = instanceData[i]['attributes'][attr];
      var name = attr;
      if (attr == 'value') {
        $('[data-instance-id="' + instanceId + '"] [data-element="' + element + '"]').val(value);
      }
      if (attr == 'html') {
        $('[data-instance-id="' + instanceId + '"] [data-element="' + element + '"]').html(value);
      } else {
        $('[data-instance-id="' + instanceId + '"] [data-element="' + element + '"]').attr(name, value);
      }
    }
  }
  calculateInstanceProperties(instance);
  if (loadInsideViews) {
    bindInstanceEvents(instance);
    loadViews(instance);
  } else {
    filterUpdateAll();
    summenUpdateAll();
  }
}


/**
 * * Holt eine Instanz vom Server und aktualisiert sie in der Seite.
 * @param {type} instance
 * @param {type} loadInsideViews
 * @returns {Boolean}
 */
function updateInstance(instance, loadInsideViews) {
  var instanceId = getInstanceId(instance);
  var cssId = $(instance).attr('id');
  var formData = {
    'data-view': 'instance-data.json',
    'data-instance-id': instanceId
  };
  $.ajax({
    type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
    url: 'index.php', // the url where we want to POST
    data: formData, // our data object
    dataType: 'json', // what type of data do we expect back from the server
    encode: true,
    //async: false,
    success: (function (data, textStatus, jqXHR) {
      try {
        logDebug("Updated Instance: " + textStatus);
        var instanceData = null;
        instanceData = jqXHR.responseJSON['instance-data'];
        updateInstanceData(instance, instanceData, loadInsideViews);
      } catch (ex) {
        throw ex;
        //displayErrors('FEHLER42! ' + ex.messsage, 'string', true);
      }
    }),
    error: (function (jqXHR, textStatus, errorThrown) {
      logError("Updated Instance: " + textStatus + errorThrown);
      displayErrors(jqXHR, 'json', true);
    }),
    complete: // after succss/error
            (function (jqXHR, textStatus) {
              displayErrors(jqXHR, 'json', false);
            })
  });
  return true;
}

/**
 * Dupliziert eine Instanz oder erstellt eine neue (leere) auf Basis von instance.
 * @param {type} instance
 * @param {type} neu true|false : true = neue Instanz (leere Instanz dupliziert), false = konkrete Instanz dupliziert
 */
function duplicateOrNew(instance, neu) {
  var instanceId = getInstanceId(instance);
  var dup = $(instance).prop('outerHTML');
  var newInstanceId = instanceId.split(ID_CLASS_NAME_SEPARATOR)[0] + ID_CLASS_NAME_SEPARATOR + (nowTimestamp());

  var search = new RegExp(instanceId, "g");
  var newHtml = dup.replace(search, newInstanceId);
  var oldCssId = $(instance).attr('id');
  var newCssId = nowTimestamp() + '_new_' + i;
  search = new RegExp(oldCssId, "g");
  var finalHtml = newHtml.replace(search, newCssId);
  /*
   es ist allerdings egal, wo die Instanz einsortiert wird,
   da anschliessend gleich die Instanzen entsprechend der
   Listeneinstellung sortiert werden:
  */
  if (neu) {
    $(instance).before(finalHtml);
  } else {
    $(instance).after(finalHtml);
  }
  var newInstance = $('[id="' + newCssId + '"]');
  logInfo('Duplikat erzeugt: cssid=' + newCssId);
  if (neu) {
      // neue Instanz:
    $(newInstance).find('input').val('');
    // Wenn art Filter gesetzt ist, diesen Wert im neu erzeugten Element eintragen,
    // damit es bei der aktuellen Filtereinstellung zu sehen ist:
    var liste = findMyListParent(newInstance);
    var filterArt = $(liste).find('input.filter.art').val();
    $(newInstance).find('input[name=art]').val(filterArt);
    $(newInstance).find('input[name=datum]').val(datumHeute());
  } else {
    // Duplikat:
    var neuerName = $(newInstance).find('input[name=name]').val();
    $(newInstance).find('input[name=name]').val(removeVorlageString(neuerName));
    $(newInstance).find('input[name=kaz]').val('');
    $(newInstance).find('input[name=stunden]').val('');
    $(newInstance).find('input[name=zeiten]').val('');
    $(newInstance).find('input[name=korrektur]').val('');
    $(newInstance).find('input[name=korrekturbemerkung]').val('');
    $(newInstance).find('input[name=datum]').val(datumHeute());
  }
  saveInstance(newInstance);
  bindInstanceEvents(newInstance);
  calculateInstanceProperties(newInstance);
  $(newInstance).find('.focus input').focus();
  berechneNameList('ARB');
  berechneNameList('NOT');
  berechneArtList();
  return newInstance;
}