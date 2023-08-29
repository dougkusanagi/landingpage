(function() {
  const fnPostReaded = async function(id) {
    $.ajax({
        url: URL_POST_READED_AJAX,
        type: 'POST',
        data: {
          _token: CSRF_TOKEN,
          id: id
        },
        success: function (data) {
          // do nothing
        }
    });
  };

  $('.btn-show-content').on('click', function() {
    var id = $(this).data('id');
    var readed = $(this).data('readed');
    var subject = $(this).data('subject');
    var content = $(this).data('content');
    $('#modalContent .modal-title').html(subject);
    $('#modalContent .modal-body').html(content);
    $('#modalContent').modal();

    if(readed != '1'){
      fnPostReaded(id);
    }
  });
})();