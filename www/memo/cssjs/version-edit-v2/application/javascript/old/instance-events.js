

/**
 * Nachdem Elemente neu erzeugt oder geladen wurden, werden an diese hier die
 * Event Handler gebunden.
 * @param {type} instance Die Instanz
 * @returns {undefined}
 */
function bindInstanceEvents(instance) {

  /**
   * Bei Aenderung: Speichern eines Data-Objekts:
   */
  $(instance).find("input").on('focus', function (event) {
    showStatus('Daten werden bearbeitet', true);
  });
  $(instance).find("input").on('change', function (event) {
    var success = saveInstance(getMyInstance(this));
  });

  $(instance).find('.prop-name, .prop-stunden, .prop-kaz').on('click', function () {
    showInstance(instance);
  });

  /**
   * Zuletzt bearbeitete oder geklickte Instanz anzeigen
   */
  function showInstance(instance) {
    //var instance = getMyInstance(this);
    var instanceId = getInstanceId(instance);
    var dup = $(instance).prop('innerHTML');
    $('#lasttouched .lastid').html(instanceId);
    $('#lasttouched .lastinstance').html(dup);
  }



  /**
   * Die aktuelle Instanz duplizieren:
   */
  $(instance).find(".duplicate").on('click', function (event) {
    var newInstance = duplicateOrNew(getMyInstance(this), false);
    // jetzt die Liste erneut sortieren
    $(findMyListParent(newInstance)).find(".sortieren.accepting-click.active").click().click();
  });


  /**
   * ID to clipboard.
   */
  $(instance).find(".id-to-clipboard").on('click', function (event) {
    var id = $(instance).attr('data-instance-id');
    copyToClipboard(id);
  });
    /**
     * Link to clipboard.
     */
    $(instance).find(".link-to-clipboard").on('click', function (event) {
        var id = $(instance).attr('data-instance-id');
        var name = $(instance).find('input[name=name]').val();
        var link = '<a href="#' + id + '">' + name + '</a>';
        copyToClipboard(link);
    });

  /**
   * Die aktuelle Instanz editieren:
   */
  $(instance).find(".edit").on('click', function (event) {
    var instance = getMyInstance(this);
    if (!$(instance).hasClass('edit-instance')) {
      $(instance).find('.editor').each(function () {
        initSummerNote(this, '... Hier ' + $(this).attr('name') + ' eingeben ...');
        if (isString($(this).val()) && '' !== $(this).val()) {
          $(this).summernote('code', $(this).val());
        }
      });
    } else {
      $(instance).find(".save").click();
      sleep(100);
      $(instance).find('.editor').each(function () {
        $(this).summernote('destroy');
      });
    }
    $(instance).toggleClass('edit-instance');
  });
  /**
   * Fuer die aktuelle Instanz info anzeigen:
   */
  $(instance).find(".with-info").on('click', function (event) {
    var instance = getMyInstance(this);
    $(instance).toggleClass('with-info');
  });
  /**
   * Fuer die aktuelle Instanz alles anzeigen:
   */
  $(instance).find(".with-all").on('click', function (event) {
    var instance = getMyInstance(this);
    showAll(instance);
  });
    $(instance).find("a[href^='#']").on('click', function (event) {
        event.preventDefault(); // machen wir nur, damit die Animation unten ausgefuehrt werden kann, statt direkt hinzuspringen
        var href= $(this).attr('href');
        var targetInstance = $(href).parent();
        $('html, body').animate({
            scrollTop: ($(href).offset().top) - 20
        }, 2000);
        showAll(targetInstance);
    });


  /**
   * Setze Datepicker in alle .datum Felder
   */
  $(instance).find("input.datum").attr('id', '').removeClass('hasDatepicker').datepicker({dateFormat: "dd.mm.yy"});

  /**
   * Das aktuelle Data-Objekt loeschen:
   */
  $(instance).find(".delete").on('click', function (event) {
    deleteInstance(getMyInstance(this));
    //deleteInstance(selectorCssId(getMyInstanceCssId(this)));
  });
  /**
   * Das aktuelle Data-Objekt speichern:
   */
  $(instance).find(".save").on('click', function (event) {
    saveInstance(getMyInstance(this));
  });

    /**
     * Das aktuelle Objekt als mark1 setzen und speichern:
     */
    $(instance).find(".mark1").on('click', function (event) {
        var mark1 = ($(getMyInstance(this)).find('input[name=mark1]').val() == 1) ? '' : 1;
        $(getMyInstance(this)).find('input[name=mark1]').val(mark1);
        saveInstance(getMyInstance(this));
    });

  /**
   * Das aktuelle Data-Objekt archivieren:
   */
  $(instance).find(".archive").on('click', function (event) {
    $(getMyInstance(this)).find('input[name=archive]').val('1');
    deleteInstance(getMyInstance(this));
  });


  /**
   * Arbeit namen Liste fuer Auto complete neu berechnen.
   */
  $(instance).find('.on-change-update-autocomplete').on('change', function (event) {
    berechneAutoCompleteListen();
  });
}

/**
 * Rich Text Editor initialisieren fuer das input element und wenn Wert leer ist,
 * den placeHolder darstellen.
 * @param {type} element
 * @param {type} placeHolder
 * @returns {undefined}
 */
function initSummerNote(element, placeHolder) {
  $(element).summernote({
    width: 1200,
    placeholder: placeHolder,
     toolbar: [
     // [groupName, [list of button]]. @see https://summernote.org/deep-dive/#customization
     ['do',['undo', 'redo']],
     ['style', ['bold', 'italic', 'underline', 'strikethrough','clear']],
     //['font', ['strikethrough', 'superscript', 'subscript']],
     ['font1', ['style', 'fontsize']],
     ['font3', ['color']],
     ['font4', ['fontname']],
     ['para', ['height', 'ul', 'ol', 'paragraph']],
     ['insert', ['link','table','hr' ]],
     ['misc',['fullscreen', 'codeview','help']]
     ],


    callbacks: {
      onChange: function (contents) {
        getMyInstance(this).find('.save').click();
        logDebug('onChange:', contents);
      }
    }
  });

}
