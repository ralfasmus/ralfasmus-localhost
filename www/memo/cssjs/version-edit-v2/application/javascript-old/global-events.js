
/**
 * Registriert Events, die nicht Instanz-bezogen sind.
 * @returns {undefined}
 */
function registerGlobalEvents() {
  $('.on-click-hide-my-parent').on('click', function () {
    $(this).parent().addClass('hidden');
  });

}