jQuery(function($) {
    $.fn.autocomplete = function(options) {
        var settings = $.extend({
            noResults: function(){
                return "Aucun résultat";
            }
        }, options);
        return this.each(function() {
            var wrapper = $(this),
                select = $('<select class="form-control"></select>');

            // remove any stale instances
            $(this).find('select.select2-hidden-accessible').remove();
            $(this).find('span.select2-container--default').remove();

            if (wrapper.data('value') && wrapper.data('label')) {
                select.append('<option selected="selected" value="' + wrapper.data('value') +'">' + wrapper.data('label') + '</option>');
            }

            wrapper.append(select);
            select.select2({
                width: '100%',
                placeholder: wrapper.data('placeholder') ? wrapper.data('placeholder') : '',
                allowClear: true,
                minimumInputLength: 1,
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
                    noResults: settings.noResults
                },
                escapeMarkup: function (markup) {
                    return markup;
                },
                ajax: {
                    url: wrapper.data('url'),
                    dataType: 'json',
                    delay: 300,
                    data: function (params) {
                        return {
                            search: { query: encodeURIComponent(params.term) }
                        };
                    },
                    processResults: function (data) {
                        if (wrapper.find('.new-select2-wrapper').length) {
                            if (wrapper.data('url404')) {
                                // @TODO handle special chars
                                var link = wrapper.data('url404') + (wrapper.data('url404').indexOf('?') >= 0 ? '&' : '?') + 'name=' + $('input.select2-search__field').val();
                                wrapper.find('.new-select2').attr('href', link);
                            }

                            data.push({text: wrapper.find('.new-select2-wrapper').html()});
                        }

                        return {
                            results: typeof selectTransformer === 'function' ? selectTransformer(data) : data,
                            pagination: {more: false}
                        };
                    }
                }
            });

            select.on('change', function() {
                wrapper.find('input[type="hidden"]').val($(this).val()).trigger('change');
            });

            select.on('select2:select', function () {
                if (typeof wrapper.data('clear') !== 'undefined') {
                    select.val([]).trigger('change');
                }
            });
        });
    };
});
