jQuery(document).ready(function ($) {

  $('#mjuh-output-text').click(function (e) {
    e.preventDefault();
    $('#mjuh-output-area').html('<span class="spinner"></span>');
    $('#mjuh-output-area .spinner').addClass('is-active');
    var url = location.href;

    var data = {
      'action': 'mjuh_output_text',
      'url': url
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
