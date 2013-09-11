(function($) {
    'use strict';

    $.fn.wizard = function(options) {
        if (!$(this).hasClass('wizard')) {
            $(this).addClass('wizard');
        }

        var $steps = $(this).find('li'),
            currentStep = options.currentStep;

        $steps.each(function(index){
            $(this)
                .append('<div class="progress-start"></div>')
                .append('<div class="progress-end"></div>');
        });

        $steps.first().find('.progress-start').hide();
        $steps.last().find('.progress-end').hide();

        for (var i = 0; i < currentStep; i++) {
            if (i != 0) {
                $steps.eq(i).find('.progress-start').addClass('active');
            }
            if (i != currentStep - 1) {
                $steps.eq(i).find('.progress-end').addClass('active');
            }
            if (i == currentStep - 1) {
                $steps.eq(i).append('<div class="dot"><i class="icon-circle"></i></div>');
            }
        }
    }

    // $.fn.wizard.defaults = 
}) (jQuery);
