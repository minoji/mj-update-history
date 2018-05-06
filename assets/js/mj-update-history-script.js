jQuery(document).ready(function ($) {

  $('#mjuh-output-text').submit(function (e) {
    e.preventDefault();
    $('#mjuh-output-area').html('<span class="spinner"></span>');
    $('#mjuh-output-area .spinner').addClass('is-active');

    var data = {
      'action': 'mjuh-output-text'
    };

    $.post(
      ajaxurl,
      data,
      function (response) {
        $('#mjuh-output-area .spinner').removeClass('is-active');
        $('#mjuh-output-area').html('<textarea id="mjuh-output-area-form" rows="20" cols="100"></textarea>');
        $('#mjuh-output-area-form').val(response.data.message);
      });
  });

});
