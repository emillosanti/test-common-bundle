jQuery(function ($) {
    'use strict';

    var btn = $('.mobile-panel-btn'),
        isPending = false;

    btn.on('click', function(event) {
        event.preventDefault();
        if (!isPending) {
            var panelId = $(this).attr('href');
            isPending = true;
            $(panelId).fadeToggle(function(){
                isPending = false;
            });
        }
    });
});
