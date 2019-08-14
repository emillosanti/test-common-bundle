jQuery(function ($) {
    'use strict';

    var body = $('body');

    body.on('click', '.btn-notification-accept, .btn-notification-deny', function(event) {
        event.preventDefault();
        $.ajax({
            url: $(this).attr('href')
        }).done(function(response) {
            var modal = $(response);
            body.append(modal);
            modal.on('hide.bs.modal', function() {
                $(this).remove();
            });
            modal.modal('show');
        });
    });
});
