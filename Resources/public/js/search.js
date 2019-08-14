jQuery(function ($) {
    'use strict';

    var form = $('form.contact-book-search'),
        selectContactType = $('.select-contact-type'),
        selectJob = form.find('.job_autocomplete_wrapper select'),
        selectQuery = form.find('.query_autocomplete_wrapper select'),
        body = $('body'),
        loader = $('.loader'),
        results = $('.results-wrapper');

    var process = function() {
        $.ajax({
            url: form.attr('action'),
            method: 'GET',
            data: form.serialize(),
            beforeSend: function() {
                results.hide();
                loader.html('<div class="loading"><section class="loader"><svg class="spinner" width="65px" height="65px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg"><circle class="path" fill="none" stroke-width="6" stroke-linecap="round" cx="33" cy="33" r="30"></circle></svg></section></div>');
            }
        }).done(function (response) {
            if ('count' in response) {
                let $counter = $('#contact-results-count');
                if ($counter.length) {
                    $counter.html(response.count);
                    $counter.show();
                }
            }

            if ('html' in response) {
                results.html(response.html);
                handleResetButton(form);
            }

            window.history.pushState(null, null, form.attr('action') + '?' + form.serialize());
        }).always(function() {
            loader.html('');
            results.show();
        });
    };

    var handleResetButton = function(form) {
        var resetButton = $('.reset-button-container');

        if (resetButton.length > 0) {
            var filtersSet = false;

            $.each($(form).serializeArray(), function(_, field) {
                if (field.value != null && field.value != '') {
                    filtersSet = true;
                    return false;
                }
            });

            if (filtersSet && resetButton.is(':hidden')) {
                resetButton.removeClass('hide').hide().fadeIn(500);
            } else if(!filtersSet && resetButton.is(':visible')){
                resetButton.fadeOut(500);
            }

        }
    }

    form.on('click', 'input[type="checkbox"]', function () {
        var form = $(this).closest('form');
        process(form);
    });

    if (selectContactType && selectContactType.length != 0) {
        selectContactType.on('change', 'select', function() {
            var selectedOption = $(this).find(':selected');
            if (selectedOption && selectedOption.length > 0) {
                window.location = selectedOption.attr('data-url');
            }
        });
    }

    form.on('change', 'select', function () {
        var form = $(this).closest('form');
        process(form);
    });

    selectJob.select2({
        placeholder: "Rechercher une fonction",
        allowClear: true,
        language: {
            errorLoading: function () {
                return 'Les résultats ne peuvent pas être chargés.';
            },
            inputTooLong: function (args) {
                var overChars = args.input.length - args.maximum;
                return 'Supprimez ' + overChars + ' caractère' +
            ((overChars > 1) ? 's' : '');
            },
            inputTooShort: function (args) {
                var remainingChars = args.minimum - args.input.length;
                return 'Saisissez au moins ' + remainingChars + ' caractère' + ((remainingChars > 1) ? 's' : '');
            },
            loadingMore: function () {
                return 'Chargement de résultats supplémentaires…';
            },
            maximumSelected: function (args) {
                return 'Vous pouvez seulement sélectionner ' + args.maximum + ' élément' + ((args.maximum > 1) ? 's' : '');
            },
            searching: function () {
                return 'Recherche en cours…';
            },
            removeAllItems: function () {
                return 'Supprimer tous les articles';
            },
            noResults: function () {
                return "Aucun résultat.";
            }
        },
        escapeMarkup: function (markup) {
            return markup;
        },
        ajax: {
            url: selectJob.data('url'),
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return {
                    search: {
                        query: encodeURIComponent(params.term)
                    }
                };
            },
            processResults: function (data) {
                return {
                    results: typeof selectTransformer === 'function' ? selectTransformer(data) : data
                };
            }
        }
    });

    $('form.contact-book-search').on('click', '.tag-info', function () {
        var form = $(this).closest('form');
        $.ajax({
            url: form.attr('action'),
            method: 'GET',
            data: form.serialize()
        }).done(function (response) {
            $('.search-result').html(response);
        });
    });

    selectQuery.select2({
        placeholder: "",
        allowClear: true,
        language: {
            errorLoading: function () {
                return 'Les résultats ne peuvent pas être chargés.';
            },
            inputTooLong: function (args) {
                var overChars = args.input.length - args.maximum;
                return 'Supprimez ' + overChars + ' caractère' +
                    ((overChars > 1) ? 's' : '');
            },
            inputTooShort: function (args) {
                var remainingChars = args.minimum - args.input.length;
                return 'Saisissez au moins ' + remainingChars + ' caractère' + ((remainingChars > 1) ? 's' : '');
            },
            loadingMore: function () {
                return 'Chargement de résultats supplémentaires…';
            },
            maximumSelected: function (args) {
                return 'Vous pouvez seulement sélectionner ' + args.maximum + ' élément' + ((args.maximum > 1) ? 's' : '');
            },
            searching: function () {
                return 'Recherche en cours…';
            },
            removeAllItems: function () {
                return 'Supprimer tous les articles';
            },
            noResults: function () {
                return "Aucun résultat.";
            }
        },
        escapeMarkup: function (markup) {
            return markup;
        },
        ajax: {
            url: selectQuery.data('url'),
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return {
                    search: {
                        query: encodeURIComponent(params.term)
                    }
                };
            },
            processResults: function (data) {
                return {
                    results: typeof selectMultiTransformer === 'function' ? selectMultiTransformer(data) : data
                };
            }
        }
    });

    selectQuery.on('select2:selecting', function(e) {
        if (e.params && e.params.args.originalEvent && e.params.args.originalEvent.currentTarget) {
            let link = $(e.params.args.originalEvent.currentTarget).find('a');
            if (link) {
                e.preventDefault();
                let attr = link.attr('data-open-side-panel');
                if (typeof attr !== typeof undefined && attr !== false) {
                    body.trigger('load-side-panel', { url: link.attr('href') });
                } else {
                    window.location = link.attr('href');
                }
            }
        }
    });

    body.on('loader.start', function() {
        if (form && form.length > 0) {
            results.hide();
            loader.html('<div class="loading"><section class="loader"><svg class="spinner" width="65px" height="65px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg"><circle class="path" fill="none" stroke-width="6" stroke-linecap="round" cx="33" cy="33" r="30"></circle></svg></section></div>');
        }
    });

    body.on('legalentity.updated', function(event, data) {
        if (form && form.length > 0) {
            process(form);
        }
    });
});
