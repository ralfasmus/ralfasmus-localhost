
/**
 * Setzt einen sortButton zurueck auf : nicht sortiert
 * @param {type} button
 * @returns {undefined}
 */
function resetSortButton(button) {
  if ($(button).hasClass('active')) {
    $(button).toggleClass('sort-up');
    $(button).toggleClass('sort-down');
  }
  $(button).removeClass('active');
}


/**
 * Liefert eine nach attrName sortierte Liste von Instanz Elementen.
 *
 * @param {type} parentElementOfToBeSortedElements
 * @param {type} attrName
 * @returns {}
 */
function sortElements(parentElementOfToBeSortedElements, sortAttr, sortDown) {
  var sortInstanceType = $(parentElementOfToBeSortedElements).attr('data-loop');
  var instances = parentElementOfToBeSortedElements.children('[data-element="instance"][data-instance-type="' + sortInstanceType + '"]');
  instances.detach().sort(function (a, b) {
    var av = $(a).attr(sortAttr);
    var bv = $(b).attr(sortAttr);
    var numeric = ($.isNumeric(av) || av == '') && ($.isNumeric(bv) || bv == '');
    if (numeric) {
      av = av == '' ? 0 : av;
      bv = bv == '' ? 0 : bv;
      av = numberOrDefault(av, 0);
      bv = numberOrDefault(bv, 0);
    }
    result = (sortDown) ? ((av <= bv) ? ((av < bv) ? 1 : 0) : -1)
        : (av >= bv) ? ((av > bv) ? 1 : 0) : -1;
    return result;
  });

  parentElementOfToBeSortedElements.append(instances);
}



/**
 * Liefert eine nach attrName sortierte Liste von Instanz Elementen.
 *
 * @param {type} parentElementOfToBeSortedElements
 * @param {type} attrName
 * @returns {}
 */
function sortArray(valueArray, sortDown, removeDuplicates) {
  valueArray.sort(function (a, b) {
    var av = a;
    var bv = b;
    var numeric = ($.isNumeric(av) || av == '') && ($.isNumeric(bv) || bv == '');
    if (numeric) {
      av = av == '' ? 0 : av;
      bv = bv == '' ? 0 : bv;
      av = numberOrDefault(av, 0);
      bv = numberOrDefault(bv, 0);
    }
    result = (sortDown) ? ((av <= bv) ? ((av < bv) ? 1 : 0) : -1)
        : (av >= bv) ? ((av > bv) ? 1 : 0) : -1;
    return result;
  });
  var resultArray = [];
  for (i = 0; i < valueArray.length - 1; i++) {
    if (valueArray[i] != valueArray[i + 1]) {
      resultArray.push(valueArray[i]);
    }
  }
  resultArray.push(valueArray[valueArray.length - 1]);
  return resultArray;
}