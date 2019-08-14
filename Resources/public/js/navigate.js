jQuery(function ($) {
    'use strict';

    init();
    var toggleButton = '.topbar .toggle';
    var mainContent = '.body .main-container';

    $(toggleButton).on('click', function(e) {
        e.preventDefault();
        $(mainContent).toggleClass('slide');
    });

    $(mainContent).on('click', function(e) {
        if (!$(e.target).closest(toggleButton).length) {
            $(this).removeClass('slide');
        }
    });

    function init() {
        tabHashed();
    }

    /**
     * extended tabs functional
     * update location hash + show the right active tab based on hash from the url
     */
    function tabHashed() {
        // Javascript to enable link to tab
        let url = document.location.toString();
        if (url.match('#')) {
            $('.nav-tabs a[href="#' + url.split('#')[1] + '"]').tab('show');
        }
        // Change hash for page-reload
        $('.nav-tabs.hashed a').on('shown.bs.tab', function (e) {
            window.location.hash = e.target.hash;
        });
    }
});
