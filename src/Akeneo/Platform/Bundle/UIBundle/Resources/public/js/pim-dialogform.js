/* global console */
define(
    [
        'jquery',
        'underscore',
        'oro/mediator',
        'pim/router',
        'oro/loading-mask',
        'pim/initselect2',
        'jquery-ui',
        'bootstrap'
    ], function (
        $,
         _,
         mediator,
         router,
         LoadingMask,
         initSelect2
    ) {
        'use strict';

        // Allow using select2 search box in jquery ui dialog
        $.ui.dialog.prototype._allowInteraction = function (e) {
            return !!$(e.target).closest('.ui-dialog, .select2-drop').length;
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
                var loadingMask = $('<div class="AknLoadingMask">').hide();
                $('body').append(loadingMask);

                var formButtons = [];
                var submitButton = $form.data('button-submit');
                var cancelButton = $form.data('button-cancel');
                if (submitButton) {
                    formButtons.push({
                        text: submitButton,
                        'class': 'btn btn-primary',
                        click: function () {
                            showLoadingMask();
                            $.ajax({
                                url: url,
                                type: 'post',
                                data: $(formId).serialize(),
                                success: function (data) {
                                    processResponse(data);
                                    mediator.trigger('dialog:open:after', this);
                                    loadingMask.remove();
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
                            loadingMask.remove();
                        }
                    });
                }

                $dialog = $form.dialog({
                    title: formTitle,
                    modal: true,
                    resizable: false,
                    width: width,
                    buttons: formButtons,
                    draggable: false,

                    open: function () {
                        $(this).parent().on('keypress', function (e) {
                            if (e.keyCode === $.ui.keyCode.ENTER) {
                                e.preventDefault();
                                e.stopPropagation();
                                $(this).find('button.btn-primary:eq(0)').click();
                            }
                        });
                        loadingMask.show();
                    },

                    close: function () {
                        $(this).remove();
                        loadingMask.remove();
                    }
                });

                initSelect2.init($(formId));
                $(formId + ' .switch').bootstrapSwitch();

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

            function processResponse(data) {
                loadingMask.hide();
                if (isJSON(data)) {
                    data = $.parseJSON(data);
                    destroyDialog();
                    if (callback) {
                        callback(data);
                    } else {
                        router.redirect(data.url);
                    }
                } else if (_.isObject(data)) {
                    destroyDialog();
                    if (callback) {
                        callback(data);
                    } else if (data.url) {
                        router.redirect(data.url);
                    } else if (data.route) {
                        router.redirectToRoute(data.route, data.params);
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
                        mediator.trigger('dialog:open:after', this);
                    }
                });
            });
        };
    }
);
