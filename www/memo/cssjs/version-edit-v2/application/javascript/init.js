
$(document).ready(function () {
   // initMoment();
    $('.dvz-js-summernote-editor').each(function () {
        initSummerNote(this, $(this).attr('placeholder'));
        console.log('summernote initialisiert');
        if (isString($(this).val()) && '' !== $(this).val()) {
            $(this).summernote('code', $(this).val());
        }
    });
    $('[data-toggle="tooltip"]').tooltip();

    filterListUpdate();
    $('.dvz-js-liste-filter-text').on('keyup', function () {
        filterListUpdate();
    });

    //$('input').on('change', function (event) {
    $('input').on('keyup', function (event) {
        $(this).change();
    });

    $('form').on('change', function (event) {
        // this uebergibt den form DOM node, event.target wuerde das INPUT oder andere Element uebergeben,
        // das original den Event fing.
        itemSave(this);
    });

    $('a.dvz-js-delete-action').on('click', function (event) {
        var id = $(event.target).attr('data-id');
        itemDelete(id);
        $('.dvz-js-remove-on-delete-' + id).remove();
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
