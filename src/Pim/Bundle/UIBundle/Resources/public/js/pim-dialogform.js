define(
    ['jquery', 'oro/navigation', 'oro/loading-mask', 'pim/initselect2', 'jquery-ui-full', 'bootstrap-tooltip'],
    function ($, Navigation, LoadingMask, initSelect2) {
        'use strict';

        // Allow using select2 search box in jquery ui dialog
        $.ui.dialog.prototype._allowInteraction = function(e) {
            return !!$(e.target).closest('.ui-dialog, .ui-datepicker, .select2-drop').length;
        };

        return function (elementId, callback) {
            var $el = $(elementId);
            if (!$el.length) {
                return console.error('DialogForm: the element could not be found!');
            }
            var $dialog;
            var url = $el.attr('data-form-url');
            if (!url) {
                throw new Error('DialogForm: please specify the url');
            }
            var width = $el.attr('data-form-width') || 400;

            var loadingMask = null;

            function showLoadingMask() {
                if (!loadingMask) {
                    loadingMask = new LoadingMask();
                    loadingMask.render().$el.appendTo($('#container'));
                }
                loadingMask.show();
            }

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
                        click: function () {
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
                        click: function () {
                            destroyDialog();
                        }
                    });
                }

                $dialog = $form.dialog({
                    title: formTitle,
                    modal: true,
                    resizable: false,
                    width: width,
                    buttons: formButtons,
                    open: function () {
                        $(this).parent().keypress(function (e) {
                            if (e.keyCode === $.ui.keyCode.ENTER) {
                                e.preventDefault();
                                e.stopPropagation();
                                $(this).find('button.btn-primary:eq(0)').click();
                            }
                        });
                    }
                });

                initSelect2.init($(formId));
                $(formId).find('[data-toggle="tooltip"]').tooltip();
            }

            function isJSON(str) {
                try {
                    JSON.parse(str);
                } catch (e) {
                    return false;
                }
                return true;
            }

            function processResponse(data, $dialog) {
                if (isJSON(data)) {
                    data = $.parseJSON(data);
                    destroyDialog();
                    if (callback) {
                        callback(data);
                    } else {
                        Navigation.getInstance().setLocation(data.url);
                    }
                } else if ($(data).prop('tagName').toLowerCase() === 'form') {
                    createDialog(data);
                }
            }

            $el.on('click', function (e) {
                e.preventDefault();
                showLoadingMask();
                $.ajax({
                    url: url,
                    type: 'get',
                    success: function (data) {
                        loadingMask.hide();
                        createDialog(data);
                    }
                });
            });
        };
    }
);
