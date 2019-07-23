

/**
 * Alle Filter einer Liste und die Liste aktualisieren.
 * @param {type} filterInputElement Das Element, in dem etwas eingegeben wurde.
 * @returns {undefined}
 */
function filterListUpdate() {
    var noteItems = $(document).find('.dvz-js-itemlist__item');
    $(noteItems).removeClass("dvz-js-hidden");
    var totalCount = $(noteItems).length;
    // Input Text Filter
    var textFilters = $('.dvz-js-liste-filter-text');

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
            var filterOutItems = $(noteItems).filter("[" + thisSelectorAttribute + "]");

            for (termIndex = 0; termIndex < $(terms).length; termIndex++) {
                var t = terms[termIndex];
                var applyFilter = t.substr(0, 1) != '!' || t.length > 1;
                var negate = applyFilter && t.substr(0, 1) == '!';
                if (applyFilter) {
                    if (negate) {
                        t = t.substr(1);
                        var selectorIn = "[" + thisSelectorAttribute + "*='" + t + "']";
                        $(filterOutItems).filter(selectorIn).addClass("dvz-js-hidden");
                    } else {
                        var selectorNotIn = "[" + thisSelectorAttribute + "*='" + t + "']";
                        $(filterOutItems).filter(":not(" + selectorNotIn + ")").addClass("dvz-js-hidden");
                    }
                }
            }
        }
    }

    // Click Filter
    var clickFilters = $(document).find('.dvz-js-liste-filter-click');
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
            var filterOutItems = $(noteItems).filter(selectorOut);
            $(filterOutItems).addClass("dvz-js-hidden");
            outCount = $(filterOutItems).length;
        }
        if (selectorOutCompare === "!=''") {
            $(counterElement).addClass('e');
            $(filterElement).addClass('e');
        } else {
            $(counterElement).removeClass('e');
            $(filterElement).removeClass('e');
        }
    }
    hideHiddenItems(noteItems);

    // update art liste
    var artNodes= $('.dvz-js-artlist-art');
    $(artNodes).addClass("dvz-js-hidden");
    $(noteItems).filter(':not(.dvz-js-hidden)').each(function () {
        var noteItemArts = $(this).attr('data-filter-art');
        if(isString(noteItemArts)) {
            var parameters = noteItemArts.split(' ');
            $(artNodes).each(function () {
                var artNode = this;
                parameters.forEach(function(noteItemArt) {
                    var artNodeArt = $(artNode).attr('data-filter');
                    if (noteItemArt.startsWith(artNodeArt)) {
                        $(artNode).removeClass('dvz-js-hidden');
                    }

                })
            }, parameters);
        }
    });
    hideHiddenItems(artNodes);
}

function hideHiddenItems(itemList) {
    $(itemList).filter('.dvz-js-hidden').addClass("d-none");
    $(itemList).filter(':not(.dvz-js-hidden)').removeClass("d-none");
}