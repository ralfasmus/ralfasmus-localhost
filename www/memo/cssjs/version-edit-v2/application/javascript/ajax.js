
/**
 * Hilfsmethode zum Senden einer Instanz zum Speichern.
 * @param {type} formData
 * @returns {Boolean}
 */
function saveItemForm(formData) {
    formData['action'] = 'itemsave';
    formData['backupextension'] = '_backup_' + jetzt('YYYY-MM-DD-HH');
    return executeAjax(formData);
}

/**
 * Sendet eine Instanz zum Speichern.
 * @param {type} item
 * @returns {undefined}
 */
function itemSave(formDomNode) {
    var formData = {};
    // Felder des Items einsammeln
    $(formDomNode).find('[name^=item-]').each(function () {
        var fieldName = $(this).attr('name');
        var fieldOldValue = $(this).attr('value');
        var fieldNewValue = $(this).val();
        if(fieldName == 'item-persistent-datetimesaved') {
           fieldNewValue = jetzt('YYYY-MM-DD-HH-mm-ss');
           $(this).val(fieldNewValue);
        }
        if ($(this).hasClass('dvz-js-summernote-editor')) {
            fieldNewValue = $(this).summernote('code');
            //alert(fieldNewValue);
        }
        formData[fieldName] = fieldNewValue;
        console.info("Save Item Field: " + fieldName + "=" + fieldNewValue);
    });

    //console.info(formData);
    // Daten versenden
    var success = saveItemForm(formData);
    if (success) {
        console.info('Success. Item saved.');
    }
}
/**
 * Sendet einen AJAX Request zum Erzeugen eines neuen Items.
 * @param {type} item
 * @returns {undefined}
 */
function itemCreate(formData) {
    var _jetzt = jetzt('YYYY-MM-DD-HH-mm-ss');
    // Felder-Defaults setzen
    formData['item-persistent-datetimecreated'] = _jetzt;
    formData['item-persistent-datetimesaved'] = _jetzt;
    formData['item-persistent-text'] = '';
    formData['item-persistent-art'] = '';

    // Url fuer das window.open erstellen, dass nach dem Speichern des neuen Items ausgefuehrt werden soll,
    // um das neue Item in einem neuen EDIT Browser-Fenster zu oeffnen
    var configId = $('#page').attr('data-config-id');
    var itemId = formData['item-persistent-id'];
    formData['nextpagetitle'] = 'NEW:' + itemId;
    formData['nextpagehref'] = '?action=itemedit&config-id=' + configId + '&item-persistent-id=' + itemId;

    var success = saveItemForm(formData);
    if (success) {
       // funktioniert hier nur, wenn PHP-seitig ein sleep eingebaut wird, um zu warten,
       // bis das saveItemForm() oben serverseitig damit fertig geworden ist, das neue Item
       // zu erzeugen und persistent zu speichern. Wurde besser geloest (siehe ececuteAjax):
       //
       // window.open('?action=itemedit&config-id=' + configId + '&item-persistent-id=' + id, 'EDIT ' + id);
    }
}
/**
 * Sendet einen AJAX Request zum Backup.
 * @param {type} item
 * @returns {undefined}
 */
function itemBackup(formData) {
    formData['action'] = 'itembackup';
    formData['backupextension'] = '_backup_' + jetzt('YYYY-MM-DD-HH-mm-ss');
    return executeAjax(formData);
}

/**
 * Sendet einen AJAX Request zum Loeschen und loescht die Item aus dem DOM.
 * @param {type} item
 * @returns {undefined}
 */
function itemDelete(formData) {
    formData['action'] = 'itemdelete';
    formData['backupextension'] = '_backup_' + jetzt('YYYY-MM-DD-HH-mm-ss');
    return executeAjax(formData);
}
/**
 * Sendet einen AJAX Request, um eine save|delete|backup Action auszufuehren.
 * Das HTML der Response wird am Ende der Seite eingebaut, damit gelieferte script tags ausgefuehrt werden.
 * So kann der Server indirekt etwas in die Browser-Konsole schreiben, z.B. PHP Stack Traces.
 * @param {type} item
 * @returns {undefined}
 */
function executeAjax(formData) {
    // config-id immer mitsenden, damit die Konfiguration, unter der die Seite angezeigt wird, auch auf dem
    // Server bekannt ist, wenn er die AJAX Action ausfuehrt.
    var configId = $('#page').attr('data-config-id');
    formData['config-id'] = configId;
    // Base View festlegen.
    formData['base-view'] = 'index-action';
    console.info("executeAjax: " + formData['action']);
    console.info(formData);
    $.ajax(
	   'index.php', // the url we want to POST
	   {
        type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
        data: formData, // our data object
        dataType: 'html', // what type of data do we expect back from the server
        encode: true,
        //async: false,
        success: (function (data, textStatus, jqXHR) {
            var result = $(data).find('.ajaxresult');
            $('.showajaxresult').html(result);
            // jetzt noch alles anhaengen, damit console.info/error usw. als script ausgefuehrt wird.
            $('.showajaxresult').append(data);

            console.info("SUCCESS: " + textStatus);
            // Wenn es eine formData['nextpagehref'] gibt, so soll das Oeffnen dieser Adresse als neues Browserfenster
            // hier getriggert werden. Genutzt fuer create = save new item + open for edit
            var nextPageHref = formData['nextpagehref'];
            if(typeof nextPageHref !== "undefined" && '' != nextPageHref && null != nextPageHref) {
                var nextPageTitle = formData['nextpagetitle'];
                if(typeof nextPageTitle === "undefined") {
                   nextPageTitle = 'NEXT PAGE';
                }
                $('#nextpage').attr('data-nextpagetitle', nextPageTitle);
                $('#nextpage').val(nextPageHref);
                // Trigger um das windows.open mit den hier vorbereiteten Daten auszufuehren:
                $('#nextpage').change();
            }
            /*
            Loesung funktioniert ohne serverseitige Wartezeit, aber der Popup-Blocker des Browsers muss ausgeschaltet
            werden, um ueber diesen Weg einen Reiter zu oeffnen. Wurde anders geloest - siehe oben:

            var configId = $('#page').attr('data-config-id');
            var id = formData['item-persistent-id'];
            var action = formData['action'];
            //if (EDIT action):
            window.open('?action=itemedit&config-id=' + configId + '&item-persistent-id=' + id, 'EDIT ' + id);
            */
        }),
        error: (function (jqXHR, textStatus, errorThrown) {
            console.error("ERROR: " + formData['action'] + ": " + textStatus + errorThrown);
        }),
        complete: // after succss/error
            (function (jqXHR, textStatus) {
                console.info('Done: ' + formData['action']);
            })
    });
    return true;
}