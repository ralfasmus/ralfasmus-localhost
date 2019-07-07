
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
 * Sendet einen AJAX Request zum Loeschen und loescht die Instance aus dem DOM.
 * @param {type} instance
 * @returns {undefined}
 */
function itemDelete(id) {
    var formData = {};
    $.ajax({
        type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
        url: 'index.php?action=item-delete&id=' + id, // the url where we want to POST
        data: formData, // our data object
        dataType: 'text', // what type of data do we expect back from the server
        encode: true,
        //async: false,
        success: (function (data, textStatus, jqXHR) {
            console.log("Deleted Instance: " + textStatus + id);
        }),
        error: (function (jqXHR, textStatus, errorThrown) {
            console.error("Deleted Instance: " + textStatus + errorThrown + id);
        }),
        complete: // after succss/error
            (function (jqXHR, textStatus) {
                console.log('done');
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
    $.ajax({
        type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
        url: 'index.php?action=item-save', // the url where we want to POST
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