define(
    ['jquery', 'oro/formatter/datetime', 'jquery-ui'],
    function ($, datetimeFormatter) {

        var init = function(id) {
            var $field = $('#' + id);
            if ($field.hasClass('hasPicker')) {
                return;
            }

            var pickerId = 'date_selector_' + id;
            var $picker = $('<input>', { type: 'text', id: pickerId, name: pickerId, placeholder: $field.attr('placeholder') });
            $picker.insertAfter($field);
            $field.addClass('hasPicker').wrap($('<span>', { 'class': 'hide' }));

            $field.on('change', function() {
                $picker.val(datetimeFormatter.formatDate($field.val()));
            });

            if ($field.val() && $field.val().length) {
                $picker.val(datetimeFormatter.formatDate($field.val()));
            }

            $picker.datepicker({
                altField: '#' + id,
                altFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                yearRange: '-80:+1',
                showButtonPanel: true
            });

            $picker.keyup(function () {
                var value = $picker.val();
                if (datetimeFormatter.isDateValid(value)) {
                    $field.val(datetimeFormatter.convertDateToBackendFormat(value));
                } else {
                    $field.val('');
                }
            });
        };

        return {
            init: init
        };
    }
);
