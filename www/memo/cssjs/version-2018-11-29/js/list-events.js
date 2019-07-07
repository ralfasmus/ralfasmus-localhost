
/**
 * Nachdem Elemente neu erzeugt oder geladen wurden, werden an deren Liste hier die
 * Event Handler gebunden.
 * @param {type} listParentElement
 * @returns {undefined}
 */
function bindListEvents(listParentElement) {

  executeListEvents(listParentElement);

  // Daten als TXT oder CSV anzeigen
  $(listParentElement).find(".btn.showtext").on('click', function () {
    var list = findMyListParent(this);
    var instances = $(list).find('[data-element="instance"]:not(.hidden)');
    var separator = $(this).attr('data-text-column-separator');
    separator = separator == 'tab' ? '\t' : separator;
    if ($(list).attr('data-list-instance-type') === 'ARB') {
      var text = '';
      var text2 = '';
      for (i = 0; i < $(instances).length; i++) {
        var datum = $(instances[i]).find('[data-element="datum"]').attr('value') + separator;
        var asmus = 'Ralf Asmus' + separator;
        var stunden = numberOrDefault($(instances[i]).find('[data-element="stunden"]').attr('value'), '0') + separator;
        //var kaz = $(instances[i]).find('[data-element="kaz"]').attr('value') + separator;
        var name = $(instances[i]).find('[data-element="name"]').attr('value');
        var name2 = textAfterAtAt($(instances[i]).find('[data-element="name"]').attr('value'));
        var newline = '\r\n';
        text += datum + asmus + stunden + name + newline;
        text2 += datum + asmus + stunden + name2 + newline;
      }
      $('.copypaste .daten').html('<h4>Kompletter Text:</h3><pre>' + text + '</pre><h4>Text nach @@:</h3><pre>' + text2 + '</pre>');
    } else if ($(list).attr('data-list-instance-type') === 'ANW') {
      var text = '';
      for (i = 0; i < $(instances).length; i++) {
        var datum = $(instances[i]).find('[data-element="datum"]').attr('value') + separator;
        var zeiten = $(instances[i]).find('[data-element="zeiten"]').attr('value') + separator;
        var stunden = numberOrDefault($(instances[i]).find('[data-element="stunden"]').attr('value'), '0') + separator;
        var bemerkung = $(instances[i]).find('[data-element="bemerkung"]').attr('value');
        var newline = '\r\n';
        text += datum + stunden + zeiten + bemerkung + newline;
      }
      $('.copypaste .daten').html('<pre>' + text + '</pre>');
    }
    $('.copypaste').removeClass('hidden');
  });
  /**
   * Eine neue Instanz erstellen:
   */
  $(listParentElement).find(".neu").on('click', function (event) {
    var dataInstanceType = findMyListParent(this).attr('data-list-instance-type');
    var newInstance = duplicateOrNew($('[data-element=instance][data-instance-type=' + dataInstanceType + ']').first(), true);
  });
}

/**
 * Zeilen sortieren.
 */
function sortierenListEvents(viewElement) {
  $(viewElement).find(".sortieren.accepting-click").on('click', function () {

    // read next Sort direction
    var list = findMyListParent(this);
    var sortDown = $(this).hasClass('sort-down');

    // reset all sort buttons
    $(list).find('.sortieren.accepting-click').each(function () {
      resetSortButton(this);
    });

    // set sort button active
    if (sortDown) {
      $(this).addClass('sort-up');
      $(this).removeClass('sort-down');
    } else {
      $(this).removeClass('sort-up');
      $(this).addClass('sort-down');
    }
    $(this).addClass('active');

    // sort list
    var sortAttr = $(this).attr('data-sort-attribute');
    var sortInstanceType = $(list).attr('data-list-instance-type');
    var sortInstancesParent = $(list).find('[data-loop="' + sortInstanceType + '"]');
    sortElements(sortInstancesParent, sortAttr, sortDown);
  });
}

/**
 * Zeilen einschraenken entsprechend Filter.
 */
function filterListEvents(viewElement) {
  // INPUT Filter: Reagiere auf Eingabe
  $(viewElement).find("input.filter.accepting-input").on('keyup', function () {
    var list = findMyListParent(this);
    updateList(list);
  });
  // Click Filter: Reagiere auf Click und rotiere zwischen 3 Zustaenden /
  // Werten im Attribut data-click '' -> click1 -> click2 -> '' usw.
  $(viewElement).find(".filter.accepting-click").on('click', function () {
    var clickState = $(this).attr('data-click');
    // '' -> click1 -> click2 -> ''
    $(this).attr('data-click', clickState == 'click2' ? '' : (clickState == 'click1' ? 'click2' : 'click1'));
    var list = findMyListParent(this);
    updateList(list);
  });
}

/**
 * Sonstige List-Click-Events ausfuehren.
 */
function otherListEvents(viewElement) {
  $(viewElement).find(".accepting-click.selektierte-archivieren").on('click', function () {
    var list = findMyListParent(this);
    $(list).find('[data-element=instance]:not(.hidden) .archive').click();
  });
  $(viewElement).find(".accepting-click.selektierte-loeschen").on('click', function () {
    var list = findMyListParent(this);
    $(list).find('[data-element=instance]:not(.hidden) .delete').click();
  });

    $(viewElement).find(".accepting-click.selektierte-mark1").on('click', function () {
        var list = findMyListParent(this);
        $(list).find('[data-element=instance]:not(.hidden) .btn.mark1').click();
    });
  $(viewElement).find(".accepting-click.all-with-info").on('click', function () {
    var list = findMyListParent(this);
    $(list).toggleClass('all-with-info');
    $(this).toggleClass('active');
    if ($(list).hasClass('all-with-info')) {
      $(list).find('[data-element=instance]').addClass('with-info');
    } else {
      $(list).find('[data-element=instance]').removeClass('with-info');
    }
  });

  $(viewElement).find(".accepting-click.alle-show-all").on('click', function () {
    var list = findMyListParent(this);
    $(list).find('[data-element=instance] .show-all').click();
  });
  $(viewElement).find(".accepting-click.position-berechnen").on('click', function () {
    var list = findMyListParent(this);

    var sortInstancesParent = $(list).find('[data-loop=SON]');
    sortElements(sortInstancesParent, 'data-sort-pos', false);

    var instances = $(list).find('[data-loop=SON] [data-element=instance]');

    var counter = 10;
    for (i = 0; i < $(instances).length; i++) {
      var inst = $(instances)[i];
      $(inst).find('input[name=pos]').val(counter);
      saveInstance(inst);
      counter += 10;
    }

  });
}

/**
 * List-Click Events ausfuehren.
 */
function executeListEvents(viewElement) {
  filterListEvents(viewElement);
  sortierenListEvents(viewElement);
  otherListEvents(viewElement);
}