
/**
 * Registriert Events, die nicht Instanz-bezogen sind.
 * @returns {undefined}
 */
function registerGlobalEvents() {
  $('.on-click-hide-my-parent').on('click', function () {
    $(this).parent().addClass('hidden');
  });

  /**
   * input val in clipboard übernehmen
   */
  $("#copytokaz .copy-to-clipboard-trigger").on('click', function (event) {
    var text = $('#copytokaz input').val();
    copyToClipboard(text);
  });
}