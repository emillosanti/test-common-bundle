// @TODO it should be universal solution for any entity instead of hardcoded relation to contact
jQuery(function($) {
    $.fn.cardCollection = function(options) {
        var settings = $.extend({}, options);

        function getNewIndex(list) {
            var maxIndex = 0;
            list.children('li').each(function(index, elt) {
                var key = parseInt($(elt).data('index'));
                if (key > maxIndex) {
                    maxIndex = key;
                }
            });

            return maxIndex + 1;
        }

        return this.each(function() {
            var select = $(this),
                list = $('ul#' + select.data('list')),
                options = {
                    width: "100%",
                    placeholder: select.data('placeholder'),
                    language: {
                        errorLoading: function () {
                            return 'Les résultats ne peuvent pas être chargés.';
                        },
                        inputTooLong: function (args) {
                            var overChars = args.input.length - args.maximum;
                            return 'Supprimez ' + overChars + ' caractère' + ((overChars > 1) ? 's' : '');
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
                };

            if ($(this).hasClass('autocomplete-preloaded')) {
                var choicesattr = $(this).data('preloadedoptions');
                var mappedOptions = choicesattr.map(function (item) {
                    return {
                        id: item.id,
                        text: typeof item.transform !== 'undefined' && item.transform === false ? item.text : item.name,
                        contact_name: item.name,
                        contact_picture: item.picture,
                        contact_job: item.job
                    }
                });

                options["data"] = mappedOptions;
            } else {
              options["ajax"] = {
                  url: select.data('url'),
                  dataType: 'json',
                  delay: 300,
                  data: function (params) {
                      return {search: {query: encodeURIComponent(params.term)}};
                  },
                  processResults: function (data) {
                      var results = [];
                      if ("function" === typeof(data.map)) {
                          results = data
                              .map(function(item) {
                                  return {
                                      id: item.id,
                                      text: typeof item.transform !== 'undefined' && item.transform === false ? item.text : item.name,
                                      contact_name: item.name,
                                      contact_picture: item.picture,
                                      contact_job: item.job
                                  }
                              });
                      }

                      if (select.hasClass('contact-list-autocomplete') || select.parent().hasClass('contact-list-autocomplete') && data.length == 0) {
                          var enteredName = $('input.select2-search__field').val();
                          if (enteredName) {
                              var message = '<a class="empty-select2 create-contact-list" href="" data-name="' + enteredName + '" data-browser-history="false">' +
                                  '<i class="sam-ico add-contact-ico"></i>' +
                                  '<i class="fa fa-plus-circle"></i> ' +
                                  '<span class="text one-row"> ' +
                                      'Créer une nouvelle liste "' + enteredName +
                                  '"</span>' +
                                  '</a>';

                              results.push({ text: message })
                          }
                      }

                      return {
                          results: typeof selectTransformer === 'function' ? selectTransformer(results) : results
                      };
                  }
              };
            }

            select.select2(options);
            select.on('select2:select', function(e) {
                if (select.parent().hasClass('contact-list-autocomplete')) {
                    $('.save-list-result').html('');

                    var listModal = $("#contact-save-modal");
                    listModal.find('.modal-title, .modal-body').hide();
                    listModal.find('.modal-title.update, .modal-body.update').show();
                    listModal.find('.contact-count').text($('#contact-result-count').data('value'));
                    listModal.find('.list-name').text(e.params.data.text);
                    listModal.find('.add-to-list-submit-button').data('list', e.params.data.id);
                    listModal.find('.add-to-list-submit-button').data('action', 'update');
                    listModal.modal('show');
                    $('.modal-backdrop').prependTo('.side-panel-container > div').eq(0);
                    select.val([]).trigger('change');
                } else {
                    var data = e.params.data,
                        prototype = list.data('prototype'),
                        keys = Object.keys(data);
                    var template = prototype.replace(/__name__/g, getNewIndex(list));

                    console.log(keys);
                    console.log(data);

                    for (var i = 0; i < keys.length; i++) {
                        var value = data[keys[i]];
                        if (value) {
                            template = template.replace(new RegExp('__' + keys[i] + '__', 'g'), data[keys[i]]);
                        } else {
                            template = template.replace(new RegExp('__' + keys[i] + '__', 'g'), '');
                        }
                    }
                }

                var jqElement = $(template);

                var newId = jqElement.data('user');
                var existingIds = list.find('li').map(function(){return $(this).data('user')}).get();

                if (existingIds.indexOf(newId) == -1) {
                    jqElement.find('input').val(data.id);
                    list.append(jqElement);
                    select.val([]).trigger('change');
                }
            });

            list.on('click', '.remove-item', function(event) {
                event.preventDefault();
                $(this).closest('li').remove();
            });
        });
    };
});
