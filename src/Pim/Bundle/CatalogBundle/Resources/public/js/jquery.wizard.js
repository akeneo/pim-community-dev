(function ($) {
    'use strict';

    $.fn.wizard = function (options) {
        var opts = $.extend({}, $.fn.wizard.defaults, options),
            $steps = $(this).find('li'),
            currentStep = opts.currentStep;

        if (!$(this).hasClass('wizard')) {
            $(this).addClass('wizard');
        }

        $steps.each(function () {
            $('div', this)
                .remove('.progress-start')
                .remove('.progress-end')
                .remove('.dot');
            $(this)
                .append('<div class="progress-start"></div>')
                .append('<div class="progress-end"></div>');
        });

        $steps.first().find('.progress-start').hide();
        $steps.last().find('.progress-end').hide();

        for (var i = 0; i < currentStep; i++) {
            if (i !== 0) {
                $steps.eq(i).find('.progress-start').addClass('active');
            }
            if (i != currentStep - 1) {
                $steps.eq(i).find('.progress-end').addClass('active');
            }
            if (i == currentStep - 1) {
                $steps.eq(i).append('<div class="dot"><i class="icon-circle"></i></div>');
            }
        }
    };

    $.fn.wizard.defaults = {
        currentStep: 1
    };
})(jQuery);
