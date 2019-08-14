jQuery(function ($) {
    'use strict';

    var body = $('body');

    body.on('mouseenter', 'form.form-update-observable', function(e) {
        if ($(this).data('values')) return;

        // exclude ui hidden fields
        let fieldsExcluded = [];
        $(this).find('.excluded').each((i, el) => fieldsExcluded.push($(el).attr('name')));
        $(this).data('values', $(this).serializeArray().filter((field) => !fieldsExcluded.includes(field.name)));
    });

    body.on('change', 'form.form-update-observable', function(e) {
        let counter = 0,
            // target = $(e.target),
            observer = $(this).find('.form-update-observer'),
            formOrigin = $(this).data('values'),
            formUpdated = $(this).serializeArray();

        // we check the whole form on each change, it's slower on 0.0001 ms ) but more reliable (connected fields, js input updates, etc)
        let fields = [...new Set([...formOrigin, ...formUpdated].map((field) => field.name))];

        fields.forEach((name) => {
            let target = $(this).find('[name="' + name + '"]');
            let fieldsOrigin = formOrigin.filter(field => field.name === name);
            let fieldsUpdated = formUpdated.filter(field => field.name === name);

            if (target.hasClass('excluded')) return;

            switch (true) {
                // multifield
                case target.attr('type') === 'checkbox':
                    let oValues = fieldsOrigin.map((field) => field.value);
                    let uValues = fieldsUpdated.map((field) => field.value);
                    let diff = oValues.filter(v => !uValues.includes(v)).concat(uValues.filter(v => !oValues.includes(v))).length;
                    if (!diff) return;

                    counter += diff;
                    break;
               // old single field
               case fieldsOrigin.length === 1 && fieldsUpdated.length === 1:
                   if (fieldsOrigin[0].value !== fieldsUpdated[0].value) {
                       target.addClass('updated');
                       counter++;
                   } else {
                       target.removeClass('updated');
                   }
                   break;
               // new single field (added or removed)
               case fieldsOrigin.length === 0 || fieldsUpdated.length === 0:
                   target.addClass('updated');
                   counter = fieldsOrigin.length > fieldsUpdated.length ? counter + fieldsOrigin.length - fieldsUpdated.length : counter + fieldsUpdated.length - fieldsOrigin.length;
                   break;
           }
        });

        observer.text(counter ? counter > 1 ? counter + ' éléments modifiés' : 'Un élément modifié' : '');
    });

    $('.vich-file input[type="file"]').on('change', function () {
        $(this).prev().val($(this).val().split(/(\\|\/)/g).pop());
    });

    wrapSAMInputs();

    body.on('click', '.checkbox-wrapper', function(){
        $(this).toggleClass('checked');
        $(this).find('input[type="checkbox"]').attr('checked', $(this).hasClass('checked')).change();
    });

    //on radio always select clicked item
    body.on('click', '.radio-wrapper', function(){
        var clickedItem = $(this).find('input[type="radio"]').eq(0);
        var radioSiblings =  $('input[name="' + clickedItem.attr('name') + '"]');

        $(this).addClass('checked');

        radioSiblings.attr('checked', false);
        radioSiblings.parents('.radio-wrapper').not($(this)).removeClass('checked');

        clickedItem.attr('checked', true);

    });

    body.on('change', '.sam-checkbox', function(){
        updateSAMWrapper($(this), 'checkbox');
    });

    body.on('change', '.sam-radio', function(){
        updateSAMWrapper($(this), 'radio');
    });

    body.on('click', '.secondary-button.active', function(e){
        e.preventDefault();
        return false;
    });

});

function wrapSAMInputs(){
    wrapInputs('checkbox', 'sam-checkbox', 'checkbox-wrapper');
    wrapInputs('radio', 'sam-radio', 'radio-wrapper');
}

function wrapInputs(type, inputClass, wrapperClass){
    $('input[type="' + type + '"].' + inputClass).each(function(){

        if ($(this).parents('.' + wrapperClass).length == 0) {
            var wrapper = $('<span class="sam-input-wrapper ' + wrapperClass + '"></span>');

            wrapper.insertBefore($(this));
            $(this).appendTo(wrapper);

            updateSAMWrapper($(this), type);
        }

    });
}

function updateSAMWrapper(element, type){
    var parent = element.parents('.sam-input-wrapper').eq(0);

    if (element.is(':checked')) {

        parent.addClass('checked');

        if (type == 'radio') {
            $('input[name="' + element.attr('name') + '"]').not(element).parents('.sam-input-wrapper').removeClass('checked');
            $(this).parent('.sam-input-wrapper').addClass('checked');
        }

    } else {
        parent.removeClass('checked');
    }
}