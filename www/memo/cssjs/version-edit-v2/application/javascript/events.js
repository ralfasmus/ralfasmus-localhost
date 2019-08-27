/**
 * Alle Filter einer Liste und die Liste aktualisieren.
 * @param {type} filterInputElement Das Element, in dem etwas eingegeben wurde.
 * @returns {undefined}
 */
function filterListUpdate() {
    var notes = $(document).find('.memo-js-notelist__note');
    $(notes).removeClass("memo-js-hidden");
    var totalCount = $(notes).length;
    // Input Text Filter
    var textFilters = $('.memo-js-liste-filter-text');

    for (filterIndex = 0; filterIndex < $(textFilters).length; filterIndex++) {
        var filterElement = $(textFilters)[filterIndex];
        var valueInput = $(filterElement).val().toLowerCase();
        valueInput = valueInput.trim();
        if (valueInput !== '') {
            $(filterElement).addClass('active');
            var thisSelectorAttribute = $(filterElement).attr('data-dvz-selector-attribute');
            var terms = [];
            if (valueInput.indexOf(" ") !== -1) {
                terms = valueInput.split(" ");
            } else {
                terms = [valueInput];
            }
            var filterOutNotes = $(notes).filter("[" + thisSelectorAttribute + "]");

            for (termIndex = 0; termIndex < $(terms).length; termIndex++) {
                var t = terms[termIndex];
                var applyFilter = t.substr(0, 1) != '!' || t.length > 1;
                var negate = applyFilter && t.substr(0, 1) == '!';
                if (applyFilter) {
                    if (negate) {
                        t = t.substr(1);
                        var selectorIn = "[" + thisSelectorAttribute + "*='" + t + "']";
                        $(filterOutNotes).filter(selectorIn).addClass("memo-js-hidden");
                    } else {
                        var selectorNotIn = "[" + thisSelectorAttribute + "*='" + t + "']";
                        $(filterOutNotes).filter(":not(" + selectorNotIn + ")").addClass("memo-js-hidden");
                    }
                }
            }
        }
    }

    // Click Filter
    var clickFilters = $(document).find('.memo-js-liste-filter-click');
    for (i = 0; i < $(clickFilters).length; i++) {
        var outCount = 0;
        var filterElement = $(clickFilters)[i];
        var counterElement = filterCounterElement(filterElement);
        var clickState = $(filterElement).attr('data-click');
        if (typeof clickState == 'string' && clickState !== '') {
            $(filterElement).addClass('active');
            $(counterElement).addClass('active');
            var selectorOutCompare = clickState === 'click1'
                ? ($(filterElement).hasClass('on-first-click-show-empty') ? "!=''" : "=''")
                // Filter Status click2:
                : ($(filterElement).hasClass('on-first-click-show-empty') ? "=''" : "!=''");
            var thisSelectorAttribute = $(filterElement).attr('data-dvz-selector-attribute');
            var selectorOut = "[" + thisSelectorAttribute + selectorOutCompare + "]";
            var filterOutNotes = $(notes).filter(selectorOut);
            $(filterOutNotes).addClass("memo-js-hidden");
            outCount = $(filterOutNotes).length;
        }
        if (selectorOutCompare === "!=''") {
            $(counterElement).addClass('e');
            $(filterElement).addClass('e');
        } else {
            $(counterElement).removeClass('e');
            $(filterElement).removeClass('e');
        }
    }
    hideHiddenNotes(notes);

    // update art liste
    var artNodes = $('.memo-js-artlist-art');
    $(artNodes).addClass("memo-js-hidden");
    $(notes).filter(':not(.memo-js-hidden)').each(function () {
        var noteNotesArts = $(this).attr('data-filter-art');
        if (isString(noteNotesArts)) {
            var parameters = noteNotesArts.split(' ');
            $(artNodes).each(function () {
                var artNode = this;
                parameters.forEach(function (noteNotesArt) {
                    var artNodeArt = $(artNode).attr('data-filter');
                    if (noteNotesArt.startsWith(artNodeArt)) {
                        $(artNode).removeClass('memo-js-hidden');
                    }

                })
            }, parameters);
        }
    });
    hideHiddenNotes(artNodes);
}

function hideHiddenNotes(noteList) {
    $(noteList).filter('.memo-js-hidden').addClass("memo-hidden");
    $(noteList).filter(':not(.memo-js-hidden)').removeClass("memo-hidden");
}