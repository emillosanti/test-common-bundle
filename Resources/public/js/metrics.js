jQuery(function ($) {
    'use strict';

    var metricsForm = $('.metrics-form'),
        body = $('body'),
        formName = metricsForm.attr('name'),
        highlightForm = $('.highlight-deal-flow-form'),
        canvas = $('.deals-metrics-chart'),
        charts = {},
        colors = ['#2196F3', '#13588e', '#101821', '#5E7CE2', '#3434F5', '#c8c8c8', '#3CBA12', '#8BC34A', '#12F493', '#FFEE00'],
        timeout = null,
        loading = $('.loading'),
        metricsWrapper = $('.metrics-wrapper');

    function buildDataset(key, data, uniqueColor, label) {
        uniqueColor = typeof uniqueColor === 'undefined' ? false : uniqueColor;
        label = label || false;
        return [{
            data: data.map(function(item) {
                return parseInt(item.count);
            }),
            backgroundColor: data.map(function(item, key) {
                var index = key;
                if (index >= colors.length) {
                    index = Math.floor(key % colors.length);
                }

                return uniqueColor ? colors[0] : colors[index];
            }),
            label: label ? label : key
        }];
    }

    function buildLabels(data, addPercentage) {
        addPercentage = typeof addPercentage === 'undefined' ? true : addPercentage;

        var total = data.reduce(function(currentValue, item) {
            return parseInt(item.count) + currentValue;
        }, 0);

        return data.map(function(item) {
            var percent = Math.round((parseInt(item.count) * 100) / total);
            return addPercentage ? item.label + ' : ' + percent + '%' : item.label;
        });
    }

    function initializeChart(element, data) {
        var jqElement = $(element),
            type = jqElement.data('type'),
            key = jqElement.data('key'),
            ctx = element.getContext('2d'),
            stepSize = jqElement.data('step-size'),
            legend = jqElement.data('legend'),
            addPercentage = typeof jqElement.data('show-percentage') !== 'undefined' ? jqElement.data('show-percentage') : true,
            uniqueColor = typeof jqElement.data('monochromatic') !== 'undefined' ? jqElement.data('monochromatic') : false,
            label = jqElement.data('label'),
            canvasHolder = jqElement.parent(),
            loader = canvasHolder.siblings('.loading');

        var config = {
                type: type,
                data: {
                    datasets: buildDataset(key, data, uniqueColor, label),
                    labels: buildLabels(data, addPercentage),
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        display: legend == 'hide' ? false : true,
                        position: 'bottom'
                    },
                    title: false,
                    animation: {
                        animateScale: true,
                        animateRotate: true
                    }
                }
            };

        if ('histogram' === type) {
            config = {
                type: 'bar',
                data: {
                    datasets: buildDataset(key, data),
                    labels: buildLabels(data),
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    legend: {
                        position: false
                    },
                    title: false,
                    animation: {
                        animateScale: true,
                        animateRotate: true
                    },
                    scales: {
                        xAxes: [{
                            display: false,
                            barPercentage: 0.9,
                        }, {
                            display: true,
                        }],
                        yAxes: [{
                            ticks: {
                                beginAtZero:true,
                                stepSize: 1
                            },
                        }]
                    }
                }
            };
        } else if ('funnel' === type) {
            config.options.sort = 'desc';
            config.options.legend.position = 'right';
        }

        if (stepSize) {
            config.options.scales = {
                        yAxes: [{
                            display: false,
                            gridLines: {
                                display: false
                            },
                            ticks: {
                                beginAtZero:true,
                                stepSize: stepSize
                            }
                        }]
                    };
        }

        if (loader) {
            loader.hide();
            canvasHolder.show();
        }
        
        charts[key] = new Chart(ctx, config);
    }

    function updateDoughnutChart(key, data){
        if ("undefined" !== typeof(charts[key])) {
            var chart = charts[key];
            chart.data.labels = buildLabels(data);
            chart.data.datasets = buildDataset(key, data);
            chart.update();
        }
    }

    function updateStepsChart(data){
        var max = 0;
        for (var i = 0; i < data.length; i++) {
            var count = parseInt(data[i].count);
            if (count > max) {
                max = count;
            }
        }
        $('.deal-step-chart .deal-step-chart-body > div').each(function (index, element) {
            var jqElement = $(element),
                count = parseInt(data[index].count),
                height = max > 0 ? (count * 100) / max : 0;
            jqElement.find('.column-bar').animate({height: height + "%"});
            jqElement.find('.column-label').animate({bottom: height + "%"});
            jqElement.find('.column-label').text(count);
        });
        $('.deal-step-chart .deal-step-chart-footer > div').each(function (index, element) {
            var jqElement = $(element),
                percent = 0;
            if (index > 0 && data[0].count > 0) {
                percent = ((parseInt(data[index].count) * 100) / (data[0].count));
            }
            jqElement.find('.relative-value').text(Math.round(percent) + '%');
        });
    }

    function getDateRanges(){
        var ranges = {};

        for (var i = 0; i < 4; i++) {
            var m = moment().subtract(i * 3, 'months');
            ranges['T' + m.quarter() + ' ' + m.year()] = [
                moment().subtract(i * 3, 'months').startOf('quarter'),
                moment().subtract(i * 3, 'months').endOf('quarter')
            ];
        }

        if (moment().month() < 6) {
            ranges['S1 ' + moment().year()] = [
                moment().startOf('year'),
                moment().endOf('year').subtract(6, 'months')
            ];
            ranges['S2 ' + (moment().year() - 1)] = [
                moment().subtract(1, 'year').startOf('year').add(6, 'months'),
                moment().subtract(1, 'year').endOf('year'),
            ];
        } else {
            ranges['S2 ' + moment().year()] = [
                moment().startOf('year').add(6, 'months'),
                moment().endOf('year'),
            ];
            ranges['S1 ' + moment().year()] = [
                moment().startOf('year'),
                moment().endOf('year').subtract(6, 'months')
            ];
        }

        ranges[moment().year()] = [moment().startOf('year'), moment().endOf('year')];
        ranges[moment().year() - 1] = [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')];

        return ranges;
    }

    canvas.each(function(index, element) {
        initializeChart(element, JSON.parse(element.getAttribute('data-chart-data')));
    });

    var process = function() {
        var data = {};

        if (metricsForm.hasClass('investor-statistics')) {
            data[formName] = {
                legalEntity: metricsForm.find('.metrics-legal-entity-select').val(),
                user: metricsForm.find('.metrics-user-select').val(),
                investorCategory: metricsForm.find('.metrics-investor-category-select').val(),
                hasFundraiser: metricsForm.find('.metrics-has-fundraiser-choice').val(),
                totalInvestmentAmount: {
                    min: metricsForm.find('.metrics-total-investment-slider .slider-min-wrapper input').val(),
                    max: metricsForm.find('.metrics-total-investment-slider .slider-max-wrapper input').val(),
                },
            };
        } else {
            var dateRange = metricsForm.find('input[name="daterange"]').val().split('-');
            data[formName] = {
                user: metricsForm.find('.metrics-user-select').val(),
                dateRange: {
                    start: dateRange[0].trim(),
                    end: dateRange[1].trim()
                },
                ticket: {
                    min: metricsForm.find('.metrics-ticket-slider .slider-min-wrapper input').val(),
                    max: metricsForm.find('.metrics-ticket-slider .slider-max-wrapper input').val(),
                },
                sourcingType: metricsForm.find('.metrics-sourcing-type-select').val(),
                operationType: metricsForm.find('.metrics-operation-type-select').val(),
                step: metricsForm.find('.metrics-step-select').val(),
                sector: metricsForm.find('.metrics-sector-select') ? metricsForm.find('.metrics-sector-select').val() : null,
                ca: {
                    min: metricsForm.find('.metrics-ca-slider .slider-min-wrapper input').val(),
                    max: metricsForm.find('.metrics-ca-slider .slider-max-wrapper input').val(),
                },
                ebitda: {
                    min: metricsForm.find('.metrics-ebitda-slider .slider-min-wrapper input').val(),
                    max: metricsForm.find('.metrics-ebitda-slider .slider-max-wrapper input').val(),
                },
                ebit: {
                    min: metricsForm.find('.metrics-ebit-slider .slider-min-wrapper input').val(),
                    max: metricsForm.find('.metrics-ebit-slider .slider-max-wrapper input').val(),
                },
            };
        }

        $.ajax({
            url: metricsForm.attr('action'),
            method: metricsForm.attr('method'),
            data: data,
            beforeSend: function() {
                metricsWrapper.hide();
                loading.show();
            },
        }).done(function(response) {
            var keys = Object.keys(response);
            keys.map(function(key) {
                updateDoughnutChart(key, response[key]);
            });
            if ("undefined" !== typeof(response.steps)) {
                updateStepsChart(response.steps);
            }
        }).always(function() {
            loading.hide();
            metricsWrapper.show();
        });
    };

    metricsForm.on('click', '.export-metrics', function(event) {
        event.preventDefault();
        var formAction = metricsForm.attr('action');
        metricsForm.attr('action', $(this).attr('href'));
        metricsForm.submit();
        metricsForm.attr('action', formAction);
    });

    metricsForm.find('input[name="daterange"]').daterangepicker({
        "showDropdowns": true,
        "locale": {
            "format": "DD/MM/YYYY",
            "separator": " - ",
            "applyLabel": "Appliquer",
            "cancelLabel": "Annuler",
            "fromLabel": "De",
            "toLabel": "A",
            "customRangeLabel": "Personnalisé",
            "weekLabel": "S",
            "daysOfWeek": [
                "Di",
                "Lu",
                "Ma",
                "Me",
                "Je",
                "Ve",
                "Sa"
            ],
            "monthNames": [
                "Janvier",
                "Février",
                "Mars",
                "Avril",
                "Mai",
                "Juin",
                "Juillet",
                "Août",
                "Septembre",
                "Octobre",
                "Novembre",
                "Décembre"
            ],
            "firstDay": 1
        },
        "showISOWeekNumbers": true,
        ranges: getDateRanges(),
        "alwaysShowCalendars": true,
        "startDate": moment().subtract(1, 'year').startOf('day'),
        "endDate": moment().endOf('day')
    }, function(start, end, label) {
        metricsForm.find('#' + formName + '_dateRange_start').val(start.format('DD/MM/YYYY'));
        metricsForm.find('#' + formName + '_dateRange_end').val(end.format('DD/MM/YYYY'));
        metricsForm.find('.daterange-label').text('Du ' + start.format('DD/MM/YYYY') + ' au ' + end.format('DD/MM/YYYY'));
    });

    metricsForm.on('change', function () {
        clearTimeout(timeout);
        timeout = setTimeout(function () {
            process(metricsForm);
        }, 500);
    });

    metricsForm.find('#' + formName + '_dateRange_start').val(moment().subtract(1, 'year').startOf('day').format('DD/MM/YYYY'));
    metricsForm.find('#' + formName + '_dateRange_end').val(moment().endOf('day').format('DD/MM/YYYY'));

    highlightForm.on('change', function() {
        highlightForm.submit();
    });

    body.on('loader.start', function() {
        if (metricsForm && metricsForm.length > 0) {
            metricsWrapper.hide();
            loading.show();
        }
    });

    body.on('legalentity.updated', function(event, data) {
        if (metricsForm && metricsForm.length > 0) {
            process(metricsForm);
        }
    });
});
