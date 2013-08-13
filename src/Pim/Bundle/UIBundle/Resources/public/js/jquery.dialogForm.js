/**
 * jQuery dialog forn plugin
 */

(function($){
    'use strict';

    $.fn.dialogForm = function() {
        var $el = $(this);
        var $dialog;
        var url = $el.attr('data-form-url');
        if (!url) {
            throw new Error('Please specify the url');
        }
        var width = $el.attr('data-form-width') || 400;

        function destroyDialog() {
            if ($dialog && $dialog.length) {
                $dialog.remove();
            }
            $dialog = null;
        }

        function createDialog(data) {
            destroyDialog();
            var $form = $(data);
            var formTitle = $form.data('title');
            var formId = '#' + $form.attr('id');

            var formButtons = [];
            var submitButton = $form.data('button-submit');
            var cancelButton = $form.data('button-cancel');
            if (submitButton) {
                formButtons.push({
                    text: submitButton,
                    'class': 'btn btn-primary',
                    click: function() {
                        $.ajax({
                            url: url,
                            type: 'post',
                            data: $(formId).serialize(),
                            success: function (data) {
                                processResponse(data, $dialog);
                            }
                        });
                    }
                });
            }
            if (cancelButton) {
                formButtons.push({
                    text: cancelButton,
                    'class': 'btn',
                    click: function() {
                        destroyDialog();
                    }
                });
            }

            $dialog = $form.dialog({
                title: formTitle,
                modal: true,
                resizable: false,
                width: width,
                buttons: formButtons
            });

            $(formId + ' select').select2({ allowClear: true });
            $(formId + ' a.validation-tooltip').tooltip();
        }

        function processResponse(data, $dialog) {
            if (isJSON(data)) {
                data = $.parseJSON(data);
                if (data.status == 1) {
                    window.location = data.url;
                }
            } else if ($(data).prop('tagName').toLowerCase() == 'form') {
                createDialog(data);
            }
        }

        function isJSON(str) {
            try {
                JSON.parse(str);
            } catch (e) {
                return false;
            }
            return true;
        }

        $el.on('click', function(e) {
            e.preventDefault();
            $.ajax({
                url: url,
                type: 'get',
                success: function(data) {
                    createDialog(data);
                }
            });
        });
    };

})(jQuery);
