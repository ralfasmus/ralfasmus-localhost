
/**
 * Sendet eine Instanz zum Speichern.
 * @param {type} instance
 * @returns {undefined}
 */
function itemSave(formDomNode) {
    var formData = {};
    $(formDomNode).find('.dvz-js-persistent').each(function () {
        var fieldName = $(this).attr('name');
        var fieldOldValue = $(this).attr('value');
        var fieldNewValue = $(this).val();

        if ($(this).hasClass('dvz-js-summernote-editor')) {
            fieldNewValue = $(this).summernote('code');
            //alert(fieldNewValue);
        }
        formData[fieldName] = fieldNewValue;
    //    console.log("Save Instance Field: " + fieldName + "=" + fieldNewValue);
    });
    //console.log(formData);
    var success = saveInstanceForm(formData);
    if (success) {
        //logInfo('Success. Instance ' + instanceId + ' saved.');
    }
}
/**
 * Sendet einen AJAX Request zum Backup.
 * @param {type} instance
 * @returns {undefined}
 */
function itemBackup(id) {
    return itemAction(id, 'backup');
}

/**
 * Sendet einen AJAX Request zum Loeschen und loescht die Instance aus dem DOM.
 * @param {type} instance
 * @returns {undefined}
 */
function itemDelete(id) {
    return itemAction(id, 'delete');
}
/**
 * Sendet einen AJAX Request zum Loeschen und loescht die Instance aus dem DOM.
 * @param {type} instance
 * @returns {undefined}
 */
function itemAction(id, action) {
    var formData = {};
    $.ajax(
	   'index.php?action=item-' + action + '&id=' + id, // the url where we want to POST
	   {
        type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
        data: formData, // our data object
        dataType: 'text', // what type of data do we expect back from the server
        encode: true,
        //async: false,
        success: (function (data, textStatus, jqXHR) {
            console.log("SUCCESS: Instance " + action + ": " + textStatus + id);
        }),
        error: (function (jqXHR, textStatus, errorThrown) {
            console.error("ERROR: Instance " + action + ": " + textStatus + errorThrown + id);
        }),
        complete: // after succss/error
            (function (jqXHR, textStatus) {
                console.log('Done.');
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
    var dataInstanceId = formData['id'];
    $.ajax('index.php?action=item-save', // the url where we want to POST,
	  {
        type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
        data: formData, // our data object
        dataType: 'text', // what type of data do we expect back from the server
        encode: true,
        //async: false,
        success: (function (data, textStatus, jqXHR) {
            console.log("Saved Instance: " + textStatus + data);
        }),
        error: (function (jqXHR, textStatus, errorThrown) {
            console.error("Saved Instance: " + textStatus + errorThrown + data);
        }),
        complete: // after succss/error
            (function (jqXHR, textStatus) {
                console.log('done');
            })
    });
    return true;
}