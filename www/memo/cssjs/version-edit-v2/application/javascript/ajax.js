/**
 * Hilfsmethode zum Senden einer Instanz zum Speichern.
 * @param {type} formData
 * @returns {Boolean}
 */
function saveNotesForm(formData) {
    formData['processor-method'] = 'noteSave';
    formData['backupextension'] = '_backup_' + jetzt('YYYY-MM-DD-HH');
    return executeAjax(formData);
}

/**
 * Sendet eine Instanz zum Speichern.
 * @param {type} note
 * @returns {undefined}
 */
function noteSave(formDomNode) {
    var formData = {};
    // Felder des Notes einsammeln
    $(formDomNode).find('[name^=note-]').each(function () {
        var fieldName = $(this).attr('name');
        var fieldOldValue = $(this).attr('value');
        var fieldNewValue = $(this).val();
        if (fieldName == 'note-persistent-datetimesaved') {
            fieldNewValue = jetzt('YYYY-MM-DD-HH-mm-ss');
            $(this).val(fieldNewValue);
        }
        if ($(this).hasClass('memo-js-summernote-editor')) {
            fieldNewValue = $(this).summernote('code');
            //alert(fieldNewValue);
        }
        formData[fieldName] = fieldNewValue;
        console.info("Save Notes Field: " + fieldName + "=" + fieldNewValue);
    });

    //console.info(formData);
    // Daten versenden
    var success = saveNotesForm(formData);
    if (success) {
        console.info('Success. Notes saved.');
    }
}

/**
 * Sendet einen AJAX Request zum Erzeugen eines neuen Notes.
 * @param {type} note
 * @returns {undefined}
 */
function noteCreate(formData) {
    var _jetzt = jetzt('YYYY-MM-DD-HH-mm-ss');
    // Felder-Defaults setzen
    formData['note-persistent-datetimecreated'] = _jetzt;
    formData['note-persistent-datetimesaved'] = _jetzt;

    // Url fuer das window.open erstellen, dass nach dem Speichern des neuen Notes ausgefuehrt werden soll,
    // um das neue Notes in einem neuen EDIT Browser-Fenster zu oeffnen
    var configId = $('#page').attr('data-config-id');
    var noteId = formData['note-persistent-id'];
    formData['nextpagetitle'] = 'NEW id:' + noteId;
    //formData['nextpagehref'] = '?action=noteedit&config-id=' + configId + '&note-persistent-id=' + noteId;
    formData['nextpagehref'] = '?processor-class=ProcessorView&processor-method=getHtml&processor-class-properties[view]=noteedit&config-id=' + configId + '&note-persistent-id=' + noteId;

    var success = saveNotesForm(formData);
    if (success) {
        // funktioniert hier nur, wenn PHP-seitig ein sleep eingebaut wird, um zu warten,
        // bis das saveNotesForm() oben serverseitig damit fertig geworden ist, das neue Notes
        // zu erzeugen und persistent zu speichern. Wurde besser geloest (siehe ececuteAjax):
        //
        // window.open('?action=noteedit&config-id=' + configId + '&note-persistent-id=' + id, 'EDIT ' + id);
    }
}

/**
 * Sendet einen AJAX Request zum Backup.
 * @param {type} note
 * @returns {undefined}
 */
function noteBackup(formData) {
    formData['processor-method'] = 'noteBackup';
    formData['backupextension'] = '_backup_' + jetzt('YYYY-MM-DD-HH-mm-ss');
    return executeAjax(formData);
}

/**
 * Sendet einen AJAX Request zum Loeschen und loescht die Notes aus dem DOM.
 * @param {type} note
 * @returns {undefined}
 */
function noteDelete(formData) {
    formData['processor-method'] = 'noteDelete';
    formData['backupextension'] = '_backup_' + jetzt('YYYY-MM-DD-HH-mm-ss');
    return executeAjax(formData);
}

/**
 * Sendet einen AJAX Request, um eine save|delete|backup Action auszufuehren.
 * Das HTML der Response wird am Ende der Seite eingebaut, damit gelieferte script tags ausgefuehrt werden.
 * So kann der Server indirekt etwas in die Browser-Konsole schreiben, z.B. PHP Stack Traces.
 * @param {type} note
 * @returns {undefined}
 */
function executeAjax(formData) {
    // config-id immer mitsenden, damit die Konfiguration, unter der die Seite angezeigt wird, auch auf dem
    // Server bekannt ist, wenn er die AJAX Action ausfuehrt.
    // ?processor-class=ProcessorAction&processor-method=noteSave
    var configId = $('#page').attr('data-config-id');
    formData['config-id'] = configId;
    // Base View festlegen.
    formData['processor-class'] = 'ProcessorAction';
    console.info("executeAjax: " + formData['processor-method']);
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
                // hier getriggert werden. Genutzt fuer create = save new note + open for edit
                var nextPageHref = formData['nextpagehref'];
                if (typeof nextPageHref !== "undefined" && '' != nextPageHref && null != nextPageHref) {
                    var nextPageTitle = formData['nextpagetitle'];
                    if (typeof nextPageTitle === "undefined") {
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
                var id = formData['note-persistent-id'];
                var action = formData['action'];
                //if (EDIT action):
                window.open('?action=noteedit&config-id=' + configId + '&note-persistent-id=' + id, 'EDIT ' + id);
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