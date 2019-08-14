$(document).on('change.select2', function (event) {
    let select2 = $(event.target).closest('div').find('.select2');

    if (select2.length) {
        let val = $(event.target).val(),
            arrow = select2.find('span.select2-selection__arrow');

        if (val !== null && val !== '') {
            arrow.addClass('filled');
        } else {
            arrow.removeClass('filled');
        }
    }
});