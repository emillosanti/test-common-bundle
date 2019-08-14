jQuery(function ($) {
    'use strict';

    var body = $('body'),
        overlay = $('.side-panel-overlay'),
        container = $('.side-panel-container'),
        containerStack = [],
        lastEventOnComplete,
        openerFrom,
        useBrowserHistory = true,
        sidePanelPending = false;

    function isSidePanelOpended() {
        return !container.children('div').first().is(':empty');
    }

    function openSidePanel(url, eventOnComplete, urlPreload, browserHistory, from) {
        if ("undefined" !== typeof(urlPreload)) {
            $.ajax({
                url: urlPreload,
                async: false,
            }).done(function (response) {
                if (response.redirectUrl) {
                    document.location = response.redirectUrl;
                } else if (response.reloadPage === true) {
                    location.reload();
                } else {
                    doOpenSidePanel(url, eventOnComplete, browserHistory, from)
                }
            });
        } else {
            doOpenSidePanel(url, eventOnComplete, browserHistory, from);
        }
    }

    function doOpenSidePanel(url, eventOnComplete, browserHistory, from) {
        if (sidePanelPending) {
            return;
        }
        sidePanelPending = true;
        useBrowserHistory = browserHistory !== false;
        openerFrom = from;
        startLoading();
        overlay.fadeIn();
        container.fadeIn();
        container.addClass('side-toggle');
        if (useBrowserHistory) {
            history.pushState(null, null, url);
        }
        closeAllSelect2();
        pushContainerStack(url, eventOnComplete);
        $.ajax({
            url: url
        })
        .catch(function(response) {
            $.ajax({ url: Routing.generate(response.status && response.status == 403 ? 'errors_side_403' : 'errors_side_5xx') })
                .done(function(response) {
                    container.children('div').html(response);
                })
                .catch(function() {
                    container.children('div').html(response);
                })
                .always(function() {
                    body.addClass('no-scroll');
                    container.children('div').fadeIn();
                    if (eventOnComplete) {
                        body.trigger(eventOnComplete, {openerFrom: openerFrom});
                    }
                });
        })
        .done(function(response) {
            body.addClass('no-scroll');
            container.children('div').html(response);
            container.children('div').fadeIn();
            // focus first form field
            $(container.children('div')).find('form input:visible, form select:visible, form textarea:visible').first().focus();

            if (eventOnComplete) {
                body.trigger(eventOnComplete, {openerFrom: openerFrom});
            }

            $('.side-panel-select2').select2();
            $('.side-panel-card-collection').cardCollection();
            $('.tooltip-wrapper').tooltip({position: 'bottom'});
        }).always(function() {
            sidePanelPending = false;
            stopLoading();
        })
    }

    function startLoading() {
        container.addClass('pending');
    }

    function stopLoading() {
        container.removeClass('pending');
    }

    function pushContainerStack(url, eventOnComplete) {

        if (container.children('div').first().is(':empty') || lastEventOnComplete === undefined) {
            lastEventOnComplete = eventOnComplete;
            if (container.children('div').first().is(':empty')) {
                return;
            }
        }

        var containerId = 'side-panel' + Math.round(new Date().getTime() + (Math.random() * 100));
        container
            .children('div')
            .first()
            .clone()
            .attr('id', containerId)
            .attr('style', 'display:none')
            .appendTo(body);

        var data = {
            containerId:containerId,
            url:url,
            eventOnComplete:eventOnComplete
        };
        containerStack.push(data);
    }

    function popContainerStack() {
        return containerStack.pop();
    }

    function clearContainerStack() {
        containerStack = [];
        lastEventOnComplete = undefined;
    }

    function closeAllSelect2() {
        $('select').each(function (i, obj) {
            if ($(obj).data('select2-id')) {
                $(obj).select2('close')
            }
        });
    }

    function closeSidePanel() {
        var prevContainerData = popContainerStack();

        if (prevContainerData) {
            var prevContainer = $('#' + prevContainerData.containerId);
            var newElement = prevContainer.clone();
            container.children('div').first().replaceWith(newElement);
            newElement.fadeIn();
            prevContainer.remove();
            if (lastEventOnComplete !== undefined) {
                lastEventOnComplete = prevContainerData.eventOnComplete;
                if (lastEventOnComplete) {
                    body.trigger(lastEventOnComplete);
                }
            }

            if (useBrowserHistory) {
                history.go(-1);
            }
        } else if (isSidePanelOpended()) {
            var rootUrl = null;
            container.children('div').children().each(function(index, item) {
                var element = $(item);
                if (element.data('root-url')) {
                    rootUrl = element.data('root-url');
                }
            });
            if (null !== rootUrl) {
                history.pushState(null, null, rootUrl);
            } else if (useBrowserHistory) {
                history.go(-1);
            }
            body.removeClass('no-scroll');
            container.removeClass('side-toggle');
            overlay.fadeOut();
            container.fadeOut(function() {
                container.children('div').hide();
                container.children('div').html('');
            });
        }
    }

    container.on('click', '.close-panel', function(event) {
        event.preventDefault();
        closeSidePanel();
    });

    body.on('click', '.open-side-panel', function(event) {
        event.preventDefault();
        var opener = $(this);
        openSidePanel(
            opener.attr('href'),
            opener.data('event'),
            opener.data('preload'),
            opener.data('browser-history'),
            opener.data('from')
        );
    });

    body.on('load-side-panel', function(event, data) {
        if (data && data.url) {
            openSidePanel(data.url, null, null, data.browserHistory);

            if (data.clearContainerStack !== false) {
                clearContainerStack();
            }
        }
    });

    body.on('side-panel.startLoading', function(event, data) {
        startLoading();
    });

    body.on('side-panel.stopLoading', function(event, data) {
        stopLoading();
    });

    body.on('update-side-panel', function(event, data) {
        if ("undefined" !== typeof(data.content)) {
            container.children('div').html(data.content);
            stopLoading();
            container.children('div').fadeIn();

            if (data.clearContainerStack !== false) {
                clearContainerStack();
            }

            if (data.eventOnComplete) {
                body.trigger(data.eventOnComplete);
            }
        }
    });

    body.on('close-side-panel', function(event, data) {
        closeSidePanel();
        if ("undefined" !== typeof(data) && "undefined" !== typeof(data.content)) {
            if ("undefined" !== typeof(data.content.error)) {
                alert(data.content.error);
                return;
            } else if ("undefined" !== typeof(data.content.type)) {
                var autoComplete;
                if ("company" === data.content.type) {
                    if ('contact' === openerFrom) {
                        autoComplete = $('.company-contact-autocomplete select');
                    } else {
                        autoComplete = $('.company-autocomplete select');
                    }
                }
                if ("contact" === data.content.type) {
                    if ('company' === openerFrom) {
                        autoComplete = $('.contact-company-autocomplete select');
                    } else {
                        autoComplete = $('.contact-autocomplete select');
                    }
                }
                if (autoComplete) {
                    autoComplete.select2('trigger', 'select', {
                        data: {
                            id: data.content.id,
                            text: data.content.text,
                            job: data.content.job,
                            picture: data.content.picture
                        }
                    });
                }
            }
        }
    });

    $(document).keyup(function(e) {
         if (e.keyCode == 27) { // escape key maps to keycode `27`
            closeSidePanel();
        }
    });

    if (container.children('div').children().length > 0) {
        overlay.fadeIn();
        container.fadeIn();
        container.children('div').fadeIn();
    }
});
