

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
            var filterOutInstances = $(noteItems).filter("[" + thisSelectorAttribute + "]");

            for (termIndex = 0; termIndex < $(terms).length; termIndex++) {
                var t = terms[termIndex];
                var applyFilter = t.substr(0, 1) != '!' || t.length > 1;
                var negate = applyFilter && t.substr(0, 1) == '!';
                if (applyFilter) {
                    if (negate) {
                        t = t.substr(1);
                        var selectorIn = "[" + thisSelectorAttribute + "*='" + t + "']";
                        $(filterOutInstances).filter(selectorIn).addClass("dvz-js-hidden");
                    } else {
                        var selectorNotIn = "[" + thisSelectorAttribute + "*='" + t + "']";
                        $(filterOutInstances).filter(":not(" + selectorNotIn + ")").addClass("dvz-js-hidden");
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
            var filterOutInstances = $(noteItems).filter(selectorOut);
            $(filterOutInstances).addClass("dvz-js-hidden");
            outCount = $(filterOutInstances).length;
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
    var artItems = $('.dvz-js-artlist__item');
    $(artItems).addClass("dvz-js-hidden");
    $(noteItems).filter(':not(.dvz-js-hidden)').each(function () {
        var arts = $(this).attr('data-filter-art');
        if(isString(arts)) {
            var parameters = [arts];
            console.log(arts);
            $(artItems).each(function () {
                var arts = parameters[0];
                var art = $(this).text();
                if (arts.includes(art)) {
                    $(this).removeClass('dvz-js-hidden');
                }
            }, parameters);
        }
    });
    hideHiddenItems(artItems);
}

function hideHiddenItems(itemList) {
    $(itemList).filter('.dvz-js-hidden').addClass("d-none");
    $(itemList).filter(':not(.dvz-js-hidden)').removeClass("d-none");
}