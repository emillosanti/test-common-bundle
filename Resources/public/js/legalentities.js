jQuery(function ($) {
    'use strict';

    var $legalEntitiesChooser = $('.legal-entities-select-wrapper'),
        $form = $('.legal-entities-select-wrapper form'),
        body = $('body');

    // update choosen legal entity
    $legalEntitiesChooser.on('change', 'select', function (event) {
        event.preventDefault();

        body.trigger('loader.start');
        $.ajax({
            url : Routing.generate('legal_entity_chooser'),
            data: $form.serialize(),
            method: 'POST'
        }).always(function() {
            body.trigger('legalentity.updated');
        });
    });
});