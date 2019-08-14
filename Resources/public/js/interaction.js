jQuery(function ($) {
    'use strict';

    $('body').on('submit', '.activity-form', function (event) {
        event.preventDefault();
        let form = $(this);
        var submitButton = $(this).find('button[type="submit"]');
        if (submitButton) {
            submitButton.attr('disabled', 'disabled');
        }
        $.ajax({
            url: form.attr('action'),
            method: form.attr('method'),
            data: form.serialize(),
        }).done((response) => {
            if (response.redirectUrl) {
                document.location = response.redirectUrl;
            } else {
                location.reload();
            }
        }).always(function() {
            if (submitButton) {
                submitButton.removeAttr('disabled');
            }
        });
    });

    $('body').on('click', '.interaction-form-toggle', () => {
        $('.interaction-form').toggleClass('hidden');
    });

    $('.interactions').on('click', '.interaction-edit', function (event) {
        event.preventDefault();
        $('.interaction-form').removeClass('hidden');
        var type = $(this).data('type');
        $.ajax({
            url: $(this).attr('href'),
            beforeSend: function() {
                $('#' + type).html('<div class="loading"><section class="loader"><svg class="spinner" width="65px" height="65px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg"><circle class="path" fill="none" stroke-width="6" stroke-linecap="round" cx="33" cy="33" r="30"></circle></svg></section></div>');
                $('.interaction-form a[href="#' + type + '"]').tab('show');
            },
        }).done(function (response) {
            $('#' + type).html(response);
        });
    });
});
