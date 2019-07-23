
$(document).ready(function () {
    // Zeitfunktionen - Library:
    initMoment();
    // Server Log Messages nach dem Laden der Seite anzeigen im DIV
    $('.showajaxresult').html($('.ajaxresult'));

    // HTML Editor:
    $('.dvz-js-summernote-editor').each(function () {
        initSummerNote(this, $(this).attr('placeholder'));
        console.info('summernote initialisiert');
        if (isString($(this).val()) && '' !== $(this).val()) {
            $(this).summernote('code', $(this).val());
        }
    });

    // Bootstrap tooltip:
    $('[data-toggle="tooltip"]').tooltip();

    // Art anklicken und filter ergaenzen:
    $('.dvz-js-artlist-art').on('click', function () {
        art = $(this).attr('data-filter');
        $('.dvz-js-liste-filter-art').val(art);
        filterListUpdate();
    });

    // einmal die Liste der Items entsprechend (voreingestellter) Filter aktualisieren:
    filterListUpdate();
    // Trigger: Aenderung im Filter-Feld = Liste aktualisieren
    $('.dvz-js-liste-filter-text').on('keyup', function () {
        filterListUpdate();
    });

    // Trigger: Taste im Item Form Feld = Form Change triggern
    $('input').on('keyup', function (event) {
        $(this).change();
    });

    // Trigger: Aenderung in der Item Form = Item speichern
    $('form').on('change', function (event) {
        // this uebergibt den form DOM node, event.target wuerde das INPUT oder andere Element uebergeben,
        // das original den Event fing.
        itemSave(this);
    });

    // Trigger: Aenderung im hidden Feld #nextpage = neues Browserfenster oeffnen:
    $('#nextpage').on('change', function (event) {
        var href = this.value;
        var title = this.dataset.nextpagetitle;
        //alert(title + ' : ' + href);
        window.open(href, title);
    });

    // Trigger: Klick auf Button CREATE ITEM
    $('.dvz-js-create-textnote-action').on('click', function (event) {
        event.preventDefault();
        var id = newId();
        itemCreate({'item-persistent-id' : id, 'item-persistent-view' : 'textnote', 'item-persistent-possible-views' : 'textnote'});
    });

       // Trigger: Klick auf Button BACKUP ITEM
        $('.dvz-js-backup-action').on('click', function (event) {
            event.preventDefault();
            var id = $(event.target).attr('data-id');
            itemBackup({'item-persistent-id' : id});
        });

    // Trigger: Klick auf Button ITEM SAVE
    $('.dvz-js-save-action').on('click', function (event) {
        event.preventDefault();
        $(this).change();
    });

    // Trigger: Klick auf Button ITEM DELETE
    $('.dvz-js-delete-action').on('click', function (event) {
        event.preventDefault();
        var id = $(event.target).attr('data-id');
        itemDelete({'item-persistent-id' : id});
        $('.dvz-js-remove-on-delete-' + id).remove();
        filterListUpdate();
    });

    // Trigger: Klick auf Button ITEM HIDE
    $('.dvz-js-hide-action').on('click', function (event) {
        event.preventDefault();
        var id = $(event.target).attr('data-id');
        $('.dvz-js-remove-on-hide-' + id).remove();
        filterListUpdate();
    });
});

/**
 * Rich Text Editor initialisieren fuer das input element und wenn Wert leer ist,
 * den placeHolder darstellen.
 * @param {type} element
 * @param {type} placeHolder
 * @returns {undefined}
 */
function initSummerNote(element, placeHolder) {
    $(element).summernote({
 //       width: 1200,
		  height: 200,                 // set editor height
		  minHeight: null,             // set minimum height of editor
		  maxHeight: null,             // set maximum height of editor
        //placeholder: placeHolder,
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
            onKeyup: function(event) {
                $(event.target).change();
            }
            /*
            onChange: function (contents) {
                var success = itemSave();
            }
            */
        }
    });

}
