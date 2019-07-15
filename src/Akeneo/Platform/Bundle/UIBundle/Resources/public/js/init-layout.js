'use strict';

define(['jquery', 'backbone', 'underscore', 'pim/router', 'oro/translator', 'oro/app', 'oro/mediator', 'oro/layout',
        'pim/dialog', 'oro/messenger', 'bootstrap', 'jquery-setup'
], function ($, Backbone, _, router, __, app, mediator, layout, Dialog, messenger) {


    /* ============================================================
     * from layout.js
     * ============================================================ */
    return function () {
        mediator.once('tab:changed', function () {
            setTimeout(function () {
                // emulates 'document ready state' for selenium tests
                document['page-rendered'] = true;
                mediator.trigger('page-rendered');
            }, 50);
        });
        layout.init();

        /* ============================================================
         * from height_fix.js
         * ============================================================ */

        /* dynamic height for central column */
        var debugBar = $('.sf-toolbar');
        var anchor = $('#bottom-anchor');
        var content = false;

        var initializeContent = function () {
            if (!content) {
                content = $('.scrollable-container').filter(':parents(.ui-widget)');
                content.css('overflow', 'auto');
            }
        };

        var adjustHeight = function () {
            initializeContent();

            var debugBarHeight = debugBar.length && debugBar.is(':visible') ? debugBar.height() : 0;
            var anchorTop = anchor.position().top;

            $(content.get().reverse()).each(function (pos, el) {
                el = $(el);
                el.height(anchorTop - el.position().top - debugBarHeight);
            });

            layout.adjustScrollspy();
        };

        var tries = 0;
        var waitForDebugBar = function () {
            if (debugBar.children().length) {
                window.setTimeout(adjustHeight, 500);
            } else if (tries < 100) {
                tries += 1;
                window.setTimeout(waitForDebugBar, 500);
            }
        };

        var adjustReloaded = function () {
            content = false;
            adjustHeight();
        };

        if (!anchor.length) {
            anchor = $('<div id="bottom-anchor"/>')
                .css({
                    position: 'fixed',
                    bottom: '0',
                    left: '0',
                    width: '1px',
                    height: '1px'
                })
                .appendTo($(document.body));
        }

        mediator.once('page-rendered', function () {
            if (debugBar.length) {
                waitForDebugBar();
            } else {
                adjustHeight();
            }
        });

        $(window).on('resize', adjustHeight);

        mediator.bind('route_complete', adjustReloaded);

        /* ============================================================
         * from form_buttons.js
         * ============================================================ */
        $(document).on('click', '.action-button', function () {
            var actionInput = $('input[name = "input_action"]');
            actionInput.val($(this).attr('data-action'));
            $('#' + actionInput.attr('data-form-id')).submit();
        });

        /* ============================================================
         * from remove.confirm.js
         * ============================================================ */

        $(document).on('click', '.remove-button', function () {
            var el = $(this);
            var message = el.data('message');
            const subTitle = el.data('subtitle');

            const doDelete = function () {
                router.showLoadingMask();

                $.ajax({
                    url: el.data('url'),
                    type: 'DELETE',
                    success: function () {
                        el.trigger('removesuccess');
                        messenger.enqueueMessage(
                            'success',
                            el.data('success-message'),
                            { 'hashNavEnabled': true }
                        );
                        if (el.data('redirect')) {
                            $.isActive(true);
                            Backbone.history.navigate('#' + el.data('redirect'));
                        } else {
                            router.hideLoadingMask();
                        }
                    },
                    error: function (response) {
                        router.hideLoadingMask();

                        let contentType = response.getResponseHeader('content-type');
                        let message = __('Unexpected error occurred. Please contact system administrator.');

                        if (contentType.indexOf('application/json') !== -1) {
                            const decodedResponse = JSON.parse(response.responseText);
                            if (undefined !== decodedResponse.message) {
                                message = decodedResponse.message
                            }
                        }

                        messenger.notify(
                            'error',
                            el.data('error-message') || message,
                            { flash: false }
                        );
                    }
                });
            };

            this.confirmModal = Dialog.confirmDelete(
                message,
                __('pim_common.confirm_deletion'),
                doDelete,
                subTitle
            );

            return false;
        });
    };
});
