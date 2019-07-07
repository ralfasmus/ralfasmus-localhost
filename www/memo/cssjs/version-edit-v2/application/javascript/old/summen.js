

function selectorInputElements(zeitraum, dataInstanceType, inputElementName) {
  return '[data-loop="' + dataInstanceType + '"] [data-element=instance][data-instance-type=' + dataInstanceType + '][data-select-datum*=' + zeitraum + '] input[name=' + inputElementName + ']';
}
