jQuery(function ($) {
    'use strict';

    var forms = $('.deals-search, .metrics-form, .investors-search'),
        slidersRange = forms.find('.slider-range');

    function calculatePosition(min, max, currentMin, currentMax) {
        var total;

        var leftVal = currentMin;
        var rightVal = currentMax;

        if(min <= 0 && max <= 0) {
            total = Math.abs(min) - Math.abs(max)
            leftVal = leftVal + Math.abs(min)
            rightVal = rightVal + Math.abs(min)
        } else if (min < 0 && max > 0) {
            total = Math.abs(min) + max
            leftVal = leftVal + Math.abs(min)
            rightVal = rightVal + Math.abs(min)
        } else {
            total = max - min
        }

        var posMin = Math.round(leftVal / total * 100);
        var posMax = Math.round(rightVal / total * 100);

        return {
            min: posMin,
            max: posMax,
        }
    }

    slidersRange.each(function (index, element) {
        var form = $(this).closest('form');
        var sliderRange = $(element);

        sliderRange.slider({
            range: true,
            min: sliderRange.data('default-min'),
            max: sliderRange.data('default-max'),
            step: sliderRange.data('step'),
            values: [sliderRange.data('min'), sliderRange.data('max')],
            create: function( event, ui ) {
                var sliderWrapper = sliderRange.closest('.slider-range-wrapper');

                var position = calculatePosition(
                    sliderRange.data('default-min'),
                    sliderRange.data('default-max'),
                    sliderRange.data('min'),
                    sliderRange.data('max')
                );

                sliderWrapper.find('.slider-range-legend .min').css('left', position.min + '%');
                sliderWrapper.find('.slider-range-legend .max').css('left', position.max + '%');
            },
            slide: function (event, ui) {
                var sliderWrapper = sliderRange.closest('.slider-range-wrapper');

                var position = calculatePosition(
                    sliderRange.data('default-min'),
                    sliderRange.data('default-max'),
                    ui.values[0],
                    ui.values[1]
                );

                sliderWrapper.find('.slider-min-wrapper input').val(ui.values[0]);
                sliderWrapper.find('.slider-max-wrapper input').val(ui.values[1]);
                sliderWrapper.find('.slider-range-legend .min').text(ui.values[0].toLocaleString()).css('left', position.min + '%');
                sliderWrapper.find('.slider-range-legend .max').text(ui.values[1].toLocaleString()).css('left', position.max + '%');
                form.trigger('change');
            }
        });
    });
});