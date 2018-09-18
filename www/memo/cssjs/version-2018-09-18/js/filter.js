/**
 * Filter und Summen-Zeile einer Liste aktualisieren.
 * @param {type} list
 * @returns {undefined}
 */
function updateList(list) {
  filterListUpdate(list);
  summenListUpdate(list);
}

/**
 * Alle Filter aller Listen aktualisieren.
 * @returns {undefined}
 */
function filterUpdateAll() {
  $('.filter').each(function () {
    var list = findMyListParent(this);
    filterListUpdate(list);
  });
}

/**
 * Alle Filter einer Liste und die Liste aktualisieren.
 * @param {type} filterInputElement Das Element, in dem etwas eingegeben wurde.
 * @returns {undefined}
 */
function filterListUpdate(list) {
  $(list).find(':not(.list-parent) .filter').removeClass('active');
  $(list).find(':not(.list-parent) .filter-counter').removeClass('active');
  var targetInstances = $(list).find(":not(.list-parent) [data-element='instance']");
  $(targetInstances).removeClass("hidden");
  var totalCount = $(targetInstances).length;
  $(list).find(':not(.list-parent) .filter-total-count').html($(targetInstances).length);

  // Input Text Filter
  var filters = $(list).find(':not(.list-parent) input.filter.accepting-input');

  for (filterIndex = 0; filterIndex < $(filters).length; filterIndex++) {
    var filterElement = $(filters)[filterIndex];
    var counterElement = filterCounterElement(filterElement);
    var valueInput = $(filterElement).val().toLowerCase();
    valueInput = valueInput.trim();
    if (valueInput !== '') {
      $(filterElement).addClass('active');
      $(counterElement).addClass('active');
      var thisSelectorAttribute = $(filterElement).attr('data-selector-attribute');
      var terms = [];
      if (valueInput.indexOf(" ") !== -1) {
        terms = valueInput.split(" ");
      } else {
        terms = [valueInput];
      }
      var filterOutInstances = $(targetInstances).filter("[" + thisSelectorAttribute + "]");

      for (termIndex = 0; termIndex < $(terms).length; termIndex++) {
        var t = terms[termIndex];
        var applyFilter = t.substr(0, 1) != '!' || t.length > 1;
        var negate = applyFilter && t.substr(0, 1) == '!';
        if (applyFilter) {
          if (negate) {
            t = t.substr(1);
            var selectorIn = "[" + thisSelectorAttribute + "*='" + t + "']";
            $(filterOutInstances).filter(selectorIn).addClass("hidden");
          } else {
            var selectorNotIn = "[" + thisSelectorAttribute + "*='" + t + "']";
            $(filterOutInstances).filter(":not(" + selectorNotIn + ")").addClass("hidden");
          }
        }
      }
    }
    var outCount = $(filterOutInstances).filter('.hidden').length;
    var selectedCount = totalCount - outCount;
    $(counterElement).find('.filter-selected-count').html(selectedCount);
  }
  // Click Filter
  var clickFilters = $(list).find('.filter.accepting-click');
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
      var thisSelectorAttribute = $(filterElement).attr('data-selector-attribute');
      var selectorOut = "[" + thisSelectorAttribute + selectorOutCompare + "]";
      var filterOutInstances = $(targetInstances).filter(selectorOut);
      $(filterOutInstances).addClass("hidden");
      outCount = $(filterOutInstances).length;
    }
    if (selectorOutCompare === "!=''") {
      $(counterElement).addClass('e');
      $(filterElement).addClass('e');
    } else {
      $(counterElement).removeClass('e');
      $(filterElement).removeClass('e');
    }
    selectedCount = totalCount - outCount;
    $(counterElement).find('.filter-selected-count').html(selectedCount);
  }

  var outCountAll = $(targetInstances).filter('.hidden').length;
  var inCountAll = totalCount - outCountAll;
  $(list).find('.filter-counter[data-selector-attribute="any"] .filter-selected-count').html(inCountAll);
}

/**
 * Liefert das Element, in dem der Counter des Filter Elements angezeigt wird.
 * @param {type} filterElement
 * @returns {jQuery}
 */
function filterCounterElement(filterElement) {
  var selector = $(filterElement).attr('data-selector-attribute');
  var parent = findMyListParent(filterElement);
  return $(parent).find('.filter-counter[data-selector-attribute="' + selector + '"]').first();
}