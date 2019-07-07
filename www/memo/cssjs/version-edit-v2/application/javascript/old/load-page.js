

/**
 * Starte initiales Laden der Seite, sobald das Geruest geladen ist und
 * regsitriere alle Events;
 * @param {type} param
 */
$(document).ready(function () {
  initMoment();
  logDebug('Starte view loading');
  loadData();
  registerGlobalEvents();
  bindInstanceEvents('[data-element="instance"][id="FRE_freitext1"]');
});

/**
 * Falls eine #fragmentid and er URL haengt, scrolle zu diesem Item und oeffne es (stelle den Text dar).
 */
function openFragmentId() {
  var href=$(location).attr('href');
  var n = href.lastIndexOf('#');
  if (n >= 0) {
      var targetFragment = href.substring(n); // inklusive #
      //var targetInstance = getMyInstance(targetFragment);
      var targetInstance = $(targetFragment).parent();
      showAll(targetInstance);
      $('html, body').animate({
          scrollTop: ($(targetFragment).offset().top) - 20
      }, 2000);
  }
}

/**
 * Lade alle Daten innerhalb von viewElement nach.
 *
 * @param {type} viewElement
 * @returns {Boolean}
 */
function loadData() {
  var formData = {};
  formData["event"] = 'page-load';
  /*
  $.each(viewElement.attributes, function (i, attrib) {
    var name = attrib.name;
    var value = attrib.value;
    formData[name] = value;
  });
  */
  // process the form
  showStatus("Daten werden geladen", false);
  loadingCounter++;

  $.ajax({
    type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
    url: 'index.php', // the url where we want to POST
    data: formData, // our data object
    dataType: 'json', // what type of data do we expect back from the server
    encode: true,
    //async: false,
    success: (function (data, textStatus, jqXHR) {
      //$(viewElement).html(jqXHR.responseText);
      instanceHtml = jqXHR.responseJSON['instance-html'];
      instancesData = jqXHR.responseJSON['instances-json']; // array ID => array(message, instance-data[][element], ...)
      $(viewElement).html(instanceHtml);
      logDebug("Loaded View " + $(viewElement).attr('data-view') + ": " + textStatus);
      loadViews(viewElement);
      var instances = $(viewElement).find("[data-element='instance']");
      if ($(instances).size() > 0) {
        // nur wenn Daten geladen wurden, die load-message verbergen:
        $(".loading-message").hide();
      }
      updateInstancesInitial(instances, instancesData, true);
      bindListEvents(viewElement);
      updateLists();
      berechneAutoCompleteListen();
      if (--loadingCounter == 0) {
        showStatus("Daten geladen.", true);
        openFragmentId();
      }

    }),
    error: (function (jqXHR, textStatus, errorThrown) {
      logError("Error loading View: " + dataView + " Status: " + textStatus + errorThrown + " ResponseURL:" + jqXHR.responseURL + " ResponseText: " + jqXHR.responseText);
      displayErrors(jqXHR, 'html', false);
    }),
    complete: // after succss/error
            (function (jqXHR, textStatus) {
              displayErrors(jqXHR, 'html', false);
            })
  });
  return true;
}


function berechneAutoCompleteListen() {
  berechneNameList('ARB');
  berechneNameList('NOT');
  berechneArtList();
}

/**
 * @param {type} elementFilter
 * @returns {undefined}
 */
function berechneNameList(type) {

  logDebug('Berechne Arbeit Name List.');
  var names = $('[data-loop=' + type + '] [data-instance-type=' + type + '] input[name=name]').map(function () {
    return $(this).val();
  }).get();
  var nameList = [];
  for (i = 0; i < names.length; i++) {
    var name = textBevorIncludingAtAt(names[i]);
    if (!!name) {
        nameList.push(removeVorlageString(name));
    }
    if (name.trim() != names[i].trim() && !!names[i].trim()) {
        nameList.push(removeVorlageString(names[i]));
    }
  }
    nameList = sortArray(nameList, false, true);
  $('input.autocomplete.name.' + type).autocomplete({source: nameList});
  return;
}

/**
 * @param {type} elementFilter
 * @returns {undefined}
 */
function berechneArtList(type) {

    logDebug('Berechne NOT Art List.');
    var names = $('[data-loop=NOT] [data-instance-type=NOT] input[name=art]').map(function () {
        return $(this).val();
    }).get();
    var nameList = [];
    for (i = 0; i < names.length; i++) {
        //var name = textBevorFirstSpace(names[i]);
        var result = names[i].split(' ');
        for (idx = 0; idx < result.length; idx++) {
            var name = result[idx];
            if (!!name) {
                nameList.push(name);
            }
        }
    }
    nameList = sortArray(nameList, false, true);
    $('input.autocomplete.art').autocomplete({
        source: nameList,
        open: function(event, ui)
        {
            //$("input.autocomplete.art").autocomplete ("widget").css("width","250px").css("background-color", "yellow");
            $("input.autocomplete.art").autocomplete ("widget").css("width", $(this).innerWidth()).css("background-color", "#fff9b3");
        }
    });
    return;
}

