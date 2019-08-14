jQuery(function ($) {
    'use strict';
    // @TODO move to automatic locale
    $.fn.datepicker.defaults.weekStart = 1;
	$.fn.datepicker.defaults.language = 'fr';

    $('input.datepicker').datepicker();

    $('.autocomplete-wrapper').autocomplete();
    $('.autocomplete-cards-wrapper').cardCollection();

    $('.tooltip-wrapper').tooltip({position: 'bottom'});

    // select2 tab fix
    jQuery(document).on('focus', '.select2', function() { jQuery(this).siblings('select').select2('open') });

    // sidebar
    $('#sidebarToggle').on('click', function() {
       $(this).closest('nav.navbar').toggleClass('toggled');
       $.ajax({
            url : Routing.generate('user_menu_toggled'),
            data: { 'toggled': $(this).closest('nav.navbar').hasClass('toggled') },
            method: 'POST'
        });
       $('body').toggleClass('navbar-toggled');
    });
});
